package com.revivo.marketplace.security;

import com.revivo.marketplace.config.RevivoProperties;
import org.junit.jupiter.api.Test;

import java.time.Clock;
import java.time.Duration;
import java.time.Instant;
import java.time.ZoneOffset;
import java.util.List;

import static org.assertj.core.api.Assertions.assertThat;

class JwtServiceTest {

    static final String SECRET = "cmV2aXZvLWRldi1zZWNyZXQtY2hhbmdlLW1lLWluLXByb2R1Y3Rpb24tcGxlYXNlLTEyMzQ1Njc4OTA=";

    private JwtService serviceAt(Instant now) {
        RevivoProperties props = new RevivoProperties(
                new RevivoProperties.Security(SECRET, Duration.ofMinutes(15), Duration.ofDays(7), List.of()),
                null, null, null, null, null, false);
        return new JwtService(props, Clock.fixed(now, ZoneOffset.UTC));
    }

    @Test
    void generatedTokenCanBeParsedBack() {
        Instant now = Instant.now();
        JwtService jwt = serviceAt(now);
        String token = jwt.generateAccessToken(42L, "alice", "ADMIN");

        assertThat(jwt.parse(token)).hasValueSatisfying(u -> {
            assertThat(u.id()).isEqualTo(42L);
            assertThat(u.username()).isEqualTo("alice");
            assertThat(u.isAdmin()).isTrue();
        });
    }

    @Test
    void expiredTokenIsRejected() {
        Instant issued = Instant.parse("2026-01-01T10:00:00Z");
        String token = serviceAt(issued).generateAccessToken(1L, "bob", "USER");
        assertThat(serviceAt(issued.plus(Duration.ofMinutes(16))).parse(token)).isEmpty();
    }

    @Test
    void tamperedTokenIsRejected() {
        JwtService jwt = serviceAt(Instant.now());
        String token = jwt.generateAccessToken(1L, "bob", "USER");
        String tampered = token.substring(0, token.length() - 2) + (token.endsWith("AA") ? "BB" : "AA");
        assertThat(jwt.parse(tampered)).isEmpty();
        assertThat(jwt.parse("not-a-jwt")).isEmpty();
    }
}
