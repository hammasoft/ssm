package com.revivo.events;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.UUID;

/** Un acheteur propose un prix au vendeur. */
public record OfferMade(UUID eventId, Instant occurredAt, Long offerId, Long itemId, String itemTitle, Long buyerId, String buyerUsername, Long sellerId, BigDecimal amount) implements DomainEvent {

    @Override
    public String key() {
        return String.valueOf(itemId);
    }

    @Override
    public String topic() {
        return Topics.OFFERS;
    }
}
