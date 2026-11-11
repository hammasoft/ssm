package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Un article a été ajouté en favori. */
public record ItemFavorited(UUID eventId, Instant occurredAt, Long itemId, Long sellerId, Long userId, String username, String title) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(itemId);
    }

    @Override
    public String topic() {
        return Topics.CATALOG;
    }
}
