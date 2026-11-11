package com.revivo.marketplace.item;

/**
 * Cycle de vie d'une annonce.
 * <pre>
 * ACTIVE ⇄ HIDDEN (par le vendeur)
 * ACTIVE → SOLD (achat) → ACTIVE (si commande annulée/remboursée)
 * ACTIVE/HIDDEN → REMOVED (modération, définitif)
 * </pre>
 */
public enum ItemStatus {
    ACTIVE, HIDDEN, SOLD, REMOVED
}
