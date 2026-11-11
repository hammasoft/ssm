package com.revivo.events;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.SerializationFeature;
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;
import org.junit.jupiter.api.Test;

import java.math.BigDecimal;
import java.time.Instant;

import static org.assertj.core.api.Assertions.assertThat;

class DomainEventSerializationTest {

    private final ObjectMapper mapper = new ObjectMapper()
            .registerModule(new JavaTimeModule())
            .disable(SerializationFeature.WRITE_DATES_AS_TIMESTAMPS);

    @Test
    void roundTripKeepsConcreteTypeThroughTypeDiscriminator() throws Exception {
        DomainEvent event = new OrderPlaced(DomainEvent.newId(), Instant.parse("2026-01-01T10:00:00Z"),
                42L, 7L, "Veste en jean", 1L, 2L, new BigDecimal("20.00"), new BigDecimal("24.29"));

        String json = mapper.writeValueAsString(event);
        DomainEvent read = mapper.readValue(json, DomainEvent.class);

        assertThat(json).contains("\"type\":\"OrderPlaced\"").doesNotContain("\"topic\"");
        assertThat(read).isEqualTo(event);
        assertThat(read.key()).isEqualTo("42");
        assertThat(read.topic()).isEqualTo(Topics.ORDERS);
    }

    @Test
    void unknownFieldsAreToleratedForForwardCompatibility() throws Exception {
        String json = """
                {"type":"UserRegistered","eventId":"6f1c1b8e-6a43-4b8e-9d55-0b1d0c3b2a10",
                 "occurredAt":"2026-01-01T10:00:00Z","userId":1,"username":"alice",
                 "email":"alice@example.com","newFieldFromV2":"ignored"}
                """;
        DomainEvent read = mapper.copy()
                .configure(com.fasterxml.jackson.databind.DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false)
                .readValue(json, DomainEvent.class);
        assertThat(read).isInstanceOf(UserRegistered.class);
    }
}
