<?php
/**
 * Données structurées JSON-LD (un seul graphe @graph relié par @id) :
 * Organization, WebSite (+SearchAction), WebPage, BreadcrumbList,
 * Product + AggregateOffer (accueil / tarifs), FAQPage, HowTo (guides), BlogPosting.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	if ( is_404() || is_search() ) {
		return;
	}

	$home  = home_url( '/' );
	$brand = iptvp_opt( 'brand' );
	$url   = iptvp_canonical() ?: $home;
	$graph = array();

	// Organization.
	$same_as = array_values( array_filter( array_map( 'iptvp_opt', array( 'facebook', 'instagram', 'twitter', 'youtube', 'telegram' ) ) ) );
	$logo_id = get_theme_mod( 'custom_logo' );
	$org     = array(
		'@type' => 'Organization',
		'@id'   => $home . '#organization',
		'name'  => $brand,
		'url'   => $home,
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : IPTVP_URI . '/assets/img/logo.svg',
		),
	);
	if ( $same_as ) {
		$org['sameAs'] = $same_as;
	}
	$contact = array_filter( array(
		'@type'             => 'ContactPoint',
		'contactType'       => 'customer support',
		'email'             => iptvp_opt( 'email' ),
		'telephone'         => iptvp_opt( 'whatsapp' ) ? '+' . preg_replace( '/\D+/', '', iptvp_opt( 'whatsapp' ) ) : '',
		'availableLanguage' => array( 'French', 'English' ),
	) );
	$org['contactPoint'] = $contact;
	$graph[]             = $org;

	// WebSite + recherche interne (sitelinks searchbox).
	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => $brand,
		'inLanguage'      => get_bloginfo( 'language' ),
		'publisher'       => array( '@id' => $home . '#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}' ),
			'query-input' => 'required name=search_term_string',
		),
	);

	// WebPage.
	$webpage = array(
		'@type'      => is_page( 'faq' ) ? array( 'WebPage', 'FAQPage' ) : 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'isPartOf'   => array( '@id' => $home . '#website' ),
		'inLanguage' => get_bloginfo( 'language' ),
	);
	if ( is_singular() ) {
		$webpage['datePublished'] = get_the_date( 'c' );
		$webpage['dateModified']  = get_the_modified_date( 'c' );
	}
	if ( ! is_front_page() ) {
		$webpage['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	}

	// FAQ : accueil, page FAQ.
	$show_faq = is_front_page() || is_page( 'faq' );
	if ( $show_faq ) {
		if ( ! is_page( 'faq' ) ) {
			$webpage['@type'] = array( 'WebPage', 'FAQPage' );
		}
		$webpage['mainEntity'] = array_map( fn( $f ) => array(
			'@type'          => 'Question',
			'name'           => $f['q'],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ),
		), is_front_page() ? array_slice( iptvp_faq(), 0, IPTVP_HOME_FAQ ) : iptvp_faq() );
	}
	$graph[] = $webpage;

	// Breadcrumb.
	if ( ! is_front_page() ) {
		$items = iptvp_breadcrumb_items();
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => array_map( fn( $item, $i ) => array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['name'],
				'item'     => $item['url'],
			), $items, array_keys( $items ) ),
		);
	}

	// Product + offres : accueil et page tarifs.
	if ( is_front_page() || is_page( array( 'tarifs', 'abonnement-iptv' ) ) ) {
		$plans  = iptvp_plans();
		$prices = array_map( fn( $p ) => (float) str_replace( ',', '.', $p['price'] ), $plans );
		$valid  = gmdate( 'Y-12-31', strtotime( '+1 year' ) );
		if ( $plans ) {
			$product = array(
				'@type'       => 'Product',
				'@id'         => $home . '#product',
				'name'        => sprintf( 'Abonnement IPTV %s', $brand ),
				'description' => iptvp_meta_description(),
				'brand'       => array( '@type' => 'Brand', 'name' => $brand ),
				'image'       => iptvp_og_image(),
				'offers'      => array(
					'@type'         => 'AggregateOffer',
					'priceCurrency' => iptvp_opt( 'currency_iso' ),
					'lowPrice'      => number_format( min( $prices ), 2, '.', '' ),
					'highPrice'     => number_format( max( $prices ), 2, '.', '' ),
					'offerCount'    => count( $plans ),
					'offers'        => array_map( fn( $p ) => array(
						'@type'           => 'Offer',
						'name'            => $p['name'],
						'price'           => number_format( (float) str_replace( ',', '.', $p['price'] ), 2, '.', '' ),
						'priceCurrency'   => iptvp_opt( 'currency_iso' ),
						'availability'    => 'https://schema.org/InStock',
						'priceValidUntil' => $valid,
						'url'             => $home . '#tarifs',
						'seller'          => array( '@id' => $home . '#organization' ),
					), $plans ),
				),
			);
			// Note agrégée UNIQUEMENT si des avis réels sont renseignés.
			$rating = (float) str_replace( ',', '.', (string) iptvp_opt( 'rating' ) );
			$count  = (int) iptvp_opt( 'rating_count' );
			if ( $rating > 0 && $count > 0 ) {
				$product['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => $rating,
					'reviewCount' => $count,
					'bestRating'  => 5,
				);
			}
			$graph[] = $product;
		}
	}

	// HowTo pour les guides d'installation.
	$guide_slug = iptvp_current_guide_slug();
	if ( $guide_slug ) {
		$guide   = iptvp_guides()[ $guide_slug ];
		$graph[] = array(
			'@type'       => 'HowTo',
			'@id'         => $url . '#howto',
			'name'        => $guide['title'],
			'description' => $guide['intro'],
			'totalTime'   => 'PT5M',
			'tool'        => array( array( '@type' => 'HowToTool', 'name' => $guide['app'] ) ),
			'step'        => array_map( fn( $s, $i ) => array(
				'@type'    => 'HowToStep',
				'position' => $i + 1,
				'name'     => wp_trim_words( $s, 8, '…' ),
				'text'     => $s,
				'url'      => $url . '#etape-' . ( $i + 1 ),
			), $guide['steps'], array_keys( $guide['steps'] ) ),
		);
	}

	// Article de blog.
	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'            => 'BlogPosting',
			'@id'              => $url . '#article',
			'headline'         => get_the_title(),
			'description'      => iptvp_meta_description(),
			'image'            => iptvp_og_image(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author' ) ) ),
			'publisher'        => array( '@id' => $home . '#organization' ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'inLanguage'       => get_bloginfo( 'language' ),
		);
	}

	$json = array(
		'@context' => 'https://schema.org',
		'@graph'   => apply_filters( 'iptvp_schema_graph', $graph ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 5 );
