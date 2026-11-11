package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Un nouvel utilisateur s'est inscrit (event-carried state transfer : email/username). */
public record UserRegistered(UUID eventId, Instant occurredAt, Long userId, String username, String email) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(userId);
    }

    @Override
    public String topic() {
        return Topics.USERS;
    }
}
