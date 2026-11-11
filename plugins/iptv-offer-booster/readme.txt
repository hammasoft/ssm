=== IPTV Offer Booster ===
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later

Mettez en avant une offre promotionnelle irrésistible, en choisissant un modèle d'affichage.

== Description ==

* 5 modèles : barre d'annonce, popup centrée, popup de sortie (exit intent), slide-in, bannière de page.
* 5 offres prêtes à l'emploi : bienvenue -15 %, 12 mois + 1 offert, vente flash, test gratuit, rattrapage avant départ.
* 5 palettes de couleurs.
* Code promo avec bouton « Copier », prix barré / prix promo, image, conditions.
* Compte à rebours basé sur une vraie date de fin ; l'offre disparaît automatiquement à l'échéance.
* Règles : pages (accueil / toutes / toutes sauf accueil), appareils, délai, % de défilement, fréquence après fermeture.
* Aperçu de chaque modèle pour les administrateurs (?iob_preview=1).
* Statistiques vues / clics / taux de clic, sans cookie ; événements GA4 view_promotion / select_promotion.
* Shortcode [iptv_offre] pour insérer l'offre dans un article.
* Accessible : dialogue modal, piège de focus, fermeture Échap, respect de prefers-reduced-motion.
* Fonctionne avec n'importe quel thème (optimisé pour le thème IPTV Pro).

== Installation ==

1. Extensions > Ajouter > Téléverser `iptv-offer-booster.zip`, puis Activer.
2. Menu « Offre marketing » : choisissez une offre prête à l'emploi et un modèle, cochez « Afficher », enregistrez.
3. Cliquez sur « Aperçu » sous le modèle pour le voir sur votre site.

== Bonnes pratiques ==

* Prix barré = prix réellement pratiqué avant la promotion (directive Omnibus : prix le plus bas des 30 derniers jours).
* Pas de faux compte à rebours : utilisez une vraie date de fin.
* Sur mobile, évitez les popups immédiates (pénalité Google « interstitiels intrusifs ») : 5 s ou 30 % de défilement minimum.
