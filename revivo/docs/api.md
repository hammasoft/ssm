# Référence de l'API

La documentation interactive complète est générée par springdoc :
- Marketplace : `http://localhost:8081/swagger-ui.html` (JSON : `/v3/api-docs`)
- Notifications : `http://localhost:8082/swagger-ui.html`

## Conventions

- Base : `/api`. JSON uniquement (sauf upload en `multipart/form-data`).
- Authentification : `Authorization: Bearer <accessToken>` (15 min). Renouvellement via `POST /api/auth/refresh`.
- Pagination : `?page=0&size=20` → `{ content, page, size, totalElements, totalPages, last }`.
- Montants : nombres décimaux en euros, 2 décimales.
- Erreurs au format **RFC 7807** avec un `code` métier stable :

```json
{
  "type": "about:blank",
  "title": "Conflict",
  "status": 409,
  "detail": "Cet article n'est plus disponible.",
  "code": "ITEM_NOT_AVAILABLE"
}
```

| HTTP | Cas |
|---|---|
| 400 | Validation (`errors` : champ → message) |
| 401 | Jeton absent, invalide ou expiré |
| 403 | Authentifié mais non autorisé (pas propriétaire, pas admin) |
| 404 | Ressource inexistante (ou masquée pour vous) |
| 409 | Règle métier violée, conflit de concurrence |
| 413 | Fichier trop volumineux |

## Endpoints — marketplace-service

### Authentification et comptes
| Méthode | Chemin | Accès | Description |
|---|---|---|---|
| POST | `/auth/register` | public | Inscription → jetons |
| POST | `/auth/login` | public | Connexion → jetons |
| POST | `/auth/refresh` | public | Rotation du refresh token |
| POST | `/auth/logout` | public | Révocation du refresh token |
| GET / PUT | `/users/me` | membre | Mon compte / modifier mon profil (dont le mode vacances) |
| POST | `/users/me/password` | membre | Changer de mot de passe |
| GET | `/users/me/following` | membre | Mes abonnements |
| GET | `/users/{id}` | public | Profil public |
| POST / DELETE | `/users/{id}/follow` | membre | Suivre / ne plus suivre |

### Catalogue et annonces
| Méthode | Chemin | Accès | Description |
|---|---|---|---|
| GET | `/categories`, `/brands`, `/meta` | public | Référentiels |
| GET | `/items` | public | Recherche : `q, categoryId, brandIds, sizes, colors, conditions, minPrice, maxPrice, sort` |
| GET | `/items/{id}` | public | Détail (+1 vue) |
| GET | `/items/{id}/similar` | public | Articles similaires |
| GET | `/items/feed` | membre | Nouveautés des membres suivis |
| GET | `/users/{id}/items` | public | Dressing d'un membre |
| POST | `/uploads` | membre | Upload d'une photo → `{ url }` |
| POST | `/items` | membre | Publier |
| PUT | `/items/{id}` | propriétaire | Modifier (une baisse de prix émet `ItemPriceDropped`) |
| POST | `/items/{id}/visibility?hidden=` | propriétaire | Masquer / réafficher |
| DELETE | `/items/{id}` | propriétaire | Supprimer (si aucune commande) |
| PUT / DELETE | `/favorites/{itemId}` | membre | Ajouter / retirer un favori |
| GET | `/favorites` | membre | Mes favoris |

