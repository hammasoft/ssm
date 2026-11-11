package com.revivo.marketplace.order;

import com.revivo.marketplace.config.RevivoProperties;
import org.junit.jupiter.api.Test;

import java.math.BigDecimal;

import static org.assertj.core.api.Assertions.assertThat;

class FeeCalculatorTest {

    private final FeeCalculator calculator = new FeeCalculator(new RevivoProperties(null, null,
            new RevivoProperties.Fees(new BigDecimal("0.50"), new BigDecimal("0.04"), new BigDecimal("3.49"),
                    new BigDecimal("5.99")), null, null, null, false));

    @Test
    void protectionFeeIsFixedPartPlusPercentageRoundedToTheCent() {
        assertThat(calculator.protectionFee(new BigDecimal("20.00"))).isEqualByComparingTo("1.30");
        assertThat(calculator.protectionFee(new BigDecimal("12.99"))).isEqualByComparingTo("1.02");
    }

    @Test
    void priceWithProtection() {
        assertThat(calculator.priceWithProtection(new BigDecimal("20.00"))).isEqualByComparingTo("21.30");
    }

    @Test
    void shippingPrices() {
        assertThat(calculator.shippingPrice(ShippingOption.RELAY_POINT)).isEqualByComparingTo("3.49");
        assertThat(calculator.shippingPrice(ShippingOption.HOME_DELIVERY)).isEqualByComparingTo("5.99");
        assertThat(calculator.shippingPrice(ShippingOption.HAND_DELIVERY)).isEqualByComparingTo("0");
    }
}
