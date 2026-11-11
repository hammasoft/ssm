package com.revivo.marketplace.offer;

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

import java.math.BigDecimal;
import java.time.Instant;

/**
 * Proposition de prix d'un acheteur. Une offre acceptée donne à l'acheteur le droit
 * d'acheter l'article à ce prix pendant une durée limitée.
 */
@Entity
@Table(name = "offers")
public class Offer extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "item_id")
    private Item item;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "buyer_id")
    private User buyer;

    @Column(nullable = false)
    private BigDecimal amount;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private OfferStatus status = OfferStatus.PENDING;

    @Column(name = "expires_at", nullable = false)
    private Instant expiresAt;

    protected Offer() {
    }

    public Offer(Item item, User buyer, BigDecimal amount, Instant expiresAt) {
        this.item = item;
        this.buyer = buyer;
        this.amount = amount;
        this.expiresAt = expiresAt;
    }

    public void accept(Instant now, Instant validUntil) {
        requirePending(now);
        status = OfferStatus.ACCEPTED;
        expiresAt = validUntil;
    }

    public void decline(Instant now) {
        requirePending(now);
        status = OfferStatus.DECLINED;
    }

    public void cancel(Instant now) {
        requirePending(now);
        status = OfferStatus.CANCELLED;
    }

    public void expire() {
        if (status == OfferStatus.PENDING || status == OfferStatus.ACCEPTED) {
            status = OfferStatus.EXPIRED;
        }
    }

    public void markUsed() {
        status = OfferStatus.USED;
    }

    public boolean isUsableBy(Long buyerId, Instant now) {
        return status == OfferStatus.ACCEPTED && buyer.getId().equals(buyerId) && expiresAt.isAfter(now);
    }

    private void requirePending(Instant now) {
        if (status != OfferStatus.PENDING) {
            throw new BusinessException("OFFER_NOT_PENDING", "Cette offre n'est plus en attente.");
        }
        if (!expiresAt.isAfter(now)) {
            status = OfferStatus.EXPIRED;
            throw new BusinessException("OFFER_EXPIRED", "Cette offre a expiré.");
        }
    }

    public Item getItem() {
        return item;
    }

    public User getBuyer() {
        return buyer;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public OfferStatus getStatus() {
        return status;
    }

    public Instant getExpiresAt() {
        return expiresAt;
    }
}
