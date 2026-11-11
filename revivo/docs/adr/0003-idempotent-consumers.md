# ADR-003 — Consommateurs idempotents et Dead Letter Topics

## Contexte
Avec l'outbox (ADR-002) et les rebalancements Kafka, un même événement peut être reçu plusieurs fois.
Un message illisible ne doit pas bloquer une partition.

## Décision
- Chaque événement porte un `eventId` (UUID). Le consommateur insère `processed_events(event_id)` **dans la même
  transaction** que ses effets. Si l'identifiant existe déjà, il ignore l'événement ; en cas de course, la clé
  primaire lève une erreur et la tentative suivante l'ignore.
- `DefaultErrorHandler` : back-off exponentiel (4 tentatives), puis `DeadLetterPublishingRecoverer` vers `<topic>-dlt`.
  Les erreurs de désérialisation ne sont pas retentées.
- Les effets externes non transactionnels (e-mails, push SSE) partent **après commit**
  (`@TransactionalEventListener(AFTER_COMMIT)`), en asynchrone pour les e-mails.

## Conséquences
- ✅ Rejouer un topic depuis l'offset 0 est sûr.
- ✅ Une panne SMTP ne bloque pas la consommation.
- ⚠️ Un e-mail peut être perdu si l'instance s'arrête entre le commit et l'envoi ; acceptable ici (la notification
  in-app est persistée). Sinon, il faudrait une outbox d'e-mails.
