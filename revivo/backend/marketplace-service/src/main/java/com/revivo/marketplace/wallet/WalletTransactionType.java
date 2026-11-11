package com.revivo.marketplace.wallet;

public enum WalletTransactionType {
    /** Paiement d'une vente placé sous séquestre. */
    SALE_ESCROW,
    /** Séquestre libéré après validation de la commande. */
    SALE_RELEASED,
    /** Séquestre annulé (commande annulée / remboursée). */
    SALE_CANCELLED,
    /** Virement vers le compte bancaire du vendeur. */
    WITHDRAWAL
}
