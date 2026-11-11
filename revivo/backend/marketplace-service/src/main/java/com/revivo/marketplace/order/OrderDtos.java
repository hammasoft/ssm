package com.revivo.marketplace.order;

import com.revivo.marketplace.user.UserDtos.UserSummary;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

import java.math.BigDecimal;
import java.time.Instant;

public final class OrderDtos {

    private OrderDtos() {
    }

    public record CheckoutRequest(@NotNull ShippingOption shippingOption,
                                  @Size(max = 500) String shippingAddress,
                                  @NotBlank String paymentMethodToken) {
    }

    /** Récapitulatif affiché avant paiement. */
    public record CheckoutQuote(Long itemId, BigDecimal itemPrice, boolean offerApplied, BigDecimal protectionFee,
                                BigDecimal shippingPrice, BigDecimal total) {
    }

    public record ShipRequest(@Size(max = 100) String trackingNumber) {
    }

    public record ReasonRequest(@NotBlank @Size(max = 1000) String reason) {
    }

    public record OrderDto(Long id, Long itemId, String itemTitle, String itemPhotoUrl, UserSummary buyer,
                           UserSummary seller, BigDecimal itemPrice, BigDecimal shippingPrice,
                           BigDecimal protectionFee, BigDecimal total, String status, String shippingOption,
                           String shippingAddress, String trackingNumber, String disputeReason, String role,
                           boolean reviewedByMe, Instant paidAt, Instant shippedAt, Instant deliveredAt,
                           Instant completedAt, Instant cancelledAt) {
    }
}
