package com.revivo.events;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;
import java.util.UUID;

/** Baisse de prix : les membres ayant mis l'article en favori sont notifiés. */
public record ItemPriceDropped(UUID eventId, Instant occurredAt, Long itemId, Long sellerId, String title, BigDecimal oldPrice, BigDecimal newPrice, List<Long> interestedUserIds) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(itemId);
    }

    @Override
    public String topic() {
        return Topics.CATALOG;
    }
}
