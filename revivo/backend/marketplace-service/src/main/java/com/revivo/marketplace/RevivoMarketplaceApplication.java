package com.revivo.marketplace;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.security.servlet.UserDetailsServiceAutoConfiguration;
import org.springframework.boot.context.properties.ConfigurationPropertiesScan;
import org.springframework.scheduling.annotation.EnableScheduling;

// Authentification 100 % JWT : pas d'utilisateur en mémoire généré par Spring Boot
@SpringBootApplication(exclude = UserDetailsServiceAutoConfiguration.class)
@ConfigurationPropertiesScan
@EnableScheduling
public class RevivoMarketplaceApplication {

    public static void main(String[] args) {
        SpringApplication.run(RevivoMarketplaceApplication.class, args);
    }
}
