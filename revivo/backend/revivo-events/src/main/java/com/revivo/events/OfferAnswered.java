package com.revivo.events;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.UUID;

/** Le vendeur a accepté ou refusé une offre. */
public record OfferAnswered(UUID eventId, Instant occurredAt, Long offerId, Long itemId, String itemTitle, Long buyerId, Long sellerId, BigDecimal amount, boolean accepted) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(itemId);
    }

    @Override
    public String topic() {
        return Topics.OFFERS;
    }
}
