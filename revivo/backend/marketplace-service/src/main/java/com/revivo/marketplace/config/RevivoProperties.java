package com.revivo.marketplace.config;

import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.validation.annotation.Validated;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.Duration;
import java.util.List;

/**
 * Configuration typée et validée au démarrage (fail-fast) de l'application.
 */
@Validated
@ConfigurationProperties(prefix = "revivo")
public record RevivoProperties(
        @NotNull Security security,
        @NotNull Storage storage,
        @NotNull Fees fees,
        @NotNull Offers offers,
        @NotNull Orders orders,
        @NotNull Outbox outbox,
        boolean demoData) {

    public record Security(@NotBlank String jwtSecret, @NotNull Duration accessTokenTtl,
                           @NotNull Duration refreshTokenTtl, List<String> corsAllowedOrigins) {
    }

    public record Storage(@NotBlank String uploadDir) {
    }

    /** Frais de protection acheteur = fixe + taux × prix ; tarifs de livraison forfaitaires. */
    public record Fees(@NotNull BigDecimal protectionFixed, @NotNull BigDecimal protectionRate,
                       @NotNull BigDecimal relayPointShipping, @NotNull BigDecimal homeDeliveryShipping) {
    }

    public record Offers(@NotNull BigDecimal minRatio, @NotNull Duration pendingTtl, @NotNull Duration acceptedTtl) {
    }

    public record Orders(@NotNull Duration autoCompleteAfter) {
    }

    public record Outbox(long pollIntervalMs, int batchSize) {
    }
}
