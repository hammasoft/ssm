package com.revivo.notification.application;

import com.revivo.events.DomainEvent;
import com.revivo.events.ItemPriceDropped;
import com.revivo.events.MessageSent;
import com.revivo.events.OfferAnswered;
import com.revivo.events.OrderPlaced;
import com.revivo.events.OrderStatusChanged;
import org.junit.jupiter.api.Test;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;

import static org.assertj.core.api.Assertions.assertThat;

class NotificationFactoryTest {

    private final NotificationFactory factory = new NotificationFactory();
    private final Instant now = Instant.parse("2026-01-01T10:00:00Z");

    @Test
    void orderPlacedNotifiesSellerAndBuyerByEmail() {
        var drafts = factory.draftsFor(new OrderPlaced(DomainEvent.newId(), now, 5L, 10L, "Veste", 2L, 1L,
                new BigDecimal("20.00"), new BigDecimal("24.79")));

        assertThat(drafts).extracting(NotificationDraft::userId).containsExactly(1L, 2L);
        assertThat(drafts).allMatch(NotificationDraft::sendEmail);
        assertThat(drafts.get(0).type()).isEqualTo("ITEM_SOLD");
        assertThat(drafts.get(1).body()).contains("24,79 €");
    }

    @Test
    void priceDropFansOutToEveryInterestedUser() {
        var drafts = factory.draftsFor(new ItemPriceDropped(DomainEvent.newId(), now, 10L, 1L, "Veste",
                new BigDecimal("30"), new BigDecimal("25"), List.of(3L, 4L, 5L)));

        assertThat(drafts).hasSize(3).allMatch(d -> d.type().equals("PRICE_DROP") && d.link().equals("/items/10"));
    }

    @Test
    void declinedOfferIsInAppOnly() {
        var drafts = factory.draftsFor(new OfferAnswered(DomainEvent.newId(), now, 7L, 10L, "Veste", 2L, 1L,
                new BigDecimal("15"), false));
        assertThat(drafts).singleElement().satisfies(d -> {
            assertThat(d.type()).isEqualTo("OFFER_DECLINED");
            assertThat(d.sendEmail()).isFalse();
        });
    }

    @Test
    void completedOrderReleasesFundsAndAsksForReview() {
        var drafts = factory.draftsFor(new OrderStatusChanged(DomainEvent.newId(), now, 5L, "Veste", 2L, 1L,
                "DELIVERED", "COMPLETED", null));
        assertThat(drafts).extracting(NotificationDraft::type).containsExactly("FUNDS_RELEASED", "REVIEW_REQUEST");
    }

    @Test
    void messageLinksToConversation() {
        var drafts = factory.draftsFor(new MessageSent(DomainEvent.newId(), now, 1L, 33L, 2L, "bob", 1L, "Bonjour !"));
        assertThat(drafts).singleElement().extracting(NotificationDraft::link).isEqualTo("/inbox/33");
    }
}
