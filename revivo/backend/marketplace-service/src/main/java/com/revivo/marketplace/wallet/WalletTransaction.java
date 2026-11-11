package com.revivo.marketplace.wallet;

import com.revivo.marketplace.common.BaseEntity;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

import java.math.BigDecimal;

/** Journal immuable des mouvements (append-only, base de l'audit comptable). */
@Entity
@Table(name = "wallet_transactions")
public class WalletTransaction extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "wallet_id")
    private Wallet wallet;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private WalletTransactionType type;

    @Column(nullable = false)
    private BigDecimal amount;

    @Column(name = "order_id")
    private Long orderId;

    private String description;

    protected WalletTransaction() {
    }

    WalletTransaction(Wallet wallet, WalletTransactionType type, BigDecimal amount, Long orderId, String description) {
        this.wallet = wallet;
        this.type = type;
        this.amount = amount;
        this.orderId = orderId;
        this.description = description;
    }

    public WalletTransactionType getType() {
        return type;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public Long getOrderId() {
        return orderId;
    }

    public String getDescription() {
        return description;
    }
}
