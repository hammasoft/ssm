# ÉTAPE 1 — Architecture du site & Sitemap

## 1.1 Principe

Structure en **silos** : chaque service est une page pilier autonome, optimisée pour un
cluster de mots-clés + intention locale. Le service transversal **« Rénovation clé en main »**
est le *hub commercial* : il pointe vers les 8 services et chaque service pointe vers lui
(page de plus forte valeur = plus de liens internes entrants).

Profondeur cible : **3 clics max** depuis l'accueil vers n'importe quelle page utile.

## 1.2 Arborescence complète

```
/ (Accueil)
│
├── /renovation-cle-en-main/                 ★ HUB — page pilier commerciale
│
├── /nos-services/                            (page index, hub SEO)
│   ├── /renovation-interieure/
│   ├── /renovation-exterieure/
│   ├── /ravalement-facades/
│   ├── /travaux-toiture/
│   ├── /electricite/
│   ├── /chauffage/
│   └── /plomberie/
│
├── /realisations/                            (archive CPT `realisations`)
│   ├── /realisations/type/{type-de-travaux}/     (taxo)
│   ├── /realisations/zone/{ville-zone}/          (taxo)
│   └── /realisations/{slug-projet}/              (single)
│
├── /zones-interventions/                     (hub SEO local)
│   └── /renovation-{ville}/                   (landing géolocalisée par ville)
│
├── /avis-clients/                            (archive CPT `avis_clients` + agrégat)
│
├── /conseils/ (blog)                         (contenu top-funnel, longue traîne)
│   ├── /conseils/{categorie}/
│   └── /conseils/{slug-article}/
│
├── /a-propos/
├── /devis/                                    (landing formulaire dédiée, no-distraction)
├── /contact/
│
└── Légal : /mentions-legales/ · /politique-confidentialite/ · /plan-du-site/
```

### Pages système
- `404` personnalisée avec liens vers services + CTA devis.
- `/plan-du-site/` HTML (UX) **en plus** du sitemap XML (SEO, généré par le plugin SEO).

## 1.3 Cartographie des URLs services

| Service                     | Slug                        | CPT/Type | Intention cible                         |
|-----------------------------|-----------------------------|----------|-----------------------------------------|
| Rénovation clé en main      | `renovation-cle-en-main`    | page     | Transactionnelle haute valeur (hub)     |
| Rénovation intérieure       | `renovation-interieure`     | page     | Transactionnelle                        |
| Rénovation extérieure       | `renovation-exterieure`     | page     | Transactionnelle                        |
| Ravalement & Façades        | `ravalement-facades`        | page     | Transactionnelle + aide financière      |
| Travaux de Toiture          | `travaux-toiture`           | page     | Transactionnelle + urgence (étanchéité) |
| Électricité                 | `electricite`               | page     | Transactionnelle + dépannage            |
| Chauffage                   | `chauffage`                 | page     | Transactionnelle + MaPrimeRénov'        |
| Plomberie                   | `plomberie`                 | page     | Transactionnelle + urgence (fuite)      |

## 1.4 Maillage interne (règles)

**Objectif : concentrer le « jus » sur `/renovation-cle-en-main/` et les pages services.**

1. **Accueil →** bloc « Nos 8 expertises » (8 liens services) + CTA vers le hub clé en main.
2. **Chaque page service →**
   - lien **montant** vers `/renovation-cle-en-main/` (ancre : « projet global ? notre offre clé en main »),
   - liens **latéraux** vers 2–3 services complémentaires
     (ex. *Électricité* ↔ *Chauffage* ↔ *Plomberie* = pôle « lots techniques » ;
      *Ravalement* ↔ *Toiture* ↔ *Rénovation extérieure* = pôle « enveloppe du bâti »),
   - liens **descendants** vers 3 réalisations filtrées par ce type de travaux
     (`tax_query` sur `type_travaux`),
   - lien vers 2 articles `/conseils/` du même thème.
3. **Rénovation clé en main →** liste les 8 services (liens descendants) + 3 réalisations
   « projet complet » + les avis clients agrégés.
4. **Réalisations (single) →** liens vers le(s) service(s) concerné(s) + CTA devis pré-rempli
   (type de travaux passé en paramètre).
5. **Landing ville `/renovation-{ville}/` →** liste des services + réalisations filtrées
   par la taxonomie `zone` = cette ville → renforce la pertinence locale.
6. **Blog `/conseils/` →** chaque article cible une page service en lien contextuel (ancre riche),
   jamais l'inverse en masse (le blog nourrit les silos, pas la home).

### Schéma des flux (pôles thématiques)

```
                         ┌───────────────────────────┐
              ┌─────────►│  RÉNOVATION CLÉ EN MAIN ★  │◄─────────┐
              │          └───────────────────────────┘          │
   ┌──────────┴─────────┐        ▲   ▲   ▲        ┌──────────────┴───────────┐
   │ Pôle ENVELOPPE     │        │   │   │        │ Pôle LOTS TECHNIQUES     │
   │ Ravalement ↔ Toit. │        │   │   │        │ Élec ↔ Chauffage ↔ Plomb.│
   │      ↔ Réno ext.   │        │   │   │        └──────────────────────────┘
   └──────────┬─────────┘        │   │   │
              │           Réno intérieure (transverse)
              ▼                   │   │   │
         Réalisations  ◄──────────┘   │   └──────────►  Conseils (blog)
         (filtrées par type + zone)   ▼
                              Landings villes
```
