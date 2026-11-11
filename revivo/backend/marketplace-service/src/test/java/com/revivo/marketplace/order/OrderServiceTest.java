package com.revivo.marketplace.order;

import com.revivo.events.DomainEvent;
import com.revivo.events.OrderPlaced;
import com.revivo.events.OrderStatusChanged;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.config.RevivoProperties;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemRepository;
import com.revivo.marketplace.item.ItemStatus;
import com.revivo.marketplace.offer.OfferService;
import com.revivo.marketplace.order.OrderDtos.CheckoutRequest;
import com.revivo.marketplace.order.OrderDtos.OrderDto;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.payment.PaymentDeclinedException;
import com.revivo.marketplace.payment.PaymentGateway;
import com.revivo.marketplace.review.ReviewRepository;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserService;
import com.revivo.marketplace.wallet.WalletService;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Nested;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.ArgumentCaptor;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.test.util.ReflectionTestUtils;

import java.math.BigDecimal;
import java.time.Clock;
import java.time.Instant;
import java.time.ZoneOffset;
import java.util.Optional;

import static com.revivo.marketplace.TestFixtures.item;
import static com.revivo.marketplace.TestFixtures.order;
import static com.revivo.marketplace.TestFixtures.user;
import static org.assertj.core.api.Assertions.assertThat;
import static org.assertj.core.api.Assertions.assertThatThrownBy;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.lenient;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoInteractions;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class OrderServiceTest {

    @Mock OrderRepository orders;
    @Mock ItemRepository items;
    @Mock OfferService offerService;
    @Mock UserService userService;
    @Mock WalletService walletService;
    @Mock PaymentGateway paymentGateway;
    @Mock ReviewRepository reviews;
    @Mock DomainEventPublisher events;

    OrderService service;
    User seller = user(1, "alice");
    User buyer = user(2, "bob");

    @BeforeEach
    void setUp() {
        FeeCalculator fees = new FeeCalculator(new RevivoProperties(null, null,
                new RevivoProperties.Fees(new BigDecimal("0.50"), new BigDecimal("0.04"), new BigDecimal("3.49"),
                        new BigDecimal("5.99")), null, null, null, false));
        Clock clock = Clock.fixed(Instant.parse("2026-03-01T12:00:00Z"), ZoneOffset.UTC);
        service = new OrderService(orders, items, offerService, userService, walletService, paymentGateway, fees,
                reviews, events, clock);
    }

    @Nested
    class Checkout {

        Item item;

        @BeforeEach
        void setUp() {
            item = item(10, seller, "20.00");
            lenient().when(items.lockById(10L)).thenReturn(Optional.of(item));
            lenient().when(userService.getUser(2L)).thenReturn(buyer);
            lenient().when(offerService.usableAcceptedOffer(10L, 2L)).thenReturn(Optional.empty());
            lenient().when(orders.saveAndFlush(any(Order.class))).thenAnswer(inv -> {
                Order o = inv.getArgument(0);
                ReflectionTestUtils.setField(o, "id", 99L);
                return o;
            });
        }

        @Test
        void chargesBuyerPutsSellerAmountInEscrowAndPublishesEvent() {
            when(paymentGateway.charge(eq(2L), any(), eq("tok_visa"), anyString())).thenReturn("pay_1");

            OrderDto dto = service.checkout(2L, 10L,
                    new CheckoutRequest(ShippingOption.RELAY_POINT, "1 rue de la Paix", "tok_visa"));

            // 20.00 + protection (0.50 + 4 %) 1.30 + point relais 3.49
            assertThat(dto.total()).isEqualByComparingTo("24.79");
            assertThat(dto.status()).isEqualTo("PAID");
            assertThat(item.getStatus()).isEqualTo(ItemStatus.SOLD);
            verify(walletService).escrow(1L, 99L, new BigDecimal("20.00"));
            verify(offerService).closeOpenOffers(10L);

            ArgumentCaptor<DomainEvent> captor = ArgumentCaptor.forClass(DomainEvent.class);
            verify(events).publish(captor.capture());
            assertThat(captor.getValue()).isInstanceOfSatisfying(OrderPlaced.class, e -> {
                assertThat(e.sellerId()).isEqualTo(1L);
                assertThat(e.total()).isEqualByComparingTo("24.79");
            });
        }

        @Test
        void cannotBuyOwnItem() {
            assertThatThrownBy(() -> service.checkout(1L, 10L,
                    new CheckoutRequest(ShippingOption.HAND_DELIVERY, null, "tok_visa")))
                    .isInstanceOf(BusinessException.class).hasMessageContaining("propre article");
            verifyNoInteractions(paymentGateway);
        }

        @Test
        void addressIsRequiredForShippedOrders() {
            assertThatThrownBy(() -> service.checkout(2L, 10L,
                    new CheckoutRequest(ShippingOption.HOME_DELIVERY, " ", "tok_visa")))
                    .isInstanceOf(BusinessException.class)
                    .extracting("code").isEqualTo("ADDRESS_REQUIRED");
        }

        @Test
        void declinedPaymentLeavesItemAvailable() {
            when(paymentGateway.charge(any(), any(), eq("tok_declined"), anyString()))
                    .thenThrow(new PaymentDeclinedException("refusé"));

            assertThatThrownBy(() -> service.checkout(2L, 10L,
                    new CheckoutRequest(ShippingOption.HAND_DELIVERY, null, "tok_declined")))
                    .isInstanceOf(PaymentDeclinedException.class);
            assertThat(item.getStatus()).isEqualTo(ItemStatus.ACTIVE);
            verifyNoInteractions(walletService, events);
        }

        @Test
        void soldItemCannotBeBoughtAgain() {
            item.markSold();
            assertThatThrownBy(() -> service.checkout(2L, 10L,
                    new CheckoutRequest(ShippingOption.HAND_DELIVERY, null, "tok_visa")))
                    .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("ITEM_NOT_AVAILABLE");
        }

        @Test
        void paymentIsRefundedWhenPersistenceFails() {
            when(paymentGateway.charge(any(), any(), any(), anyString())).thenReturn("pay_2");
            when(orders.saveAndFlush(any(Order.class))).thenThrow(new IllegalStateException("db down"));

            assertThatThrownBy(() -> service.checkout(2L, 10L,
                    new CheckoutRequest(ShippingOption.HAND_DELIVERY, null, "tok_visa")))
                    .isInstanceOf(IllegalStateException.class);
            verify(paymentGateway).refund(eq("pay_2"), any());
        }
    }

    @Nested
    class Lifecycle {

        Item item;
        Order order;

        @BeforeEach
        void setUp() {
            item = item(10, seller, "20.00");
            item.markSold();
            order = order(5, item, buyer, ShippingOption.RELAY_POINT);
            when(orders.findById(5L)).thenReturn(Optional.of(order));
        }

        @Test
        void onlySellerCanShip() {
            assertThatThrownBy(() -> service.ship(2L, 5L, "TRK1")).isInstanceOf(ForbiddenException.class);
        }

        @Test
        void fullHappyPathReleasesFundsToSeller() {
            service.ship(1L, 5L, "TRK1");
            assertThat(order.getStatus()).isEqualTo(OrderStatus.SHIPPED);
            assertThat(order.getTrackingNumber()).isEqualTo("TRK1");

            service.confirmReception(2L, 5L);

            assertThat(order.getStatus()).isEqualTo(OrderStatus.COMPLETED);
            verify(walletService).release(1L, 5L, new BigDecimal("20.00"));
            ArgumentCaptor<DomainEvent> captor = ArgumentCaptor.forClass(DomainEvent.class);
            verify(events, org.mockito.Mockito.times(2)).publish(captor.capture());
            assertThat(captor.getAllValues()).extracting(e -> ((OrderStatusChanged) e).newStatus())
                    .containsExactly("SHIPPED", "COMPLETED");
        }

        @Test
        void buyerCannotConfirmBeforeShipment() {
            assertThatThrownBy(() -> service.confirmReception(2L, 5L))
                    .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("NOT_SHIPPED");
            verify(walletService, never()).release(any(), any(), any());
        }

        @Test
        void cancellationRefundsBuyerAndRelistsItem() {
            service.cancel(1L, 5L, "Article abîmé");

            assertThat(order.getStatus()).isEqualTo(OrderStatus.CANCELLED);
            assertThat(item.getStatus()).isEqualTo(ItemStatus.ACTIVE);
            verify(paymentGateway).refund("pay_123", order.getTotal());
            verify(walletService).cancelEscrow(1L, 5L, new BigDecimal("20.00"));
        }

        @Test
        void shippedOrderCannotBeCancelled() {
            service.ship(1L, 5L, "TRK1");
            assertThatThrownBy(() -> service.cancel(2L, 5L, "trop tard"))
                    .isInstanceOf(BusinessException.class).extracting("code").isEqualTo("CANNOT_CANCEL");
        }

        @Test
        void disputeResolvedInFavourOfBuyerRefundsHim() {
            service.ship(1L, 5L, "TRK1");
            service.openDispute(2L, 5L, "Article non conforme");
            assertThat(order.getStatus()).isEqualTo(OrderStatus.DISPUTED);

            service.resolveDispute(100L, 5L, true);

            assertThat(order.getStatus()).isEqualTo(OrderStatus.REFUNDED);
            verify(walletService).cancelEscrow(1L, 5L, new BigDecimal("20.00"));
        }
    }
}
