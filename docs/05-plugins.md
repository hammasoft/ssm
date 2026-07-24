# ÉTAPE 5 — Matrice minimale de plugins

Règle d'or : **chaque plugin doit justifier son poids**. On vise ≤ 10 extensions actives,
zéro doublon de fonction, et on privilégie le code du thème + le serveur (cache, sécurité)
plutôt qu'un plugin quand c'est possible.

| # | Fonction            | Plugin recommandé                | Alternative légère        | Pourquoi / notes perf                                            |
|---|---------------------|----------------------------------|---------------------------|------------------------------------------------------------------|
| 1 | Champs sur-mesure   | **ACF Pro**                      | Meta Box                  | Cœur du modèle de données (Local JSON versionné, zéro requête admin en prod). |
| 2 | SEO                 | **Rank Math** (ou SEO Framework) | The SEO Framework         | Sitemaps, breadcrumbs, contrôle des metas. On **désactive** son schema (géré par le thème) pour éviter les doublons JSON-LD. |
| 3 | Formulaires         | **WPForms Lite/Pro** (ou Fluent Forms) | Forminator          | Formulaire devis multi-étapes, logique conditionnelle, anti-spam natif. Fluent Forms = plus léger si budget serré. |
| 4 | Cache / perf        | **WP Rocket** (ou LiteSpeed Cache si serveur LiteSpeed) | FlyingPress | Cache page, minification, lazy, préchargement, critical CSS. LiteSpeed = gratuit + cache objet si dispo. |
| 5 | Images WebP/AVIF    | **Imagify** (ou ShortPixel)      | Converter for Media       | Conversion + livraison next-gen. À coupler au CDN. |
| 6 | Sécurité            | **Wordfence** (ou Solid Security) | Sucuri (scanner)         | WAF, 2FA admin, limitation login. Compléter le durcissement thème (XML-RPC déjà off). |
| 7 | Sauvegardes         | **UpdraftPlus**                  | Solid Backups             | Sauvegardes planifiées vers stockage externe (S3/Drive) avant chaque MEP. |
| 8 | Cookies / RGPD      | **Complianz** (ou Tarteaucitron) | CookieYes                 | Bandeau consentement + blocage scripts tiers avant accord (obligation FR/UE). |
| 9 | Redirections        | **Redirection** (ou via Rank Math) | —                       | Gérer les 301 lors des refontes d'URL (évite les 404 = perte SEO). |
| 10| Avis externes (opt) | **Widget avis Google** léger     | Intégration API GBP maison | Facultatif : afficher les avis Google. Préférer une intégration cachée côté serveur pour ne pas charger de JS tiers bloquant. |

### À éviter
- **Constructeurs lourds** (Elementor + add-ons) si l'objectif perf 90+ est ferme → préférer
  **Gutenberg natif + ACF blocks**, ou **Bricks Builder** (beaucoup plus léger qu'Elementor).
- **Jetpack** complet, sliders révolver, plugins « tout-en-un » redondants.
- Deux plugins qui produisent le **même JSON-LD** (thème + SEO) → garder une seule source.
- Plugins de statistiques lourds côté serveur → préférer **GA4 + Google Tag Manager** (chargés en defer).

### Budget « poids » indicatif
Cible : **< 15 requêtes** et **< 500 Ko** transférés en page d'accueil (hors images hero),
CSS critique inliné, JS différé. Mesure de contrôle : PageSpeed + WebPageTest après chaque ajout de plugin.
