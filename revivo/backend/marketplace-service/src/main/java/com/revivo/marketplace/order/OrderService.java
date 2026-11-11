package com.revivo.marketplace.order;

import com.revivo.events.DomainEvent;
import com.revivo.events.OrderPlaced;
import com.revivo.events.OrderStatusChanged;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemRepository;
import com.revivo.marketplace.offer.Offer;
import com.revivo.marketplace.offer.OfferService;
import com.revivo.marketplace.order.OrderDtos.CheckoutQuote;
import com.revivo.marketplace.order.OrderDtos.CheckoutRequest;
import com.revivo.marketplace.order.OrderDtos.OrderDto;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.payment.PaymentGateway;
import com.revivo.marketplace.review.ReviewRepository;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import com.revivo.marketplace.user.UserService;
import com.revivo.marketplace.wallet.WalletService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.math.BigDecimal;
import java.time.Clock;
import java.time.Instant;
import java.util.Optional;

/**
 * Orchestration du tunnel d'achat et du cycle de vie des commandes.
 * <p>
 * Toutes les opérations sont transactionnelles ; les événements partent via l'outbox,
 * donc uniquement si la transaction est validée.
 */
@Service
@Transactional(readOnly = true)
public class OrderService {

    private static final Logger log = LoggerFactory.getLogger(OrderService.class);

    private final OrderRepository orders;
    private final ItemRepository items;
    private final OfferService offerService;
    private final UserService userService;
    private final WalletService walletService;
    private final PaymentGateway paymentGateway;
    private final FeeCalculator feeCalculator;
    private final ReviewRepository reviews;
    private final DomainEventPublisher events;
    private final Clock clock;

    public OrderService(OrderRepository orders, ItemRepository items, OfferService offerService,
                        UserService userService, WalletService walletService, PaymentGateway paymentGateway,
                        FeeCalculator feeCalculator, ReviewRepository reviews, DomainEventPublisher events,
                        Clock clock) {
        this.orders = orders;
        this.items = items;
        this.offerService = offerService;
        this.userService = userService;
        this.walletService = walletService;
        this.paymentGateway = paymentGateway;
        this.feeCalculator = feeCalculator;
        this.reviews = reviews;
        this.events = events;
        this.clock = clock;
    }

    // ------------------------------------------------------------------ checkout

    public CheckoutQuote quote(Long buyerId, Long itemId, ShippingOption option) {
        Item item = items.findById(itemId).orElseThrow(() -> new NotFoundException("Article", itemId));
        Optional<Offer> offer = offerService.usableAcceptedOffer(itemId, buyerId);
        BigDecimal price = offer.map(Offer::getAmount).orElse(item.getPrice());
        BigDecimal fee = feeCalculator.protectionFee(price);
        BigDecimal shipping = feeCalculator.shippingPrice(option);
        return new CheckoutQuote(itemId, price, offer.isPresent(), fee, shipping, price.add(fee).add(shipping));
    }

    /**
     * Achat d'un article :
     * <ol>
     *   <li>verrou pessimiste sur l'article (deux acheteurs ne peuvent pas payer le même article) ;</li>
     *   <li>application de l'éventuelle offre acceptée ;</li>
     *   <li>débit via le PSP (clé d'idempotence) puis séquestre des fonds dans le porte-monnaie vendeur ;</li>
     *   <li>compensation (remboursement) si la suite du traitement échoue.</li>
     * </ol>
     */
    @Transactional
    public OrderDto checkout(Long buyerId, Long itemId, CheckoutRequest request) {
        Item item = items.lockById(itemId).orElseThrow(() -> new NotFoundException("Article", itemId));
        if (item.isOwnedBy(buyerId)) {
            throw new BusinessException("OWN_ITEM", "Vous ne pouvez pas acheter votre propre article.");
        }
        if (!item.isPurchasable()) {
            throw new BusinessException("ITEM_NOT_AVAILABLE", "Cet article n'est plus disponible.");
        }
        ShippingOption option = request.shippingOption();
        if (option.requiresAddress() && (request.shippingAddress() == null || request.shippingAddress().isBlank())) {
            throw new BusinessException("ADDRESS_REQUIRED", "Une adresse de livraison est requise.");
        }
        User buyer = userService.getUser(buyerId);
        Optional<Offer> offer = offerService.usableAcceptedOffer(itemId, buyerId);
        BigDecimal price = offer.map(Offer::getAmount).orElse(item.getPrice());
        BigDecimal fee = feeCalculator.protectionFee(price);
        BigDecimal shipping = feeCalculator.shippingPrice(option);
        BigDecimal total = price.add(fee).add(shipping);

        Instant now = clock.instant();
        String paymentRef = paymentGateway.charge(buyerId, total, request.paymentMethodToken(),
                "checkout-" + itemId + "-" + buyerId + "-" + now.toEpochMilli());
        try {
            item.markSold();
            offer.ifPresent(Offer::markUsed);
            offerService.closeOpenOffers(itemId);
            Order order = orders.saveAndFlush(new Order(item, buyer, offer.map(Offer::getId).orElse(null), price,
                    shipping, fee, option, option.requiresAddress() ? request.shippingAddress().trim() : null,
                    paymentRef, now));
            walletService.escrow(item.getSeller().getId(), order.getId(), price);
            events.publish(new OrderPlaced(DomainEvent.newId(), now, order.getId(), itemId, item.getTitle(), buyerId,
                    item.getSeller().getId(), price, order.getTotal()));
            log.info("Commande {} créée : article {} acheté par {} pour {} €", order.getId(), itemId, buyerId, total);
            return toDto(order, buyerId);
        } catch (RuntimeException e) {
            // Compensation : l'acheteur a été débité mais la commande n'a pas pu être enregistrée.
            paymentGateway.refund(paymentRef, total);
            throw e;
        }
    }

