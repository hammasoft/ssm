<?php
/**
 * Réglages éditables dans Apparence > Personnaliser > « IPTV Pro ».
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

/** Valeurs par défaut de toutes les options. */
function iptvp_defaults() {
	return array(
		'brand'          => 'IPTV Pro',
		'currency'       => '€',
		'currency_iso'   => 'EUR',
		'price_1'        => '12.99',
		'price_3'        => '29.99',
		'price_6'        => '44.99',
		'price_12'       => '69.99',
		'price_24'       => '119.99',
		'whatsapp'       => '33600000000',
		'email'          => 'contact@example.com',
		'telegram'       => '',
		'facebook'       => '',
		'instagram'      => '',
		'twitter'        => '',
		'youtube'        => '',
		'channels'       => "20\u{00A0}000",
		'vod'            => "80\u{00A0}000",
		'clients'        => "15\u{00A0}000",
		'rating'         => '',
		'rating_count'   => '',
		'checkout_url'   => '',
		'og_image'       => '',
		'gsc_verify'     => '',
		'bing_verify'    => '',
		'ga4_id'         => '',
	);
}

/** Lecture d'une option avec fallback. */
function iptvp_opt( $key ) {
	$defaults = iptvp_defaults();
	$value    = get_theme_mod( 'iptvp_' . $key, $defaults[ $key ] ?? '' );
	return '' === $value && isset( $defaults[ $key ] ) && in_array( $key, array( 'brand', 'currency', 'currency_iso' ), true ) ? $defaults[ $key ] : $value;
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'iptvp', array(
		'title'    => __( 'IPTV Pro', 'iptv-pro' ),
		'priority' => 30,
	) );

	$sections = array(
		'iptvp_brand'   => array( __( 'Marque & chiffres clés', 'iptv-pro' ), array(
			'brand'        => array( __( 'Nom de la marque', 'iptv-pro' ), 'text' ),
			'channels'     => array( __( 'Nombre de chaînes (affichage)', 'iptv-pro' ), 'text' ),
			'vod'          => array( __( 'Nombre de films & séries (affichage)', 'iptv-pro' ), 'text' ),
			'clients'      => array( __( 'Clients satisfaits (affichage)', 'iptv-pro' ), 'text' ),
			'rating'       => array( __( 'Note moyenne /5 (avis réels uniquement)', 'iptv-pro' ), 'text' ),
			'rating_count' => array( __( "Nombre d'avis (réels uniquement)", 'iptv-pro' ), 'text' ),
		) ),
		'iptvp_pricing' => array( __( 'Tarifs', 'iptv-pro' ), array(
			'currency'     => array( __( 'Symbole devise', 'iptv-pro' ), 'text' ),
			'currency_iso' => array( __( 'Code ISO devise (EUR, USD, CAD…)', 'iptv-pro' ), 'text' ),
			'price_1'      => array( __( 'Prix 1 mois', 'iptv-pro' ), 'text' ),
			'price_3'      => array( __( 'Prix 3 mois', 'iptv-pro' ), 'text' ),
			'price_6'      => array( __( 'Prix 6 mois', 'iptv-pro' ), 'text' ),
			'price_12'     => array( __( 'Prix 12 mois', 'iptv-pro' ), 'text' ),
			'price_24'     => array( __( 'Prix 24 mois', 'iptv-pro' ), 'text' ),
			'checkout_url' => array( __( 'URL de commande (laisser vide = WhatsApp)', 'iptv-pro' ), 'url' ),
		) ),
		'iptvp_contact' => array( __( 'Contact & réseaux sociaux', 'iptv-pro' ), array(
			'whatsapp'  => array( __( 'Numéro WhatsApp (format international sans +)', 'iptv-pro' ), 'text' ),
			'email'     => array( __( 'E-mail', 'iptv-pro' ), 'email' ),
			'telegram'  => array( __( 'Lien Telegram', 'iptv-pro' ), 'url' ),
			'facebook'  => array( __( 'Lien Facebook', 'iptv-pro' ), 'url' ),
			'instagram' => array( __( 'Lien Instagram', 'iptv-pro' ), 'url' ),
			'twitter'   => array( __( 'Lien X / Twitter', 'iptv-pro' ), 'url' ),
			'youtube'   => array( __( 'Lien YouTube', 'iptv-pro' ), 'url' ),
		) ),
		'iptvp_seo'     => array( __( 'SEO & suivi', 'iptv-pro' ), array(
			'og_image'    => array( __( 'Image de partage par défaut (URL 1200×630)', 'iptv-pro' ), 'url' ),
			'gsc_verify'  => array( __( 'Code de vérification Google Search Console', 'iptv-pro' ), 'text' ),
			'bing_verify' => array( __( 'Code de vérification Bing Webmaster', 'iptv-pro' ), 'text' ),
			'ga4_id'      => array( __( 'ID Google Analytics 4 (G-XXXX)', 'iptv-pro' ), 'text' ),
		) ),
	);

	$defaults = iptvp_defaults();
	foreach ( $sections as $section_id => list( $title, $fields ) ) {
		$wp_customize->add_section( $section_id, array( 'title' => $title, 'panel' => 'iptvp' ) );
		foreach ( $fields as $key => list( $label, $type ) ) {
			$sanitize = 'url' === $type ? 'esc_url_raw' : ( 'email' === $type ? 'sanitize_email' : 'sanitize_text_field' );
			$wp_customize->add_setting( 'iptvp_' . $key, array(
				'default'           => $defaults[ $key ] ?? '',
				'sanitize_callback' => $sanitize,
			) );
			$wp_customize->add_control( 'iptvp_' . $key, array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => $type,
			) );
		}
	}
} );
