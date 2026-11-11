package com.revivo.events;

import java.time.Instant;
import java.util.UUID;

/** Nouveau message privé. */
public record MessageSent(UUID eventId, Instant occurredAt, Long messageId, Long conversationId, Long senderId, String senderUsername, Long recipientId, String preview) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(conversationId);
    }

    @Override
    public String topic() {
        return Topics.MESSAGING;
    }
}
