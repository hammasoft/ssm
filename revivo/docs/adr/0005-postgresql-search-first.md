# ADR-005 — Recherche PostgreSQL d'abord, moteur dédié ensuite

## Contexte
La recherche combine texte libre et filtres structurés. Un moteur dédié (OpenSearch) apporte pertinence, facettes
et tolérance aux fautes, au prix d'une brique d'infrastructure de plus et d'une synchronisation à maintenir.

## Décision
V1 sur PostgreSQL avec la **Criteria API** (`Specification` composables, construites dynamiquement à partir des filtres),
des index ciblés `(status, created_at)`, `seller_id`, `category_id`, un `LIKE` insensible à la casse avec
échappement, et la pagination Spring Data. Les photos restent sur disque local derrière `StorageService`.

## Conséquences
- ✅ Zéro infrastructure supplémentaire, cohérence immédiate.
- ⚠️ `LIKE '%…%'` ne profite pas des index B-tree. Étape intermédiaire : `pg_trgm` + index GIN.
- 🔜 Étape cible : un *search-service* alimenté par `revivo.catalog.v1` (CQRS), qui ne demande pas de modifier le marketplace.
