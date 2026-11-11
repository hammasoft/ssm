package com.revivo.notification.config;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.validation.annotation.Validated;

@Validated
@ConfigurationProperties(prefix = "revivo")
public record NotificationProperties(@NotNull Security security, @NotNull Mail mail, @NotBlank String frontendUrl) {

    public record Security(@NotBlank String jwtSecret) {
    }

    public record Mail(boolean enabled, @NotBlank String from) {
    }
}
