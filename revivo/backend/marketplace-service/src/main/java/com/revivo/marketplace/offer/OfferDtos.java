package com.revivo.marketplace.offer;

import com.revivo.marketplace.user.UserDtos.UserSummary;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.Digits;
import jakarta.validation.constraints.NotNull;

import java.math.BigDecimal;
import java.time.Instant;

public final class OfferDtos {

    private OfferDtos() {
    }

    public record MakeOfferRequest(@NotNull @DecimalMin("1.00") @Digits(integer = 8, fraction = 2) BigDecimal amount) {
    }

    public record OfferDto(Long id, Long itemId, String itemTitle, String itemPhotoUrl, BigDecimal itemPrice,
                           BigDecimal amount, String status, Instant expiresAt, UserSummary buyer,
                           UserSummary seller, Instant createdAt) {

        static OfferDto from(Offer o) {
            var item = o.getItem();
            return new OfferDto(o.getId(), item.getId(), item.getTitle(), item.firstPhotoUrl(), item.getPrice(),
                    o.getAmount(), o.getStatus().name(), o.getExpiresAt(), UserSummary.from(o.getBuyer()),
                    UserSummary.from(item.getSeller()), o.getCreatedAt());
        }
    }
}
