# ADR-001 — Monolithe modulaire + service satellite piloté par les événements

## Contexte
Une marketplace C2C a un cœur fortement transactionnel : l'achat modifie l'annonce, l'offre, la commande et le
porte-monnaie, qui doivent rester cohérents. Elle a aussi des fonctions périphériques (notifications, e-mails,
plus tard recherche et recommandations) qui tolèrent la latence et dont la charge varie différemment.

## Décision
- Un **marketplace-service** regroupe les agrégats transactionnels dans des packages isolés par domaine.
- Les fonctions périphériques sont des **services séparés, alimentés uniquement par Kafka**. Le premier est le notification-service.
- Le contrat entre les deux est un module Maven versionné (`revivo-events`).

## Conséquences
- ✅ Transactions ACID là où l'argent circule, sans saga distribuée.
- ✅ Le notification-service peut tomber, être redéployé ou rejouer l'historique sans impact sur les ventes.
- ✅ Chaque package est un candidat à l'extraction (frontières nettes, peu de dépendances croisées).
- ⚠️ Un module partagé couple le déploiement des contrats : la règle « ajouts uniquement » le rend supportable.
- ⚠️ Le marketplace-service scale en bloc ; c'est acceptable tant que le profil de charge reste homogène.
