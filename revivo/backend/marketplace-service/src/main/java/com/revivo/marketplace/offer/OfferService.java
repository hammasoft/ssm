package com.revivo.marketplace.offer;

import com.revivo.events.DomainEvent;
import com.revivo.events.OfferAnswered;
import com.revivo.events.OfferMade;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.common.Money;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.config.RevivoProperties;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.offer.OfferDtos.OfferDto;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.math.BigDecimal;
import java.time.Clock;
import java.time.Instant;
import java.util.EnumSet;
import java.util.Optional;

@Service
@Transactional(readOnly = true)
public class OfferService {

    private static final Logger log = LoggerFactory.getLogger(OfferService.class);

    private final OfferRepository offers;
    private final ItemService itemService;
    private final UserService userService;
    private final DomainEventPublisher events;
    private final RevivoProperties.Offers config;
    private final Clock clock;

    public OfferService(OfferRepository offers, ItemService itemService, UserService userService,
                        DomainEventPublisher events, RevivoProperties properties, Clock clock) {
        this.offers = offers;
        this.itemService = itemService;
        this.userService = userService;
        this.events = events;
        this.config = properties.offers();
        this.clock = clock;
    }

    /**
     * Règles : article disponible, pas sa propre annonce, montant ≥ min-ratio × prix et &lt; prix.
     * Une nouvelle offre remplace l'offre en attente précédente du même acheteur.
     */
    @Transactional
    public OfferDto make(Long buyerId, Long itemId, BigDecimal rawAmount) {
        Item item = itemService.find(itemId);
        BigDecimal amount = Money.scale(rawAmount);
        if (item.isOwnedBy(buyerId)) {
            throw new BusinessException("OWN_ITEM", "Vous ne pouvez pas faire d'offre sur votre propre article.");
        }
        if (!item.isPurchasable()) {
            throw new BusinessException("ITEM_NOT_AVAILABLE", "Cet article n'est plus disponible.");
        }
        BigDecimal min = Money.scale(item.getPrice().multiply(config.minRatio()));
        if (amount.compareTo(min) < 0) {
            throw new BusinessException("OFFER_TOO_LOW", "L'offre minimale pour cet article est de " + min + " €.");
        }
        if (amount.compareTo(item.getPrice()) >= 0) {
            throw new BusinessException("OFFER_TOO_HIGH", "L'offre doit être inférieure au prix affiché.");
        }
        Instant now = clock.instant();
        offers.findByItemIdAndBuyerIdAndStatusIn(itemId, buyerId, EnumSet.of(OfferStatus.PENDING))
                .forEach(o -> o.cancel(now));

        User buyer = userService.getUser(buyerId);
        Offer offer = offers.save(new Offer(item, buyer, amount, now.plus(config.pendingTtl())));
        events.publish(new OfferMade(DomainEvent.newId(), now, offer.getId(), itemId, item.getTitle(), buyerId,
                buyer.getUsername(), item.getSeller().getId(), amount));
        return OfferDto.from(offer);
    }

    @Transactional
    public OfferDto accept(Long sellerId, Long offerId) {
        Offer offer = findForSeller(sellerId, offerId);
        Instant now = clock.instant();
        offer.accept(now, now.plus(config.acceptedTtl()));
        publishAnswer(offer, true);
        return OfferDto.from(offer);
    }

    @Transactional
    public OfferDto decline(Long sellerId, Long offerId) {
        Offer offer = findForSeller(sellerId, offerId);
        offer.decline(clock.instant());
        publishAnswer(offer, false);
        return OfferDto.from(offer);
    }

    @Transactional
    public OfferDto cancel(Long buyerId, Long offerId) {
        Offer offer = find(offerId);
        if (!offer.getBuyer().getId().equals(buyerId)) {
            throw new ForbiddenException("Cette offre ne vous appartient pas.");
        }
        offer.cancel(clock.instant());
        return OfferDto.from(offer);
    }

    /** Offre acceptée encore valide pour cet acheteur (utilisée au moment du paiement). */
    public Optional<Offer> usableAcceptedOffer(Long itemId, Long buyerId) {
        Instant now = clock.instant();
        return offers.findByItemIdAndBuyerIdAndStatusIn(itemId, buyerId, EnumSet.of(OfferStatus.ACCEPTED)).stream()
                .filter(o -> o.isUsableBy(buyerId, now))
                .findFirst();
    }

    /** À la vente, toutes les offres ouvertes sur l'article deviennent caduques. */
    @Transactional
    public void closeOpenOffers(Long itemId) {
        offers.findByItemIdAndStatusIn(itemId, EnumSet.of(OfferStatus.PENDING, OfferStatus.ACCEPTED))
                .forEach(Offer::expire);
    }

    public PageResponse<OfferDto> sent(Long buyerId, int page, int size) {
        return PageResponse.of(offers.findByBuyerId(buyerId,
                PageRequest.of(page, size, Sort.by(Sort.Direction.DESC, "createdAt"))), OfferDto::from);
    }

    public PageResponse<OfferDto> received(Long sellerId, int page, int size) {
        return PageResponse.of(offers.findByItemSellerId(sellerId,
                PageRequest.of(page, size, Sort.by(Sort.Direction.DESC, "createdAt"))), OfferDto::from);
    }

    @Scheduled(fixedDelayString = "PT5M", initialDelayString = "PT30S")
    @Transactional
    public void expireOffers() {
        int expired = offers.expireAll(clock.instant());
        if (expired > 0) {
            log.info("{} offres expirées", expired);
        }
    }

    private void publishAnswer(Offer offer, boolean accepted) {
        Item item = offer.getItem();
        events.publish(new OfferAnswered(DomainEvent.newId(), clock.instant(), offer.getId(), item.getId(),
                item.getTitle(), offer.getBuyer().getId(), item.getSeller().getId(), offer.getAmount(), accepted));
    }

    private Offer find(Long id) {
        return offers.findById(id).orElseThrow(() -> new NotFoundException("Offre", id));
    }

    private Offer findForSeller(Long sellerId, Long offerId) {
        Offer offer = find(offerId);
        if (!offer.getItem().isOwnedBy(sellerId)) {
            throw new ForbiddenException("Seul le vendeur peut répondre à cette offre.");
        }
        return offer;
    }
}
