package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Un membre suit un autre membre. */
public record UserFollowed(UUID eventId, Instant occurredAt, Long followerId, String followerUsername, Long followedId) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(followedId);
    }

    @Override
    public String topic() {
        return Topics.USERS;
    }
}
