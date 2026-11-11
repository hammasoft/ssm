package com.revivo.marketplace.payment;

import java.math.BigDecimal;

/**
 * Port vers le prestataire de paiement (architecture hexagonale) : l'implémentation
 * simulée peut être remplacée par Stripe / Adyen / Mangopay sans toucher au domaine.
 */
public interface PaymentGateway {

    /**
     * Débite l'acheteur.
     *
     * @param paymentMethodToken jeton de carte tokenisé côté front (jamais de PAN côté serveur)
     * @param idempotencyKey     clé d'idempotence transmise au PSP
     * @return référence de la transaction chez le PSP
     * @throws PaymentDeclinedException si le paiement est refusé
     */
    String charge(Long buyerId, BigDecimal amount, String paymentMethodToken, String idempotencyKey);

    void refund(String paymentReference, BigDecimal amount);
}
