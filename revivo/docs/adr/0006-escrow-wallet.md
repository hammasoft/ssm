# ADR-006 — Paiement sous séquestre et porte-monnaie vendeur

## Contexte
Acheteurs et vendeurs ne se connaissent pas. L'acheteur doit être protégé contre la non-livraison, et le vendeur
contre un acheteur de mauvaise foi.

## Décision
- L'acheteur paie **prix + frais de protection + livraison** ; le vendeur ne paie aucune commission.
- Le prix de l'article est crédité en **`pending`** dans le porte-monnaie du vendeur. Il passe en **`available`**
  quand l'acheteur confirme la réception, automatiquement 48 h après la livraison, ou après un arbitrage en
  faveur du vendeur.
- Annulation ou remboursement : remboursement PSP de l'acheteur et `pending -= prix`.
- Chaque mouvement est tracé dans `wallet_transactions` (journal append-only). Des contraintes `CHECK` empêchent un solde négatif.
- `PaymentGateway` est un port ; l'implémentation actuelle est simulée (`tok_visa`, `tok_declined`).

## Conséquences
- ✅ Modèle de confiance lisible, frais transparents pour l'acheteur.
- ✅ Branchement d'un PSP réel (Stripe Connect, Mangopay) localisé dans un adaptateur.
- ⚠️ En réel, le séquestre est porté par le PSP (comptes de monnaie électronique) : le porte-monnaie devient un miroir réconcilié par webhooks.
