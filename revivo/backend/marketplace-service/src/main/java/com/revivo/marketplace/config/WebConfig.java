package com.revivo.marketplace.config;

import org.springframework.context.annotation.Configuration;
import org.springframework.web.servlet.config.annotation.ResourceHandlerRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;

import java.nio.file.Path;
import java.time.Duration;

/** Sert les photos téléversées (en production : remplacer par S3/MinIO + CDN, cf. ADR-005). */
@Configuration
public class WebConfig implements WebMvcConfigurer {

    private final RevivoProperties properties;

    public WebConfig(RevivoProperties properties) {
        this.properties = properties;
    }

    @Override
    public void addResourceHandlers(ResourceHandlerRegistry registry) {
        String location = Path.of(properties.storage().uploadDir()).toAbsolutePath().normalize().toUri().toString();
        registry.addResourceHandler("/uploads/**")
                .addResourceLocations(location.endsWith("/") ? location : location + "/")
                .setCachePeriod((int) Duration.ofDays(30).toSeconds());
    }
}
