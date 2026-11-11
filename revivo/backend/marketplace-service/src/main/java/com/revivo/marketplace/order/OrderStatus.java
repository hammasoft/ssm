package com.revivo.marketplace.order;

import java.util.EnumSet;
import java.util.Map;
import java.util.Set;

/**
 * Machine à états d'une commande.
 * <pre>
 *            ┌──────────► CANCELLED  (avant expédition, remboursement)
 *  PAID ─────┼──────────► SHIPPED ──► DELIVERED ──► COMPLETED (fonds libérés au vendeur)
 *            │               │            │
 *            │               └──► DISPUTED ◄┘ ──► REFUNDED | COMPLETED (arbitrage admin)
 *            └──► COMPLETED  (remise en main propre uniquement)
 * </pre>
 */
public enum OrderStatus {
    PAID, SHIPPED, DELIVERED, COMPLETED, CANCELLED, DISPUTED, REFUNDED;

    private static final Map<OrderStatus, Set<OrderStatus>> TRANSITIONS = Map.of(
            PAID, EnumSet.of(SHIPPED, CANCELLED, COMPLETED),
            SHIPPED, EnumSet.of(DELIVERED, COMPLETED, DISPUTED),
            DELIVERED, EnumSet.of(COMPLETED, DISPUTED),
            DISPUTED, EnumSet.of(COMPLETED, REFUNDED),
            COMPLETED, EnumSet.noneOf(OrderStatus.class),
            CANCELLED, EnumSet.noneOf(OrderStatus.class),
            REFUNDED, EnumSet.noneOf(OrderStatus.class));

    public boolean canTransitionTo(OrderStatus target) {
        return TRANSITIONS.get(this).contains(target);
    }

    public boolean isTerminal() {
        return TRANSITIONS.get(this).isEmpty();
    }
}
