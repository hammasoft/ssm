# ÉTAPE 3 — Spécifications UX/UI & Conversion

Principe directeur : **1 objectif par écran = obtenir le lead** (devis ou appel).
Chaque section « above the fold » contient au moins un point de conversion.

## 3.1 Wireframe — Page d'Accueil

```
┌─────────────────────────────────────────────────────────────┐
│ HEADER (sticky)                                             │
│  Logo | Services▾  Réalisations  Avis  Conseils  Contact   │
│                             [ ☎ Appeler ]  [ Devis gratuit ]│  ← CTA toujours visibles
├─────────────────────────────────────────────────────────────┤
│ HERO                                                        │
│  H1 : "Rénovation clé en main à {Ville} — un seul          │
│        interlocuteur, zéro souci"                          │
│  Sous-titre : promesse (délais, garantie décennale, devis  │
│  sous 48 h)                                                │
│  [ Demander mon devis gratuit ]   [ ☎ 01 23 45 67 89 ]     │
│  Bandeau confiance : ★4,9/5 · Assurance décennale ·        │
│                       +250 chantiers · RGE                 │
├─────────────────────────────────────────────────────────────┤
│ NOS 8 EXPERTISES  (grille 4×2, icône + titre + lien)       │
│  Réno int. | Réno ext. | Ravalement | Toiture              │
│  Électricité | Chauffage | Plomberie | Clé en main ★        │
├─────────────────────────────────────────────────────────────┤
│ BLOC "CLÉ EN MAIN"  (offre phare)                          │
│  Visuel + 3 bénéfices (interlocuteur unique / prix ferme / │
│  coordination des lots)  →  [ Découvrir l'offre ]          │
├─────────────────────────────────────────────────────────────┤
│ PROCESSUS EN 4 ÉTAPES                                       │
│  1 Contact  2 Visite & devis  3 Chantier  4 Réception      │
├─────────────────────────────────────────────────────────────┤
│ RÉALISATIONS À LA UNE  (3 cartes Avant/Après, rp_featured) │
│  → [ Voir toutes nos réalisations ]                        │
├─────────────────────────────────────────────────────────────┤
│ PREUVE SOCIALE  (avis vérifiés + note agrégée + logos)     │
├─────────────────────────────────────────────────────────────┤
│ ZONES D'INTERVENTION  (carte + liste villes → landings)    │
├─────────────────────────────────────────────────────────────┤
│ FAQ (accordéon, riche en mots-clés + FAQPage schema)       │
├─────────────────────────────────────────────────────────────┤
│ CTA FINAL  "Un projet ? Parlons-en."  [ Devis ] [ Appel ] │
├─────────────────────────────────────────────────────────────┤
│ FOOTER  NAP (Nom/Adresse/Tel) · horaires · liens · légal   │
└─────────────────────────────────────────────────────────────┘

MOBILE : barre fixe basse  [ ☎ Appeler ]  [ Devis ]  (sticky, z-index haut)
```

## 3.2 Wireframe — Page Service type (ex. « Ravalement & Façades »)

```
┌─────────────────────────────────────────────────────────────┐
│ HERO SERVICE                                               │
│  H1 : "Ravalement de façade à {Ville}"                     │
│  Sous-titre bénéfice + [ Devis gratuit ] [ ☎ Appeler ]     │
│  Fil d'Ariane : Accueil › Services › Ravalement & Façades  │
├─────────────────────────────────────────────────────────────┤
│ INTRO SEO  (200–300 mots, mot-clé + intention + local)     │
├─────────────────────────────────────────────────────────────┤
│ PRESTATIONS DÉTAILLÉES  (liste : isolation, crépi,         │
│  réparation, peinture...) → chaque item = bénéfice client  │
├─────────────────────────────────────────────────────────────┤
│ POURQUOI NOUS  (garantie décennale, RGE, aides MaPrimeRénov)│
├─────────────────────────────────────────────────────────────┤
│ RÉALISATIONS LIÉES  (tax_query type_travaux = ce service)  │
├─────────────────────────────────────────────────────────────┤
│ AVIS LIÉS  (tax_query sur ce service)                      │
├─────────────────────────────────────────────────────────────┤
│ MAILLAGE : "Projet global ?" → CTA vers CLÉ EN MAIN        │
│            + liens latéraux (Toiture, Réno extérieure)     │
├─────────────────────────────────────────────────────────────┤
│ FAQ SERVICE  (accordéon + FAQPage schema)                  │
├─────────────────────────────────────────────────────────────┤
│ CTA FINAL + formulaire devis (type pré-sélectionné)        │
└─────────────────────────────────────────────────────────────┘
```

## 3.3 Entonnoir de conversion — Devis « Rénovation clé en main »

Formulaire **multi-étapes** (réduit la friction ; 1 question par écran = taux de complétion ↑).
Barre de progression visible. Aucune demande de contact **avant** l'engagement (étapes 1→4),
coordonnées demandées **en dernier** (principe d'escalier de l'engagement).

```
Étape 1 — TYPE DE PROJET
  ○ Rénovation complète  ○ Pièce(s) spécifique(s)  ○ Extérieur / façade / toiture
  ○ Lots techniques (élec/chauffage/plomberie)     ○ Je ne sais pas encore
        │  (pré-remplit rp_types si arrivé depuis une page service)
        ▼
Étape 2 — SURFACE & BIEN
  Type : maison / appartement · Surface (m²) · Année du bien
        ▼
Étape 3 — BUDGET INDICATIF
  Slider : <15k · 15–30k · 30–60k · 60–100k · >100k
  (qualifie le lead → priorisation commerciale)
        ▼
Étape 4 — DÉLAI
  ○ Urgent (<1 mois)  ○ 1–3 mois  ○ 3–6 mois  ○ Réflexion
        ▼
Étape 5 — COORDONNÉES  (le "prix" du devis)
  Prénom · Tél* · Email* · Code postal*   [ RGPD consent ]
  Micro-copie : "Réponse sous 48 h. Sans engagement."
        ▼
   ┌──────────────────────────────────────────────┐
   │  MERCI + CONFIRMATION                        │
   │  • Écran de remerciement (conversion GA4/Ads)│
   │  • Email auto client (accusé) + interne (lead)│
   │  • Redirection douce vers 3 réalisations     │
   │  • Bouton "Appeler maintenant" en secours    │
   └──────────────────────────────────────────────┘
```

### Règles UX transverses

- **Sticky call/devis mobile** : barre fixe basse, `tel:` cliquable, `position:fixed`.
- **Champs minimaux visibles** : téléphone > email (les artisans convertissent au tél).
- **Preuve sociale contextuelle** : afficher « ★4,9 · +250 chantiers » près de chaque CTA.
- **Zéro distraction sur `/devis/`** : landing sans menu (header allégé), un seul objectif.
- **Perf = conversion** : LCP < 2,5 s ; hero image `fetchpriority="high"`, reste `lazy`.
- **Accessibilité** : contrastes AA, labels explicites, focus visible, `aria-*` sur l'accordéon.
- **Tracking** : événements `generate_lead` (submit), `contact` (clic `tel:`) → GA4 + Ads.