    // ------------------------------------------------------------------ cycle de vie

    @Transactional
    public OrderDto ship(Long sellerId, Long orderId, String trackingNumber) {
        Order order = find(orderId);
        requireSeller(order, sellerId);
        if (order.getShippingOption() == ShippingOption.HAND_DELIVERY) {
            throw new BusinessException("HAND_DELIVERY", "Remise en main propre : pas d'expédition à déclarer.");
        }
        if (trackingNumber == null || trackingNumber.isBlank()) {
            throw new BusinessException("TRACKING_REQUIRED", "Le numéro de suivi est obligatoire.");
        }
        order.ship(trackingNumber.trim(), clock.instant());
        publishStatus(order, OrderStatus.PAID, "Suivi : " + trackingNumber.trim());
        return toDto(order, sellerId);
    }

    /** Simulation du webhook transporteur « colis livré » (vendeur ou admin). */
    @Transactional
    public OrderDto markDelivered(AuthenticatedUser actor, Long orderId) {
        Order order = find(orderId);
        if (!actor.isAdmin() && !order.isSeller(actor.id())) {
            throw new ForbiddenException("Action réservée au transporteur / vendeur.");
        }
        OrderStatus previous = order.transitionTo(OrderStatus.DELIVERED, clock.instant());
        publishStatus(order, previous, null);
        return toDto(order, actor.id());
    }

    /** L'acheteur confirme que tout est conforme : les fonds sont libérés au vendeur. */
    @Transactional
    public OrderDto confirmReception(Long buyerId, Long orderId) {
        Order order = find(orderId);
        if (!order.isBuyer(buyerId)) {
            throw new ForbiddenException("Seul l'acheteur peut confirmer la réception.");
        }
        if (order.getStatus() == OrderStatus.PAID && order.getShippingOption() != ShippingOption.HAND_DELIVERY) {
            throw new BusinessException("NOT_SHIPPED", "La commande n'a pas encore été expédiée.");
        }
        complete(order, "Validée par l'acheteur");
        return toDto(order, buyerId);
    }

    @Transactional
    public OrderDto cancel(Long actorId, Long orderId, String reason) {
        Order order = find(orderId);
        if (!order.involves(actorId)) {
            throw new ForbiddenException("Cette commande ne vous concerne pas.");
        }
        if (order.getStatus() != OrderStatus.PAID) {
            throw new BusinessException("CANNOT_CANCEL", "Une commande expédiée ne peut plus être annulée.");
        }
        OrderStatus previous = order.transitionTo(OrderStatus.CANCELLED, clock.instant());
        refund(order);
        publishStatus(order, previous, reason);
        return toDto(order, actorId);
    }

    @Transactional
    public OrderDto openDispute(Long buyerId, Long orderId, String reason) {
        Order order = find(orderId);
        if (!order.isBuyer(buyerId)) {
            throw new ForbiddenException("Seul l'acheteur peut ouvrir un litige.");
        }
        OrderStatus previous = order.getStatus();
        order.dispute(reason, clock.instant());
        publishStatus(order, previous, reason);
        return toDto(order, buyerId);
    }

