# Architecture Decision Records

Chaque décision structurante est tracée au format *Contexte → Décision → Conséquences*.

| # | Décision | Statut |
|---|---|---|
| [001](0001-modular-monolith-plus-event-driven-satellite.md) | Monolithe modulaire + service satellite piloté par les événements | Acceptée |
| [002](0002-transactional-outbox.md) | Transactional Outbox pour publier sur Kafka | Acceptée |
| [003](0003-idempotent-consumers.md) | Consommateurs idempotents + Dead Letter Topics | Acceptée |
| [004](0004-sse-for-realtime.md) | Server-Sent Events pour le temps réel | Acceptée |
| [005](0005-postgresql-search-first.md) | Recherche PostgreSQL d'abord, moteur dédié ensuite | Acceptée |
| [006](0006-escrow-wallet.md) | Paiement sous séquestre et porte-monnaie vendeur | Acceptée |
