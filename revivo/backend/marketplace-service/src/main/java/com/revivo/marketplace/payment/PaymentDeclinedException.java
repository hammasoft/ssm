package com.revivo.marketplace.payment;

import com.revivo.marketplace.common.BusinessException;

public class PaymentDeclinedException extends BusinessException {

    public PaymentDeclinedException(String message) {
        super("PAYMENT_DECLINED", message);
    }
}
