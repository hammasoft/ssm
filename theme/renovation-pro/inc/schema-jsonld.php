<?php
/**
 * Données structurées Schema.org — GeneralContractor (JSON-LD dans le <head>).
 *
 * Injecté sur toutes les pages pour l'entité entreprise ; enrichi de
 * AggregateRating dès qu'il existe des avis vérifiés. Sur un single "réalisation",
 * un noeud complémentaire décrit le projet.
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fiche de l'entreprise. ⚠️ À personnaliser (NAP identique partout : Google, GMB, site).
 *
 * @return array<string, mixed>
 */
function rp_business_data(): array {
	return array(
		'name'      => 'Nom de l\'Entreprise BTP',
		'legalName' => 'Nom de l\'Entreprise SARL',
		'phone'     => '+33123456789',
		'email'     => 'contact@example.com',
		'street'    => '12 rue de la Rénovation',
		'locality'  => 'Ville',
		'region'    => 'Île-de-France',
		'postal'    => '75000',
		'country'   => 'FR',
		'lat'       => 48.8566,
		'lng'       => 2.3522,
		'price'     => '€€',
		'founding'  => '2010',
		'areas'     => array( 'Ville', 'Ville-2', 'Ville-3' ), // zones desservies
		'sameAs'    => array(
			'https://www.google.com/maps/place/...',
			'https://www.facebook.com/...',
		),
	);
}

/**
 * Construit le noeud LocalBusiness/GeneralContractor.
 *
 * @return array<string, mixed>
 */
function rp_build_business_schema(): array {
	$b        = rp_business_data();
	$site_url = home_url( '/' );

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'GeneralContractor',
		'@id'           => $site_url . '#business',
		'name'          => $b['name'],
		'legalName'     => $b['legalName'],
		'url'           => $site_url,
		'telephone'     => $b['phone'],
		'email'         => $b['email'],
		'priceRange'    => $b['price'],
		'foundingDate'  => $b['founding'],
		'address'       => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $b['street'],
			'addressLocality' => $b['locality'],
			'addressRegion'   => $b['region'],
			'postalCode'      => $b['postal'],
			'addressCountry'  => $b['country'],
		),
		'geo'           => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => $b['lat'],
			'longitude' => $b['lng'],
		),
		'areaServed'    => array_map(
			static fn( string $city ): array => array(
				'@type' => 'City',
				'name'  => $city,
			),
			$b['areas']
		),
		'sameAs'        => $b['sameAs'],
	);

	// Logo si un logo personnalisé est défini.
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( (int) $logo_id, 'full' );
		if ( $logo_url ) {
			$schema['logo']  = $logo_url;
			$schema['image'] = $logo_url;
		}
	}

	// AggregateRating (si avis vérifiés disponibles).
	if ( function_exists( 'rp_get_aggregate_rating' ) ) {
		$agg = rp_get_aggregate_rating();
		if ( $agg['count'] > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) $agg['rating'],
				'reviewCount' => (string) $agg['count'],
				'bestRating'  => '5',
				'worstRating' => '1',
			);
		}
	}

	return $schema;
}

/**
 * Noeud décrivant une réalisation (single `realisations`).
 *
 * @return array<string, mixed>|null
 */
function rp_build_realisation_schema(): ?array {
	if ( ! is_singular( 'realisations' ) ) {
		return null;
	}

	$id = get_the_ID();

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'CreativeWork',
		'name'        => get_the_title( $id ),
		'description' => wp_strip_all_tags( (string) get_the_excerpt( $id ) ),
		'url'         => get_permalink( $id ),
		'image'       => get_the_post_thumbnail_url( $id, 'rp_hero' ) ?: null,
		'provider'    => array( '@id' => home_url( '/' ) . '#business' ),
		'locationCreated' => (string) get_post_meta( $id, 'rp_zone_texte', true ),
	);
}

/**
 * Imprime le(s) bloc(s) JSON-LD dans le <head>.
 */
function rp_print_jsonld(): void {
	$graph = array( rp_build_business_schema() );

	$realisation = rp_build_realisation_schema();
	if ( null !== $realisation ) {
		$graph[] = $realisation;
	}

	foreach ( $graph as $node ) {
		printf(
			"<script type=\"application/ld+json\">%s</script>\n",
			wp_json_encode( $node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}
add_action( 'wp_head', 'rp_print_jsonld', 20 );
