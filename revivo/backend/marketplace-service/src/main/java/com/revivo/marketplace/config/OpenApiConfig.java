package com.revivo.marketplace.config;

import io.swagger.v3.oas.models.Components;
import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Info;
import io.swagger.v3.oas.models.info.License;
import io.swagger.v3.oas.models.security.SecurityRequirement;
import io.swagger.v3.oas.models.security.SecurityScheme;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenApiConfig {

    @Bean
    OpenAPI revivoOpenApi() {
        return new OpenAPI()
                .info(new Info()
                        .title("Revivo — Marketplace API")
                        .version("v1")
                        .description("API REST de la marketplace de seconde main Revivo : catalogue, offres, "
                                + "commandes avec paiement sous séquestre, porte-monnaie, messagerie, avis, modération.")
                        .license(new License().name("MIT")))
                .components(new Components().addSecuritySchemes("bearer",
                        new SecurityScheme().type(SecurityScheme.Type.HTTP).scheme("bearer").bearerFormat("JWT")))
                .addSecurityItem(new SecurityRequirement().addList("bearer"));
    }
}