### Offres, commandes, porte-monnaie, avis
| Méthode | Chemin | Accès | Description |
|---|---|---|---|
| POST | `/items/{id}/offers` | membre | Faire une offre |
| GET | `/offers/sent`, `/offers/received` | membre | Mes offres |
| POST | `/offers/{id}/accept`, `/decline` | vendeur | Répondre |
| POST | `/offers/{id}/cancel` | acheteur | Annuler |
| GET | `/items/{id}/checkout-quote?shippingOption=` | membre | Devis |
| POST | `/items/{id}/checkout` | membre | Acheter |
| GET | `/orders/purchases`, `/orders/sales`, `/orders/{id}` | parties | Commandes |
| POST | `/orders/{id}/ship` | vendeur | Déclarer l'envoi |
| POST | `/orders/{id}/delivered` | vendeur / admin | Simuler le webhook transporteur |
| POST | `/orders/{id}/confirm` | acheteur | Valider la réception (libère les fonds) |
| POST | `/orders/{id}/cancel` | parties | Annuler (avant envoi) |
| POST | `/orders/{id}/dispute` | acheteur | Ouvrir un litige |
| POST | `/orders/{id}/review` | parties | Laisser un avis |
| GET | `/users/{id}/reviews` | public | Avis reçus |
| GET | `/wallet`, `/wallet/transactions` | membre | Soldes et historique |
| POST | `/wallet/withdrawals` | membre | Retrait vers IBAN |

### Messagerie, signalements, administration
| Méthode | Chemin | Accès | Description |
|---|---|---|---|
| GET | `/conversations` | membre | Boîte de réception |
| GET | `/conversations/unread-count` | membre | Non-lus |
| POST | `/conversations` | membre | Contacter un membre (à propos d'un article) |
| GET / POST | `/conversations/{id}/messages` | participant | Lire (marque comme lu) / écrire |
| POST | `/reports` | membre | Signaler un article ou un membre |
| GET | `/admin/stats` | admin | KPI |
| GET | `/admin/reports` | admin | Signalements ouverts |
| POST | `/admin/reports/{id}/resolve` | admin | `REMOVE_ITEM`, `BAN_USER` ou `DISMISS` |
| POST | `/admin/users/{id}/ban?banned=` | admin | Suspendre / réactiver |
| GET | `/admin/disputes` | admin | Litiges |
| POST | `/admin/orders/{id}/resolve-dispute?refundBuyer=` | admin | Arbitrer |

## Endpoints — notification-service

| Méthode | Chemin | Description |
|---|---|---|
| GET | `/notifications` | Mes notifications (pagination Spring Data : `{ content, page: { number, size, totalElements, totalPages } }`) |
| GET | `/notifications/unread-count` | Nombre de non-lues |
| GET | `/notifications/stream?access_token=` | Flux **SSE** (`event: notification`) |
| POST | `/notifications/{id}/read` | Marquer comme lue |
| POST | `/notifications/read-all` | Tout marquer comme lu |

## Exemple : parcours complet en cURL

```bash
API=http://localhost:8081/api
login() { curl -s $API/auth/login -H 'Content-Type: application/json' \
  -d "{\"email\":\"$1\",\"password\":\"Password123!\"}" | jq -r .accessToken; }
ALICE=$(login alice@revivo.dev); BOB=$(login bob@revivo.dev)

ITEM=$(curl -s "$API/items?q=veste" | jq '.content[0].id')
OFFER=$(curl -s $API/items/$ITEM/offers -H "Authorization: Bearer $BOB" \
  -H 'Content-Type: application/json' -d '{"amount":20}' | jq .id)
curl -s -X POST $API/offers/$OFFER/accept -H "Authorization: Bearer $ALICE" | jq .status

ORDER=$(curl -s $API/items/$ITEM/checkout -H "Authorization: Bearer $BOB" -H 'Content-Type: application/json' \
  -d '{"shippingOption":"RELAY_POINT","shippingAddress":"Relais 1, Paris","paymentMethodToken":"tok_visa"}' | jq .id)
curl -s -X POST $API/orders/$ORDER/ship -H "Authorization: Bearer $ALICE" \
  -H 'Content-Type: application/json' -d '{"trackingNumber":"TRK123"}' | jq .status
curl -s -X POST $API/orders/$ORDER/confirm -H "Authorization: Bearer $BOB" | jq .status
curl -s $API/wallet -H "Authorization: Bearer $ALICE"     # {"available":20.00,"pending":0.00}
```
