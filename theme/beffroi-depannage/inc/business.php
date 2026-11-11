<?php
/**
 * Fiche entreprise (NAP). Chaque valeur est modifiable dans
 * Apparence → Personnaliser → « Beffroi : entreprise » sans toucher au code.
 *
 * ⚠️ Le NAP (nom, adresse, téléphone) doit être IDENTIQUE sur le site,
 * la fiche Google Business Profile et les annuaires.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeurs par défaut (à remplacer par les vraies coordonnées avant la mise en ligne).
 *
 * @return array<string, string>
 */
function bd_business_defaults(): array {
	return array(
		'name'          => 'Beffroi Dépannage',
		'tagline'       => 'Plomberie · Chauffage — Urgence 24h/24 dans le Nord-Pas-de-Calais',
		'phone_display' => '03 20 00 00 00',
		'phone_e164'    => '+33320000000',
		'email'         => 'contact@beffroi-depannage.fr',
		'street'        => '1 place du Beffroi',
		'postal'        => '59000',
		'city'          => 'Lille',
		'region'        => 'Hauts-de-France',
		'lat'           => '50.6366',
		'lng'           => '3.0635',
		'siret'         => '000 000 000 00000',
		'delay'         => '30 à 45 min',
		'price_from'    => '69',
		'rating'        => '4.9',
		'review_count'  => '0',
		'google_url'    => '',
	);
}

/**
 * Lit une donnée entreprise (Customizer > défaut).
 */
function bd_business( string $key ): string {
	$defaults = bd_business_defaults();
	$value    = get_theme_mod( 'bd_' . $key, $defaults[ $key ] ?? '' );
	return is_string( $value ) ? trim( $value ) : '';
}

/**
 * Zones d'intervention mises en avant (SEO local + page Zones).
 *
 * @return array<string, string[]>
 */
function bd_zones(): array {
	return array(
		'Nord (59)'          => array( 'Lille', 'Roubaix', 'Tourcoing', 'Villeneuve-d\'Ascq', 'Marcq-en-Barœul', 'Wattrelos', 'Lambersart', 'Armentières', 'Douai', 'Valenciennes', 'Dunkerque', 'Cambrai', 'Maubeuge' ),
		'Pas-de-Calais (62)' => array( 'Arras', 'Lens', 'Liévin', 'Béthune', 'Hénin-Beaumont', 'Bruay-la-Buissière', 'Calais', 'Boulogne-sur-Mer', 'Saint-Omer' ),
	);
}
