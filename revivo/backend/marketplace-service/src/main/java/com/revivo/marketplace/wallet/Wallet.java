package com.revivo.marketplace.wallet;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.OneToOne;
import jakarta.persistence.Table;
import jakarta.persistence.Version;

import java.math.BigDecimal;

/**
 * Porte-monnaie vendeur.
 * <ul>
 *   <li>{@code pending} : fonds sous séquestre (commande payée mais pas encore validée par l'acheteur)</li>
 *   <li>{@code available} : fonds libérés, retirables vers un compte bancaire</li>
 * </ul>
 */
@Entity
@Table(name = "wallets")
public class Wallet extends BaseEntity {

    @OneToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "user_id", unique = true)
    private User user;

    @Column(name = "available_balance", nullable = false)
    private BigDecimal available = BigDecimal.ZERO;

    @Column(name = "pending_balance", nullable = false)
    private BigDecimal pending = BigDecimal.ZERO;

    @Version
    private long version;

    protected Wallet() {
    }

    public Wallet(User user) {
        this.user = user;
    }

    void creditPending(BigDecimal amount) {
        requirePositive(amount);
        pending = pending.add(amount);
    }

    void releasePending(BigDecimal amount) {
        requirePositive(amount);
        if (pending.compareTo(amount) < 0) {
            throw new IllegalStateException("Séquestre insuffisant");
        }
        pending = pending.subtract(amount);
        available = available.add(amount);
    }

    void cancelPending(BigDecimal amount) {
        requirePositive(amount);
        if (pending.compareTo(amount) < 0) {
            throw new IllegalStateException("Séquestre insuffisant");
        }
        pending = pending.subtract(amount);
    }

    void withdraw(BigDecimal amount) {
        requirePositive(amount);
        if (available.compareTo(amount) < 0) {
            throw new BusinessException("INSUFFICIENT_FUNDS", "Solde disponible insuffisant.");
        }
        available = available.subtract(amount);
    }

    private static void requirePositive(BigDecimal amount) {
        if (amount == null || amount.signum() <= 0) {
            throw new IllegalArgumentException("Le montant doit être positif");
        }
    }

    public User getUser() {
        return user;
    }

    public BigDecimal getAvailable() {
        return available;
    }

    public BigDecimal getPending() {
        return pending;
    }
}
