# ÉTAPE 2 — Modélisation des données (CPT & ACF Pro)

## 2.1 Custom Post Types

| CPT             | Slug (rewrite)   | `has_archive` | REST | Usage                                  |
|-----------------|------------------|---------------|------|----------------------------------------|
| `realisations`  | `realisations`   | oui           | oui  | Portfolio projets (Avant/Après, etc.)  |
| `avis_clients`  | `avis`           | oui           | oui  | Témoignages vérifiés (+ agrégat SEO)   |

Le code de déclaration est dans
[`theme/renovation-pro/inc/cpt-realisations.php`](../theme/renovation-pro/inc/cpt-realisations.php)
et [`inc/cpt-avis.php`](../theme/renovation-pro/inc/cpt-avis.php).

## 2.2 Taxonomies (partagées / dédiées)

| Taxonomie        | Slug         | Rattachée à                 | Hiérarchique | Rôle                          |
|------------------|--------------|-----------------------------|--------------|-------------------------------|
| `type_travaux`   | `type`       | `realisations`, `avis_clients` | oui        | Silo par service (maillage)   |
| `zone`           | `zone`       | `realisations`, `avis_clients` | oui        | SEO local (ville / secteur)   |

> `type_travaux` sert de pont entre une réalisation, un avis et la page service :
> une page service requête `tax_query` sur son terme pour afficher projets + avis liés.

## 2.3 Schéma ACF Pro — Réalisation (`group_realisation`)

Groupe attaché à `post_type == realisations`. Champs (préfixe `rp_`) :

| Label                 | Nom (`name`)        | Type              | Notes                                        |
|-----------------------|---------------------|-------------------|----------------------------------------------|
| Accroche projet       | `rp_accroche`       | text              | H1/meta, 60–70 car.                          |
| Photo AVANT           | `rp_photo_avant`    | image (ID)        | Rendu WebP, `alt` obligatoire                |
| Photo APRÈS           | `rp_photo_apres`    | image (ID)        | Slider comparatif Avant/Après                |
| Galerie               | `rp_galerie`        | gallery           | Photos additionnelles                        |
| Budget (€)            | `rp_budget`         | number            | Fourchette basse (affichée « à partir de »)  |
| Budget affiché        | `rp_budget_label`   | select            | `<15k`,`15-30k`,`30-60k`,`60-100k`,`>100k`   |
| Durée du chantier     | `rp_duree`          | text              | Ex. « 6 semaines »                           |
| Zone géographique     | `rp_zone_texte`     | text              | Ville + CP (doublé par la taxo `zone`)       |
| Types de travaux      | `rp_types`          | taxonomy          | Lié à `type_travaux` (multi)                 |
| Prestations réalisées | `rp_prestations`    | repeater          | sous-champ `item` (text)                     |
| Descriptif détaillé   | `rp_descriptif`     | wysiwyg           | Contenu SEO                                  |
| Projet mis en avant   | `rp_featured`       | true_false        | Tri « à la une » sur l'accueil               |

L'export prêt à synchroniser :
[`acf-json/group_realisation.json`](../theme/renovation-pro/acf-json/group_realisation.json).

## 2.4 Schéma ACF Pro — Avis client (`group_avis`)

Groupe attaché à `post_type == avis_clients`. Champs :

| Label                | Nom (`name`)         | Type          | Notes                                       |
|----------------------|----------------------|---------------|---------------------------------------------|
| Nom du client        | `rp_client_nom`      | text          | Ex. « Famille Martin »                      |
| Localité             | `rp_client_ville`    | text          | Ville (affichée, renforce le local)         |
| Note (1–5)           | `rp_note`            | number (1–5)  | Alimente `AggregateRating` JSON-LD          |
| Témoignage           | `rp_temoignage`      | textarea      | Corps de l'avis                             |
| Prestation concernée | `rp_type`            | taxonomy      | Lié à `type_travaux`                        |
| Vérifié              | `rp_verifie`         | true_false    | N'affiche que les avis vérifiés             |
| Source               | `rp_source`          | select        | `google`,`site`,`bouche-a-oreille`          |
| Date de l'avis       | `rp_date`            | date_picker   | `datePublished` du schema Review            |
| Réalisation liée     | `rp_realisation`     | post_object   | Vers un `realisations` (preuve croisée)     |

L'export prêt à synchroniser :
[`acf-json/group_avis.json`](../theme/renovation-pro/acf-json/group_avis.json).

## 2.5 Bonnes pratiques ACF

- **Local JSON activé** (dossier `acf-json/` présent dans le thème) → versionné dans Git,
  synchronisation auto entre environnements, zéro clic en prod. Voir `inc/setup.php`.
- Préfixe `rp_` sur tous les champs → évite les collisions, filtrable proprement.
- Images stockées en **ID** (`return_format: id`) → génération des tailles/formats maîtrisée
  côté thème (`wp_get_attachment_image` avec `srcset` + `loading="lazy"`).
- Les données factuelles (budget, note, date) sont **typées** → réutilisables en JSON-LD.
