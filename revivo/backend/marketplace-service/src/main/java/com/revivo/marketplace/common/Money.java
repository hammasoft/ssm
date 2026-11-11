package com.revivo.marketplace.common;

import java.math.BigDecimal;
import java.math.RoundingMode;

/** Utilitaires monétaires : tous les montants sont en euros, arrondis au centime (HALF_UP). */
public final class Money {

    private Money() {
    }

    public static BigDecimal of(String value) {
        return scale(new BigDecimal(value));
    }

    public static BigDecimal scale(BigDecimal value) {
        return value.setScale(2, RoundingMode.HALF_UP);
    }
}
