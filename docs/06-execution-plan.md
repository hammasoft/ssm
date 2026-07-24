# ÉTAPE 6 — Plan d'exécution (checklist chronologique)

Du local → staging → production. Cocher au fur et à mesure.

## Phase 0 — Préparation & environnement
- [ ] Choisir l'hébergement (PHP 8.2+, HTTP/2-3, LiteSpeed ou Nginx+FastCGI, SSD/NVMe).
- [ ] Créer 3 environnements : **local** (LocalWP/DDEV), **staging** (sous-domaine), **prod**.
- [ ] Initialiser Git : versionner **le thème `renovation-pro`** + `acf-json/` (jamais `wp-content/uploads`, ni `wp-config.php`).
- [ ] `.gitignore` : core WP, plugins tiers, `uploads/`, `*.log`, secrets.
- [ ] Node + build Tailwind/CSS configuré (`npm run build` → `assets/css/app.css`).

## Phase 1 — Installation & configuration WordPress (local)
- [ ] Installer WordPress 6.x, définir les **permaliens** en `/%postname%/`.
- [ ] `wp-config.php` : `DISALLOW_FILE_EDIT`, `WP_DEBUG=false` (true en local), clés de sécurité, `FORCE_SSL_ADMIN`.
- [ ] Installer le thème `renovation-pro` (dépôt) + **ACF Pro** → les champs se synchronisent via Local JSON.
- [ ] Installer la matrice de plugins (docs/05) — **activer un par un** et mesurer l'impact.
- [ ] Vérifier l'apparition des CPT `realisations` / `avis_clients` et des taxonomies.

## Phase 2 — Contenu & structure
- [ ] Créer l'arborescence de pages (docs/01) + menus (principal / footer).
- [ ] Rédiger les 8 pages services + la page **Rénovation clé en main** (contenu SEO + maillage).
- [ ] Saisir 6–10 réalisations réelles (Avant/Après, budget, durée, zone, types).
- [ ] Saisir 8–15 avis clients vérifiés (`rp_verifie = oui`).
- [ ] Créer les landings villes prioritaires + page `/devis/` dédiée.
- [ ] Renseigner **`rp_business_data()`** (NAP, geo, sameAs) dans `inc/schema-jsonld.php`.

## Phase 3 — SEO & données structurées
- [ ] Configurer le plugin SEO (titres/metas templates, sitemap XML, breadcrumbs).
- [ ] **Désactiver le schema du plugin SEO** (le thème gère `GeneralContractor`) → pas de doublon.
- [ ] Valider le JSON-LD : [Rich Results Test](https://search.google.com/test/rich-results) + [Schema Validator](https://validator.schema.org/).
- [ ] Vérifier `AggregateRating` (avis vérifiés) + `FAQPage` sur les pages avec FAQ.
- [ ] Créer / relier **Google Business Profile** (NAP strictement identique au site).

## Phase 4 — Performance & Core Web Vitals
- [ ] Activer cache page + minification + critical CSS (WP Rocket / LiteSpeed).
- [ ] Générer WebP/AVIF (Imagify/ShortPixel) ; hero en `fetchpriority="high"`, reste `lazy`.
- [ ] Brancher un **CDN** (Cloudflare) : cache statique, Brotli, HTTP/3.
- [ ] Vérifier `defer` sur JS non critique, suppression heartbeat/emoji (déjà dans le thème).
- [ ] Objectif : **PageSpeed mobile ≥ 90**, LCP < 2,5 s, CLS < 0,1, INP < 200 ms.

## Phase 5 — Conversion & tracking
- [ ] Construire le **formulaire devis multi-étapes** (docs/03) + logique conditionnelle.
- [ ] Emails : accusé client + notification interne (SMTP transactionnel type Brevo/Postmark, **pas** `wp_mail` PHP brut).
- [ ] Sticky call/devis mobile ; `tel:` cliquable ; page merci dédiée.
- [ ] GA4 + Google Tag Manager (en defer) ; events `generate_lead` (submit) et `contact` (clic tél).
- [ ] Configurer les conversions Google Ads si campagnes prévues.

## Phase 6 — Sécurité & conformité
- [ ] Wordfence/Solid : WAF, 2FA admin, limitation des tentatives de login.
- [ ] Confirmer XML-RPC off, `/wp-admin` protégé (IP/Basic Auth si possible), en-têtes HSTS/CSP au serveur.
- [ ] Bandeau cookies RGPD (Complianz) + `/mentions-legales/` + `/politique-confidentialite/`.
- [ ] Sauvegardes automatiques (UpdraftPlus) vers stockage externe.

## Phase 7 — Recette sur staging
- [ ] Déployer le thème sur staging (`git pull`), sync ACF (auto), rebuild assets.
- [ ] Tests : responsive, formulaires (réception mails), 404 custom, redirections 301.
- [ ] Revalider PageSpeed, Rich Results, liens internes (crawl Screaming Frog).
- [ ] Relecture orthographe/contenu + vérification NAP partout.
- [ ] `noindex` sur staging (robots) pour éviter l'indexation du sous-domaine.

## Phase 8 — Mise en production
- [ ] Sauvegarde complète avant bascule.
- [ ] Déployer thème + plugins ; migrer contenu (WP Migrate / All-in-One si besoin).
- [ ] Recherche-remplacement des URLs staging → prod (via WP-CLI `wp search-replace`, tables sérialisées OK).
- [ ] Retirer le `noindex`, purger tous les caches (plugin + CDN).
- [ ] Soumettre le **sitemap XML** à Google Search Console + Bing Webmaster.
- [ ] Vérifier HTTPS forcé + redirections `www`/non-`www` cohérentes (301).

## Phase 9 — Post-lancement (J+1 → J+30)
- [ ] Surveiller Search Console (couverture, Core Web Vitals réels/CrUX, requêtes).
- [ ] Suivre les conversions GA4 (leads / appels) et ajuster les CTA.
- [ ] Publier régulièrement dans `/conseils/` (longue traîne → renforce les silos).
- [ ] Collecter de nouveaux avis vérifiés (boucle : chantier livré → demande d'avis).
- [ ] Test de restauration de sauvegarde (vérifier que le backup est exploitable).

---

### Commandes utiles (WP-CLI)

```bash
# Vider et régénérer les permaliens après ajout de CPT/taxo
wp rewrite flush --hard

# Régénérer les miniatures après changement de tailles d'images
wp media regenerate --yes

# Recherche-remplacement d'URLs (migration staging -> prod)
wp search-replace 'https://staging.example.com' 'https://www.example.com' --all-tables --precise

# Purge du transient d'agrégat d'avis (si besoin)
wp transient delete rp_aggregate_rating
```
