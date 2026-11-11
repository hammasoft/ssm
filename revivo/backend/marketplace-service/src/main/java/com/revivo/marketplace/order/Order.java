package com.revivo.marketplace.order;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import jakarta.persistence.Version;

import java.math.BigDecimal;
import java.time.Instant;

/** Agrégat « Commande » : porte les montants figés au moment du paiement et la machine à états. */
@Entity
@Table(name = "orders")
public class Order extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "item_id")
    private Item item;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "buyer_id")
    private User buyer;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "seller_id")
    private User seller;

    @Column(name = "offer_id")
    private Long offerId;

    @Column(name = "item_price", nullable = false)
    private BigDecimal itemPrice;

    @Column(name = "shipping_price", nullable = false)
    private BigDecimal shippingPrice;

    @Column(name = "protection_fee", nullable = false)
    private BigDecimal protectionFee;

    @Column(nullable = false)
    private BigDecimal total;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private OrderStatus status;

    @Enumerated(EnumType.STRING)
    @Column(name = "shipping_option", nullable = false)
    private ShippingOption shippingOption;

    @Column(name = "shipping_address")
    private String shippingAddress;

    @Column(name = "tracking_number")
    private String trackingNumber;

    @Column(name = "payment_reference", nullable = false)
    private String paymentReference;

    @Column(name = "dispute_reason")
    private String disputeReason;

    @Column(name = "paid_at")
    private Instant paidAt;
    @Column(name = "shipped_at")
    private Instant shippedAt;
    @Column(name = "delivered_at")
    private Instant deliveredAt;
    @Column(name = "completed_at")
    private Instant completedAt;
    @Column(name = "cancelled_at")
    private Instant cancelledAt;

    @Version
    private long version;

    protected Order() {
    }

    public Order(Item item, User buyer, Long offerId, BigDecimal itemPrice, BigDecimal shippingPrice,
                 BigDecimal protectionFee, ShippingOption shippingOption, String shippingAddress,
                 String paymentReference, Instant paidAt) {
        this.item = item;
        this.buyer = buyer;
        this.seller = item.getSeller();
        this.offerId = offerId;
        this.itemPrice = itemPrice;
        this.shippingPrice = shippingPrice;
        this.protectionFee = protectionFee;
        this.total = itemPrice.add(shippingPrice).add(protectionFee);
        this.shippingOption = shippingOption;
        this.shippingAddress = shippingAddress;
        this.paymentReference = paymentReference;
        this.status = OrderStatus.PAID;
        this.paidAt = paidAt;
    }

    /** Applique une transition en vérifiant la machine à états ; renvoie l'ancien statut. */
    OrderStatus transitionTo(OrderStatus target, Instant now) {
        if (!status.canTransitionTo(target)) {
            throw new BusinessException("INVALID_ORDER_TRANSITION",
                    "Transition impossible de " + status + " vers " + target + ".");
        }
        OrderStatus previous = status;
        status = target;
        switch (target) {
            case SHIPPED -> shippedAt = now;
            case DELIVERED -> deliveredAt = now;
            case COMPLETED -> completedAt = now;
            case CANCELLED, REFUNDED -> cancelledAt = now;
            default -> { }
        }
        return previous;
    }

    void ship(String trackingNumber, Instant now) {
        transitionTo(OrderStatus.SHIPPED, now);
        this.trackingNumber = trackingNumber;
    }

    void dispute(String reason, Instant now) {
        transitionTo(OrderStatus.DISPUTED, now);
        this.disputeReason = reason;
    }

    public boolean involves(Long userId) {
        return buyer.getId().equals(userId) || seller.getId().equals(userId);
    }

    public boolean isBuyer(Long userId) {
        return buyer.getId().equals(userId);
    }

    public boolean isSeller(Long userId) {
        return seller.getId().equals(userId);
    }

    public Item getItem() {
        return item;
    }

    public User getBuyer() {
        return buyer;
    }

    public User getSeller() {
        return seller;
    }

    public Long getOfferId() {
        return offerId;
    }

    public BigDecimal getItemPrice() {
        return itemPrice;
    }

    public BigDecimal getShippingPrice() {
        return shippingPrice;
    }

    public BigDecimal getProtectionFee() {
        return protectionFee;
    }

    public BigDecimal getTotal() {
        return total;
    }

    public OrderStatus getStatus() {
        return status;
    }

    public ShippingOption getShippingOption() {
        return shippingOption;
    }

    public String getShippingAddress() {
        return shippingAddress;
    }

    public String getTrackingNumber() {
        return trackingNumber;
    }

    public String getPaymentReference() {
        return paymentReference;
    }

    public String getDisputeReason() {
        return disputeReason;
    }

    public Instant getPaidAt() {
        return paidAt;
    }

    public Instant getShippedAt() {
        return shippedAt;
    }

    public Instant getDeliveredAt() {
        return deliveredAt;
    }

    public Instant getCompletedAt() {
        return completedAt;
    }

    public Instant getCancelledAt() {
        return cancelledAt;
    }
}
