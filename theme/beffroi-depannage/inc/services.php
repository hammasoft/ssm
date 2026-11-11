<?php
/**
 * Dépannages les plus recherchés (ordre = volume de recherche décroissant,
 * cf. docs/plomberie-urgence/01-etude-seo-concurrence.md § 4).
 *
 * `article` = slug de l'article guide associé (créé par l'import de contenu).
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<int, array{icon:string,title:string,text:string,price:string,article:string}>
 */
function bd_services(): array {
	return array(
		array(
			'icon'    => '💧',
			'title'   => 'Fuite d\'eau',
			'text'    => 'Fuite sous évier, sur un tuyau, un raccord ou au plafond : on coupe, on répare, on sécurise.',
			'price'   => 'dès 90 €',
			'article' => 'fuite-d-eau-gestes-urgence',
		),
		array(
			'icon'    => '🚽',
			'title'   => 'WC bouché',
			'text'    => 'Débouchage de toilettes au furet ou à la pompe, sans casse et sans éclaboussures.',
			'price'   => 'dès 95 €',
			'article' => 'wc-bouche-que-faire',
		),
		array(
			'icon'    => '🌀',
			'title'   => 'Canalisation bouchée',
			'text'    => 'Évier, douche, baignoire ou colonne : furet électrique, hydrocurage et caméra.',
			'price'   => 'dès 95 €',
			'article' => 'debouchage-canalisation-causes-prix',
		),
		array(
			'icon'    => '🚿',
			'title'   => 'Plus d\'eau chaude',
			'text'    => 'Chauffe-eau, cumulus ou ballon qui fuit, disjoncte ou ne chauffe plus : réparation ou remplacement.',
			'price'   => 'dès 90 €',
			'article' => 'plus-d-eau-chaude-panne-chauffe-eau',
		),
		array(
			'icon'    => '🔥',
			'title'   => 'Chaudière en panne',
			'text'    => 'Chaudière gaz ou fioul en sécurité, code erreur, pas de chauffage : dépannage toutes marques.',
			'price'   => 'dès 110 €',
			'article' => 'chaudiere-en-panne-que-faire',
		),
		array(
			'icon'    => '🔍',
			'title'   => 'Recherche de fuite',
			'text'    => 'Fuite invisible, compteur qui tourne : détection non destructive (caméra thermique, gaz traceur).',
			'price'   => 'dès 290 €',
			'article' => 'recherche-de-fuite-non-destructive',
		),
		array(
			'icon'    => '🔁',
			'title'   => 'Chasse d\'eau qui coule',
			'text'    => 'Mécanisme, flotteur ou joint défectueux : réparation immédiate pour stopper le gaspillage.',
			'price'   => 'dès 85 €',
			'article' => 'chasse-d-eau-qui-coule',
		),
		array(
			'icon'    => '♨️',
			'title'   => 'Radiateurs froids',
			'text'    => 'Purge, équilibrage, désembouage ou circulateur en panne : on relance votre chauffage.',
			'price'   => 'dès 90 €',
			'article' => 'radiateur-froid-solutions',
		),
		array(
			'icon'    => '🌊',
			'title'   => 'Dégât des eaux',
			'text'    => 'Intervention d\'urgence, recherche de l\'origine et rapport pour votre assurance.',
			'price'   => 'sur devis',
			'article' => 'degat-des-eaux-demarches-assurance',
		),
		array(
			'icon'    => '🚰',
			'title'   => 'Robinet / mitigeur',
			'text'    => 'Robinet qui goutte, cartouche HS, flexible percé : réparation ou remplacement dans la journée.',
			'price'   => 'dès 65 €',
			'article' => 'robinet-mitigeur-qui-fuit',
		),
	);
}

/**
 * FAQ de la page d'accueil (affichée + balisée FAQPage).
 *
 * @return array<int, array{q:string,a:string}>
 */
function bd_faq(): array {
	$delay = bd_business( 'delay' );
	return array(
		array(
			'q' => 'En combien de temps un plombier peut-il arriver chez moi ?',
			'a' => "En moyenne {$delay} dans la métropole lilloise et les grandes villes du bassin minier, un peu plus en zone rurale. Le délai exact vous est annoncé au téléphone.",
		),
		array(
			'q' => 'Intervenez-vous la nuit, le dimanche et les jours fériés ?',
			'a' => 'Oui, 24h/24 et 7j/7. Une majoration de nuit ou de week-end peut s\'appliquer : elle vous est annoncée avant le déplacement, jamais découverte sur la facture.',
		),
		array(
			'q' => 'Combien coûte un dépannage de plomberie en urgence ?',
			'a' => 'Le déplacement + diagnostic démarre à ' . bd_business( 'price_from' ) . ' €. Un débouchage simple ou une petite fuite se situe généralement entre 90 et 200 €. Un devis écrit est signé avant tout travaux.',
		),
		array(
			'q' => 'Que faire en attendant le plombier en cas de fuite ?',
			'a' => 'Coupez l\'eau au robinet d\'arrêt général (souvent près du compteur), coupez l\'électricité si l\'eau approche des prises, épongez et prenez des photos pour l\'assurance.',
		),
		array(
			'q' => 'Mon assurance peut-elle prendre en charge l\'intervention ?',
			'a' => 'En cas de dégât des eaux, la recherche de fuite et une partie des réparations sont souvent couvertes par l\'assurance habitation. Nous vous remettons une facture détaillée et un rapport pour votre dossier.',
		),
		array(
			'q' => 'Quelles villes couvrez-vous ?',
			'a' => 'Tout le Nord (59) et le Pas-de-Calais (62) : métropole lilloise, Douai, Valenciennes, Dunkerque, Arras, Lens, Liévin, Béthune, Calais, Boulogne-sur-Mer, Saint-Omer et leurs alentours.',
		),
	);
}
