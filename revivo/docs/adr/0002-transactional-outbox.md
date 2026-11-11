# ADR-002 — Transactional Outbox

## Contexte
Écrire en base puis appeler `kafkaTemplate.send()` n'est pas atomique : un crash entre les deux perd l'événement,
et un envoi suivi d'un rollback publie un événement fantôme. Les transactions XA/2PC entre PostgreSQL et Kafka sont
lourdes et mal supportées.

## Décision
- `DomainEventPublisher.publish()` (propagation `MANDATORY`) insère l'événement sérialisé dans `outbox_events`,
  **dans la transaction métier**.
- `OutboxRelay` interroge la table chaque seconde (`SELECT … FOR UPDATE SKIP LOCKED LIMIT n`), publie avec
  `acks=all` et l'idempotence du producteur, puis renseigne `published_at`. Il s'arrête au premier échec pour
  préserver l'ordre.
- Une purge quotidienne supprime les lignes publiées depuis plus de 7 jours.

## Alternatives écartées
- **CDC Debezium** : élégant et sans polling, mais ajoute Kafka Connect à l'infrastructure. C'est l'évolution naturelle si le volume augmente.
- **`@TransactionalEventListener(AFTER_COMMIT)` + envoi direct** : l'événement est perdu si le broker est indisponible au moment du commit.

## Conséquences
- ✅ Livraison *at-least-once* garantie, ordre conservé par agrégat.
- ✅ Plusieurs instances du service peuvent relayer en parallèle sans doublon de traitement grâce à `SKIP LOCKED`.
- ⚠️ Latence de publication d'environ 1 s ; doublons possibles (crash entre l'envoi et l'UPDATE) → voir ADR-003.
- ⚠️ La table doit être surveillée (`outboxBacklog` dans `/api/admin/stats`).
