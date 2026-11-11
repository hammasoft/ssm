package com.revivo.marketplace.item;

import com.revivo.marketplace.user.UserDtos.UserSummary;
import jakarta.validation.constraints.DecimalMax;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.Digits;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotEmpty;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;

public final class ItemDtos {

    private ItemDtos() {
    }

    public record ItemRequest(
            @NotBlank @Size(min = 5, max = 100) String title,
            @NotBlank @Size(min = 10, max = 3000) String description,
            @NotNull Long categoryId,
            Long brandId,
            @Size(max = 20) String size,
            @Size(max = 30) String color,
            @NotNull ItemCondition condition,
            @NotNull @DecimalMin("1.00") @DecimalMax("50000.00") @Digits(integer = 8, fraction = 2) BigDecimal price,
            @NotEmpty @Size(max = Item.MAX_PHOTOS) List<@NotBlank String> photoUrls) {
    }

    /** Vignette de la grille de résultats. */
    public record ItemCard(Long id, String title, BigDecimal price, BigDecimal priceWithProtection, String size,
                           String brand, String condition, String photoUrl, String status, int favoriteCount,
                           boolean favorited, UserSummary seller) {
    }

    public record ItemDetail(Long id, String title, String description, BigDecimal price,
                             BigDecimal priceWithProtection, String size, String color, String condition,
                             String conditionLabel, Long categoryId, String categoryName, Long brandId,
                             String brand, List<String> photos, String status, int viewCount, int favoriteCount,
                             boolean favorited, boolean mine, boolean purchasable, UserSummary seller,
                             Instant createdAt, Instant updatedAt) {
    }
}
