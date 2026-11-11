package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Un avis a été laissé suite à une transaction. */
public record ReviewPosted(UUID eventId, Instant occurredAt, Long reviewId, Long orderId, Long authorId, String authorUsername, Long targetUserId, int rating) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(targetUserId);
    }

    @Override
    public String topic() {
        return Topics.REVIEWS;
    }
}