    /** Arbitrage d'un litige par la modération : remboursement de l'acheteur ou paiement du vendeur. */
    @Transactional
    public OrderDto resolveDispute(Long adminId, Long orderId, boolean refundBuyer) {
        Order order = find(orderId);
        if (order.getStatus() != OrderStatus.DISPUTED) {
            throw new BusinessException("NOT_DISPUTED", "Cette commande n'est pas en litige.");
        }
        if (refundBuyer) {
            OrderStatus previous = order.transitionTo(OrderStatus.REFUNDED, clock.instant());
            refund(order);
            publishStatus(order, previous, "Litige résolu en faveur de l'acheteur");
        } else {
            complete(order, "Litige résolu en faveur du vendeur");
        }
        log.info("Litige de la commande {} arbitré par {} (remboursement={})", orderId, adminId, refundBuyer);
        return toDto(order, adminId);
    }

    /** Validation automatique N heures après la livraison si l'acheteur ne s'est pas manifesté. */
    @Transactional
    public int autoCompleteDelivered(Instant deliveredBefore) {
        var due = orders.findTop100ByStatusAndDeliveredAtBefore(OrderStatus.DELIVERED, deliveredBefore);
        due.forEach(o -> complete(o, "Validation automatique"));
        return due.size();
    }

    private void complete(Order order, String detail) {
        OrderStatus previous = order.transitionTo(OrderStatus.COMPLETED, clock.instant());
        walletService.release(order.getSeller().getId(), order.getId(), order.getItemPrice());
        publishStatus(order, previous, detail);
    }

    private void refund(Order order) {
        paymentGateway.refund(order.getPaymentReference(), order.getTotal());
        walletService.cancelEscrow(order.getSeller().getId(), order.getId(), order.getItemPrice());
        order.getItem().relist();
    }

    // ------------------------------------------------------------------ lecture

    public OrderDto get(AuthenticatedUser user, Long orderId) {
        Order order = find(orderId);
        if (!user.isAdmin() && !order.involves(user.id())) {
            throw new ForbiddenException("Cette commande ne vous concerne pas.");
        }
        return toDto(order, user.id());
    }

    public PageResponse<OrderDto> purchases(Long buyerId, int page, int size) {
        return PageResponse.of(orders.findByBuyerId(buyerId, pageOf(page, size)), o -> toDto(o, buyerId));
    }

    public PageResponse<OrderDto> sales(Long sellerId, int page, int size) {
        return PageResponse.of(orders.findBySellerId(sellerId, pageOf(page, size)), o -> toDto(o, sellerId));
    }

    public PageResponse<OrderDto> byStatus(OrderStatus status, Long adminId, int page, int size) {
        return PageResponse.of(orders.findByStatus(status, pageOf(page, size)), o -> toDto(o, adminId));
    }

    private static PageRequest pageOf(int page, int size) {
        return PageRequest.of(page, Math.min(size, 100), Sort.by(Sort.Direction.DESC, "createdAt"));
    }

    private Order find(Long id) {
        return orders.findById(id).orElseThrow(() -> new NotFoundException("Commande", id));
    }

    private static void requireSeller(Order order, Long userId) {
        if (!order.isSeller(userId)) {
            throw new ForbiddenException("Action réservée au vendeur.");
        }
    }

    private void publishStatus(Order order, OrderStatus previous, String detail) {
        events.publish(new OrderStatusChanged(DomainEvent.newId(), clock.instant(), order.getId(),
                order.getItem().getTitle(), order.getBuyer().getId(), order.getSeller().getId(), previous.name(),
                order.getStatus().name(), detail));
    }

    private OrderDto toDto(Order o, Long viewerId) {
        String role = o.isBuyer(viewerId) ? "BUYER" : o.isSeller(viewerId) ? "SELLER" : "ADMIN";
        boolean reviewed = reviews.existsByOrderIdAndAuthorId(o.getId(), viewerId);
        return new OrderDto(o.getId(), o.getItem().getId(), o.getItem().getTitle(), o.getItem().firstPhotoUrl(),
                UserSummary.from(o.getBuyer()), UserSummary.from(o.getSeller()), o.getItemPrice(),
                o.getShippingPrice(), o.getProtectionFee(), o.getTotal(), o.getStatus().name(),
                o.getShippingOption().name(), o.getShippingAddress(), o.getTrackingNumber(), o.getDisputeReason(),
                role, reviewed, o.getPaidAt(), o.getShippedAt(), o.getDeliveredAt(), o.getCompletedAt(),
                o.getCancelledAt());
    }
}
