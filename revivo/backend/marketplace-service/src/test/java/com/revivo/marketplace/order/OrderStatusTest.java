package com.revivo.marketplace.order;

import org.junit.jupiter.params.ParameterizedTest;
import org.junit.jupiter.params.provider.CsvSource;

import static org.assertj.core.api.Assertions.assertThat;

class OrderStatusTest {

    @ParameterizedTest(name = "{0} → {1} : {2}")
    @CsvSource({
            "PAID, SHIPPED, true",
            "PAID, CANCELLED, true",
            "PAID, DELIVERED, false",
            "SHIPPED, CANCELLED, false",
            "SHIPPED, DISPUTED, true",
            "DELIVERED, COMPLETED, true",
            "DISPUTED, REFUNDED, true",
            "COMPLETED, DISPUTED, false",
            "CANCELLED, PAID, false",
            "REFUNDED, COMPLETED, false"
    })
    void transitions(OrderStatus from, OrderStatus to, boolean allowed) {
        assertThat(from.canTransitionTo(to)).isEqualTo(allowed);
    }

    @ParameterizedTest
    @CsvSource({"COMPLETED, true", "CANCELLED, true", "REFUNDED, true", "PAID, false", "DISPUTED, false"})
    void terminalStates(OrderStatus status, boolean terminal) {
        assertThat(status.isTerminal()).isEqualTo(terminal);
    }
}
