package com.revivo.notification.config;

import io.jsonwebtoken.JwtException;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.springframework.stereotype.Component;

import javax.crypto.SecretKey;
import java.util.Optional;

/** Ce service ne fait que vérifier les JWT émis par le marketplace-service (secret partagé). */
@Component
public class JwtVerifier {

    private final SecretKey key;

    public JwtVerifier(NotificationProperties properties) {
        this.key = Keys.hmacShaKeyFor(Decoders.BASE64.decode(properties.security().jwtSecret()));
    }

    public Optional<Long> userId(String token) {
        try {
            String sub = Jwts.parser().verifyWith(key).requireIssuer("revivo").build()
                    .parseSignedClaims(token).getPayload().getSubject();
            return Optional.of(Long.valueOf(sub));
        } catch (JwtException | IllegalArgumentException e) {
            return Optional.empty();
        }
    }
}
