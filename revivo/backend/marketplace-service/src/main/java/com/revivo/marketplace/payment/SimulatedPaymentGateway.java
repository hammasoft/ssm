package com.revivo.marketplace.payment;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.util.UUID;

/**
 * PSP simulé pour la démo. Jetons de test :
 * <ul>
 *   <li>{@code tok_visa} (ou tout autre jeton) → paiement accepté</li>
 *   <li>{@code tok_declined} → paiement refusé</li>
 * </ul>
 */
@Component
public class SimulatedPaymentGateway implements PaymentGateway {

    private static final Logger log = LoggerFactory.getLogger(SimulatedPaymentGateway.class);

    @Override
    public String charge(Long buyerId, BigDecimal amount, String paymentMethodToken, String idempotencyKey) {
        if ("tok_declined".equals(paymentMethodToken)) {
            throw new PaymentDeclinedException("Paiement refusé par la banque.");
        }
        String reference = "pay_" + UUID.nameUUIDFromBytes(idempotencyKey.getBytes()).toString().replace("-", "");
        log.info("[PSP simulé] Débit de {} € pour l'utilisateur {} → {}", amount, buyerId, reference);
        return reference;
    }

    @Override
    public void refund(String paymentReference, BigDecimal amount) {
        log.info("[PSP simulé] Remboursement de {} € sur {}", amount, paymentReference);
    }
}
