package com.revivo.events;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.UUID;

/** Commande payée : le montant est placé sous séquestre (protection acheteur). */
public record OrderPlaced(UUID eventId, Instant occurredAt, Long orderId, Long itemId, String itemTitle, Long buyerId, Long sellerId, BigDecimal itemPrice, BigDecimal total) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(orderId);
    }

    @Override
    public String topic() {
        return Topics.ORDERS;
    }
}
