<?php
/**
 * Données structurées JSON-LD : Plumber (+HVACBusiness), FAQPage, Article, BreadcrumbList.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Entité entreprise.
 *
 * @return array<string, mixed>
 */
function bd_schema_business(): array {
	$area = array();
	foreach ( bd_zones() as $cities ) {
		foreach ( $cities as $city ) {
			$area[] = array( '@type' => 'City', 'name' => $city );
		}
	}

	$offers = array();
	foreach ( bd_services() as $service ) {
		$offers[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type'       => 'Service',
				'name'        => 'Dépannage ' . $service['title'],
				'description' => $service['text'],
			),
		);
	}

	$data = array(
		'@context'                  => 'https://schema.org',
		'@type'                     => array( 'Plumber', 'HVACBusiness' ),
		'@id'                       => home_url( '/#business' ),
		'name'                      => bd_business( 'name' ),
		'slogan'                    => bd_business( 'tagline' ),
		'url'                       => home_url( '/' ),
		'telephone'                 => bd_business( 'phone_e164' ),
		'email'                     => bd_business( 'email' ),
		'priceRange'                => '€€',
		'address'                   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => bd_business( 'street' ),
			'postalCode'      => bd_business( 'postal' ),
			'addressLocality' => bd_business( 'city' ),
			'addressRegion'   => bd_business( 'region' ),
			'addressCountry'  => 'FR',
		),
		'geo'                       => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) bd_business( 'lat' ),
			'longitude' => (float) bd_business( 'lng' ),
		),
		'openingHoursSpecification' => array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
			'opens'     => '00:00',
			'closes'    => '23:59',
		),
		'areaServed'                => $area,
		'hasOfferCatalog'           => array(
			'@type'           => 'OfferCatalog',
			'name'            => 'Dépannages plomberie et chauffage',
			'itemListElement' => $offers,
		),
	);

	if ( has_custom_logo() ) {
		$data['logo'] = wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' );
	}

	// N'afficher la note que si elle correspond à de vrais avis (exigence Google).
	if ( (int) bd_business( 'review_count' ) > 0 ) {
		$data['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => bd_business( 'rating' ),
			'reviewCount' => (int) bd_business( 'review_count' ),
		);
	}

	return $data;
}

/**
 * Sortie dans le <head>.
 */
add_action(
	'wp_head',
	static function (): void {
		$graph = array( bd_schema_business() );

		if ( is_front_page() ) {
			$faq = array();
			foreach ( bd_faq() as $item ) {
				$faq[] = array(
					'@type'          => 'Question',
					'name'           => $item['q'],
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $item['a'] ),
				);
			}
			$graph[] = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq );
		}

		if ( is_singular( 'post' ) ) {
			$post    = get_queried_object();
			$graph[] = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'Article',
				'headline'         => get_the_title( $post ),
				'description'      => bd_meta_description(),
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'mainEntityOfPage' => get_permalink( $post ),
				'author'           => array( '@type' => 'Organization', 'name' => bd_business( 'name' ), 'url' => home_url( '/' ) ),
				'publisher'        => array( '@id' => home_url( '/#business' ) ),
				'image'            => get_the_post_thumbnail_url( $post, 'full' ) ?: null,
			);
		}

		$crumbs = bd_breadcrumb_items();
		if ( count( $crumbs ) > 1 ) {
			$list = array();
			foreach ( $crumbs as $i => $crumb ) {
				$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb['label'], 'item' => $crumb['url'] );
			}
			$graph[] = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list );
		}

		foreach ( $graph as $node ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $node, static fn( $v ) => null !== $v ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
		}
	},
	5
);
