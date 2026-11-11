# Installation — thème Beffroi Dépannage

## 1. Installer le thème
1. Zipper `theme/beffroi-depannage/` puis **Apparence → Thèmes → Ajouter → Téléverser**
   (ou copier le dossier dans `wp-content/themes/`).
2. Activer **Beffroi Dépannage**.

## 2. Renseigner l'entreprise (obligatoire avant la mise en ligne)
**Apparence → Personnaliser → Beffroi : entreprise** : nom, téléphone (affiché + format
international `+33…`), adresse, SIRET, délai moyen, prix du déplacement, note et nombre d'avis
Google. Ces valeurs alimentent l'en-tête, les boutons d'appel, le pied de page, les articles
(jetons `{{phone}}`, `{{brand}}`…) et le JSON-LD `Plumber`.

> Le numéro par défaut `03 20 00 00 00` et l'adresse sont **fictifs**.

## 3. Importer le contenu
**Apparence → Importer le contenu Beffroi → Lancer l'import** (ou `wp beffroi import`).

Crée :
- 6 pages : Accueil, Conseils, Tarifs, Zones d'intervention, Contact, Mentions légales ;
- 19 articles classés en 7 catégories, avec meta description et mot-clé focus
  (renseignés aussi pour Yoast / Rank Math s'ils sont installés plus tard) ;
- réglages : accueil statique, page des articles = Conseils, permaliens `/%postname%/`.

L'import est idempotent ; cocher « Écraser » pour réappliquer le contenu du thème.

## 4. Après l'import
- Ajouter une image à la une pour chaque article (photos réelles d'interventions de préférence).
- Créer le menu principal (sinon un menu par défaut est affiché).
- Compléter les mentions légales (forme juridique, assurance décennale, médiateur, hébergeur).
- Installer un formulaire (WPForms Lite / Contact Form 7) sur la page Contact si souhaité.
- Brancher Google Tag Manager / GA4 : l'évènement `call_click` est déjà poussé dans `dataLayer`
  à chaque clic sur un numéro → le déclarer comme conversion.
- Créer / optimiser la fiche **Google Business Profile** avec exactement le même NAP.

## 5. Personnaliser
| Besoin | Fichier |
|---|---|
| Ordre / textes / prix des dépannages de la home | `inc/services.php` (`bd_services()`) |
| FAQ de la home | `inc/services.php` (`bd_faq()`) |
| Villes desservies | `inc/business.php` (`bd_zones()`) |
| Couleurs | `assets/css/app.css` (variables `:root`) |
| Ajouter un article | nouveau fichier `content/articles/NN-slug.html` puis relancer l'import |
