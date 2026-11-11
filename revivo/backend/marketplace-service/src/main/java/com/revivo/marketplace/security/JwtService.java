package com.revivo.marketplace.security;

import com.revivo.marketplace.config.RevivoProperties;
import io.jsonwebtoken.Claims;
import io.jsonwebtoken.JwtException;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.springframework.stereotype.Service;

import javax.crypto.SecretKey;
import java.time.Clock;
import java.time.Instant;
import java.util.Date;
import java.util.Optional;

/**
 * Émission / validation des access tokens (JWT HS256, durée courte).
 * Le même secret est partagé avec le notification-service, qui ne fait que vérifier.
 */
@Service
public class JwtService {

    static final String ISSUER = "revivo";

    private final SecretKey key;
    private final RevivoProperties.Security props;
    private final Clock clock;

    public JwtService(RevivoProperties properties, Clock clock) {
        this.props = properties.security();
        this.key = Keys.hmacShaKeyFor(Decoders.BASE64.decode(props.jwtSecret()));
        this.clock = clock;
    }

    public String generateAccessToken(Long userId, String username, String role) {
        Instant now = clock.instant();
        return Jwts.builder()
                .issuer(ISSUER)
                .subject(String.valueOf(userId))
                .claim("username", username)
                .claim("role", role)
                .issuedAt(Date.from(now))
                .expiration(Date.from(now.plus(props.accessTokenTtl())))
                .signWith(key)
                .compact();
    }

    public long accessTokenTtlSeconds() {
        return props.accessTokenTtl().toSeconds();
    }

    public Optional<AuthenticatedUser> parse(String token) {
        try {
            Claims claims = Jwts.parser()
                    .verifyWith(key)
                    .requireIssuer(ISSUER)
                    .clock(() -> Date.from(clock.instant()))
                    .build()
                    .parseSignedClaims(token)
                    .getPayload();
            return Optional.of(new AuthenticatedUser(Long.valueOf(claims.getSubject()),
                    claims.get("username", String.class), claims.get("role", String.class)));
        } catch (JwtException | IllegalArgumentException e) {
            return Optional.empty();
        }
    }
}
