package com.revivo.marketplace.offer;

import com.revivo.events.OfferAnswered;
import com.revivo.events.OfferMade;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.config.RevivoProperties;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserService;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.test.util.ReflectionTestUtils;

import java.math.BigDecimal;
import java.time.Clock;
import java.time.Duration;
import java.time.Instant;
import java.time.ZoneOffset;
import java.util.List;
import java.util.Optional;

import static com.revivo.marketplace.TestFixtures.item;
import static com.revivo.marketplace.TestFixtures.user;
import static org.assertj.core.api.Assertions.assertThat;
import static org.assertj.core.api.Assertions.assertThatThrownBy;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.argThat;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class OfferServiceTest {

    static final Instant NOW = Instant.parse("2026-03-01T12:00:00Z");

    @Mock OfferRepository offers;
    @Mock ItemService itemService;
    @Mock UserService userService;
    @Mock DomainEventPublisher events;

    OfferService service;
    User seller = user(1, "alice");
    User buyer = user(2, "bob");
    Item item = item(10, seller, "20.00");

    @BeforeEach
    void setUp() {
        RevivoProperties props = new RevivoProperties(null, null, null,
                new RevivoProperties.Offers(new BigDecimal("0.30"), Duration.ofHours(48), Duration.ofHours(24)),
                null, null, false);
        service = new OfferService(offers, itemService, userService, events, props,
                Clock.fixed(NOW, ZoneOffset.UTC));
    }

    @Test
    void makeOfferReplacesPreviousPendingOfferAndNotifiesSeller() {
        Offer previous = new Offer(item, buyer, new BigDecimal("12.00"), NOW.plusSeconds(3600));
        when(itemService.find(10L)).thenReturn(item);
        when(userService.getUser(2L)).thenReturn(buyer);
        when(offers.findByItemIdAndBuyerIdAndStatusIn(any(), any(), any())).thenReturn(List.of(previous));
        when(offers.save(any(Offer.class))).thenAnswer(inv -> {
            Offer o = inv.getArgument(0);
            ReflectionTestUtils.setField(o, "id", 7L);
            return o;
        });

        var dto = service.make(2L, 10L, new BigDecimal("15"));

        assertThat(dto.amount()).isEqualByComparingTo("15.00");
        assertThat(dto.expiresAt()).isEqualTo(NOW.plus(Duration.ofHours(48)));
        assertThat(previous.getStatus()).isEqualTo(OfferStatus.CANCELLED);
        verify(events).publish(argThat(e -> e instanceof OfferMade m && m.sellerId() == 1L));
    }

    @Test
    void offerBelowMinimumRatioIsRejected() {
        when(itemService.find(10L)).thenReturn(item);
        assertThatThrownBy(() -> service.make(2L, 10L, new BigDecimal("5.99")))
                .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("OFFER_TOO_LOW");
    }

    @Test
    void offerAtOrAbovePriceIsRejected() {
        when(itemService.find(10L)).thenReturn(item);
        assertThatThrownBy(() -> service.make(2L, 10L, new BigDecimal("20.00")))
                .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("OFFER_TOO_HIGH");
    }

    @Test
    void sellerCannotOfferOnOwnItem() {
        when(itemService.find(10L)).thenReturn(item);
        assertThatThrownBy(() -> service.make(1L, 10L, new BigDecimal("15")))
                .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("OWN_ITEM");
    }

    @Test
    void onlySellerCanAccept() {
        Offer offer = new Offer(item, buyer, new BigDecimal("15.00"), NOW.plusSeconds(3600));
        when(offers.findById(7L)).thenReturn(Optional.of(offer));
        assertThatThrownBy(() -> service.accept(2L, 7L)).isInstanceOf(ForbiddenException.class);
    }

    @Test
    void acceptedOfferIsUsableByBuyerForLimitedTime() {
        Offer offer = new Offer(item, buyer, new BigDecimal("15.00"), NOW.plusSeconds(3600));
        when(offers.findById(7L)).thenReturn(Optional.of(offer));

        service.accept(1L, 7L);

        assertThat(offer.getStatus()).isEqualTo(OfferStatus.ACCEPTED);
        assertThat(offer.isUsableBy(2L, NOW.plus(Duration.ofHours(23)))).isTrue();
        assertThat(offer.isUsableBy(2L, NOW.plus(Duration.ofHours(25)))).isFalse();
        assertThat(offer.isUsableBy(3L, NOW)).isFalse();
        verify(events).publish(argThat(e -> e instanceof OfferAnswered a && a.accepted()));
    }

    @Test
    void expiredOfferCannotBeAccepted() {
        Offer offer = new Offer(item, buyer, new BigDecimal("15.00"), NOW.minusSeconds(1));
        when(offers.findById(7L)).thenReturn(Optional.of(offer));
        assertThatThrownBy(() -> service.accept(1L, 7L))
                .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("OFFER_EXPIRED");
    }
}
