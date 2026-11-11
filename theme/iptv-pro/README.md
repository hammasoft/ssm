# Thème WordPress « IPTV Pro »

Thème premium pour un service d'abonnement IPTV : landing page de conversion, tarifs,
guides d'installation, FAQ, blog, SEO avancé. Aucun plugin requis.

## Installation (2 minutes)

1. WordPress > **Apparence > Thèmes > Ajouter > Téléverser un thème** → `iptv-pro.zip` → **Activer**.
2. À l'activation, le thème crée automatiquement :
   - les pages **Accueil** (page d'accueil statique), **Tarifs**, **Installation IPTV** + 6 guides
     (Smart TV, Fire TV Stick, Android, iOS/Apple TV, PC/Mac, MAG), **FAQ**, **Contact**, **Blog** ;
   - les pages légales **Mentions légales, CGV, Politique de remboursement, Politique de confidentialité** ;
   - **22 articles de blog SEO** (3 de départ + 19 articles ciblés, chacun avec sa FAQ), classés en
     4 catégories : Guides IPTV, Applications IPTV, Dépannage IPTV, Bien choisir son IPTV ;
   - la page **Laisser un avis** (formulaire public d'avis clients modéré) ;
   - les menus (principal, pied de page, légal) et les permaliens `/%postname%/`.
3. **Apparence > Personnaliser > IPTV Pro** : nom de marque, prix, devise, numéro WhatsApp, e-mail,
   réseaux sociaux, URL de paiement, Google Search Console, Bing, GA4.
4. **Apparence > Personnaliser > Identité du site** : logo et icône.
5. Complétez les champs `[entre crochets]` des pages légales (obligatoire en France : LCEN, Code de la consommation).

## Thème déjà activé ? Importer les nouveaux contenus

**Apparence > Contenus IPTV Pro** :
- « Créer les pages manquantes » (ex. *Laisser un avis*) — les pages existantes ne sont jamais modifiées ;
- « Importer les articles manquants » : tout publier maintenant, ou **programmer 1 article tous les N jours**
  (recommandé : un rythme régulier envoie un signal de fraîcheur à Google).

## Avis clients

- Menu **Avis clients** : ajoutez les avis reçus (WhatsApp, e-mail…) avec note, ville et appareil,
  ou laissez vos clients les déposer via la page **/laisser-un-avis/** (shortcode `[iptv_avis_form]`).
- Les avis déposés arrivent **en attente** et vous êtes prévenu par e-mail ; vous les publiez après vérification.
- Protection anti-spam : pot de miel, nonce, 1 avis / IP / 10 min, consentement RGPD.
- La section « Avis clients » de l'accueil affiche les 9 derniers avis publiés et la note moyenne ;
  elle reste masquée tant qu'aucun avis n'est publié.
- La note moyenne et les avis sont ajoutés automatiquement au JSON-LD `Product` (étoiles dans Google possibles).
- **Uniquement des avis réels** : les faux avis sont interdits (art. L121-4 du Code de la consommation,
  jusqu'à 300 000 € d'amende) et sanctionnés par Google.

## Architecture SEO

| Page | Requête principale ciblée | Données structurées |
|---|---|---|
| Accueil | abonnement IPTV, IPTV premium 4K | Organization, WebSite + SearchAction, Product + AggregateOffer, FAQPage |
| /tarifs/ | prix abonnement IPTV, IPTV 12 mois | Product + offres, BreadcrumbList |
| /installation-iptv/ (page pilier) | installation IPTV Smarters Pro | BreadcrumbList |
| /installation-iptv/{appareil}/ | IPTV Smart TV, IPTV Firestick… | HowTo, BreadcrumbList |
| /faq/ | questions IPTV | FAQPage |
| Articles (22) | requêtes informationnelles (longue traîne) | BlogPosting, FAQPage, BreadcrumbList |

L'audit concurrentiel complet (concurrents en 1re page, clusters de mots-clés, lacunes, plan 90 jours)
est dans `docs/iptv-seo-audit.md` à la racine du dépôt.

**Inclus dans le thème :**
- Balise `<title>` et **meta description** sur chaque page (meta box « SEO » dans l'éditeur, + case *noindex*).
- **Canonical**, **Open Graph**, **Twitter Cards**, `hreflang`, `max-image-preview:large`.
- **noindex** automatique : recherche, 404, auteurs, dates, pièces jointes (redirigées en 301 vers le fichier).
- **Sitemap XML** natif (`/wp-sitemap.xml`) sans utilisateurs ni pages noindex, déclaré dans `robots.txt`.
- **Un seul H1 par page**, hiérarchie H2/H3 logique, fil d'Ariane visible.
- **Maillage interne** en cocon : accueil → appareils → guides ↔ guides, articles → offres, « À lire aussi ».
- **Performance** : 0 police externe, 0 librairie JS, CSS ~23 Ko (~5 Ko gzip), SVG inline, JS en `defer`, CSS Gutenberg
  retiré de l'accueil, emojis/oEmbed/XML-RPC désactivés → objectif PageSpeed 95+.
- Compatible **Yoast / Rank Math / SEOPress** : si l'un est actif, le module meta du thème se désactive
  automatiquement (le JSON-LD métier reste).

## Personnaliser le contenu

Tout le contenu métier est dans `inc/data.php` (offres, avantages, FAQ, guides) et est filtrable
depuis un plugin ou un thème enfant :

```php
add_filter( 'iptvp_faq', function ( $faq ) {
	$faq[] = array( 'q' => 'Ma question ?', 'a' => 'Ma réponse.' );
	return $faq;
} );

// Avis clients : à n'utiliser qu'avec de VRAIS avis (les faux avis sont illégaux).
add_filter( 'iptvp_testimonials', fn() => array(
	array( 'name' => 'Prénom, Ville', 'text' => 'Avis réel…', 'rating' => 5 ),
) );
```

Le texte SEO en bas de l'accueil se remplace simplement en écrivant du contenu dans la page « Accueil ».

## Checklist SEO après mise en ligne

1. Réglages > Lecture : décocher « Demander aux moteurs de recherche de ne pas indexer ce site ».
2. Ajouter le site dans Google Search Console + Bing Webmaster, soumettre `/wp-sitemap.xml`.
3. Tester l'accueil avec le *Test des résultats enrichis* de Google et PageSpeed Insights.
4. Ajouter une image à la une (1200×630) à chaque article.
5. Publier 2 à 4 articles par mois sur la longue traîne (ex. « IPTV sur Samsung 2026 », « TiviMate vs IPTV Smarters »).
6. Obtenir des liens depuis des sites tech/high-tech pertinents (pas de réseaux de liens achetés).

## Conformité

Les marques citées (IPTV Smarters, Samsung, LG, Amazon, Apple…) ne servent qu'à indiquer la compatibilité.
Ne diffusez que des chaînes et contenus pour lesquels vous disposez des droits de distribution :
la retransmission non autorisée est illégale (en France, l'Arcom fait bloquer ces services).
