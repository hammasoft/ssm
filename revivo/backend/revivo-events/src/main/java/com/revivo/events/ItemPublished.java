package com.revivo.events;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;
import java.util.UUID;

/** Un article a été mis en vente. Les abonnés du vendeur sont notifiés. */
public record ItemPublished(UUID eventId, Instant occurredAt, Long itemId, Long sellerId, String sellerUsername, String title, BigDecimal price, List<Long> followerIds) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(itemId);
    }

    @Override
    public String topic() {
        return Topics.CATALOG;
    }
}
