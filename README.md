# Site WordPress BTP / Rénovation — Architecture & Kit de démarrage

Projet : site vitrine haut de gamme, ultrarapide, orienté **acquisition de leads**
(demandes de devis + appels directs) pour une entreprise de rénovation générale.

- **Stack :** WordPress 6.x · PHP 8.2+ · Gutenberg natif + **ACF Pro** · Tailwind CSS (build) ou CSS sur-mesure
- **Perf :** objectif PageSpeed **90+ mobile** (WebP/AVIF, plugins minimaux, cache serveur)
- **SEO :** `GeneralContractor` / `LocalBusiness` en JSON-LD, pages services géolocalisées, maillage interne
- **Conversion :** formulaire devis multi-étapes, sticky call/devis mobile, preuves sociales

## Contenu du dépôt

```
.
├── README.md                          ← ce fichier
├── docs/
│   ├── 01-architecture-sitemap.md     ← ÉTAPE 1 : arborescence + maillage interne
│   ├── 02-data-model-acf.md           ← ÉTAPE 2 : CPT + schéma ACF Pro
│   ├── 03-ux-ui-conversion.md         ← ÉTAPE 3 : wireframes + entonnoir devis
│   ├── 05-plugins.md                  ← ÉTAPE 5 : matrice minimale de plugins
│   └── 06-execution-plan.md           ← ÉTAPE 6 : checklist de déploiement
└── theme/renovation-pro/              ← ÉTAPE 4 : code prêt à déployer
    ├── style.css
    ├── functions.php                  ← charge les modules inc/
    ├── inc/
    │   ├── setup.php                  ← theme supports, image sizes, menus
    │   ├── taxonomies.php             ← "Type de travaux", "Ville / Zone"
    │   ├── cpt-realisations.php       ← CPT Réalisations
    │   ├── cpt-avis.php               ← CPT Avis clients
    │   ├── schema-jsonld.php          ← JSON-LD GeneralContractor dans <head>
    │   ├── security-cleanup.php       ← XML-RPC, emojis, nettoyage wp_head
    │   └── performance.php            ← préchargement, defer, WebP/AVIF hints
    └── acf-json/                      ← synchronisation ACF Local JSON
        ├── group_realisation.json
        └── group_avis.json
```

> Les 6 étapes demandées sont couvertes : **1, 2, 3, 5, 6** dans `docs/`, et l'**ÉTAPE 4**
> (code PHP) est livrée sous forme de fichiers réels et testables dans `theme/renovation-pro/`.

## Démarrage rapide

1. Copier `theme/renovation-pro/` dans `wp-content/themes/`.
2. Installer + activer **ACF Pro** (les champs se synchronisent tout seuls via `acf-json/`).
3. Activer le thème → les CPT `realisations` et `avis_clients` + taxonomies apparaissent.
4. Renseigner l'entreprise dans `inc/schema-jsonld.php` (`rp_business_data()`).
5. Suivre la checklist `docs/06-execution-plan.md`.

---

## Thème IPTV Pro (abonnement IPTV)

| Élément | Source | Zip prêt à téléverser |
|---|---|---|
| Thème IPTV Pro (pages, 22 articles SEO, avis clients, SEO) | `theme/iptv-pro/` | **`dist/iptv-pro.zip`** |
| Plugin IPTV Offer Booster (offre marketing, 5 modèles) | `plugins/iptv-offer-booster/` | **`dist/iptv-offer-booster.zip`** |
| Audit SEO concurrentiel | `docs/iptv-seo-audit.md` | — |
