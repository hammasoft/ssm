package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Transition du cycle de vie d'une commande (SHIPPED, DELIVERED, COMPLETED, CANCELLED, DISPUTED, REFUNDED). */
public record OrderStatusChanged(UUID eventId, Instant occurredAt, Long orderId, String itemTitle, Long buyerId, Long sellerId, String previousStatus, String newStatus, String detail) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(orderId);
    }

    @Override
    public String topic() {
        return Topics.ORDERS;
    }
}
