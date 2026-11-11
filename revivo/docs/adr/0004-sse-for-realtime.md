# ADR-004 — Server-Sent Events pour le temps réel

## Contexte
Le front doit afficher immédiatement « Vendu ! », « Nouvelle offre », « Nouveau message ». Le flux va du serveur
vers le client uniquement.

## Décision
Un endpoint `GET /api/notifications/stream` (`SseEmitter`) par utilisateur connecté, avec un heartbeat toutes les
25 s. Le front utilise l'API native `EventSource` (reconnexion automatique) et invalide les caches React Query
concernés à chaque événement.

## Alternatives écartées
- **WebSocket / STOMP** : bidirectionnel, inutile ici, avec une authentification et un proxying plus complexes.
- **Polling seul** : simple, mais latence et charge inutiles. On le garde en filet de sécurité (compteurs toutes les 30 à 60 s).

## Conséquences
- ✅ HTTP standard, compatible avec les proxys (Nginx : `proxy_buffering off`).
- ⚠️ `EventSource` ne permet pas d'en-tête `Authorization` → jeton en query string (courte durée de vie, HTTPS obligatoire, à exclure des logs d'accès).
- ⚠️ Avec plusieurs instances, un utilisateur n'est connecté qu'à l'une d'elles : soit chaque instance consomme
  avec un group-id propre (fan-out), soit on ajoute Redis Pub/Sub entre la persistance et le push.
