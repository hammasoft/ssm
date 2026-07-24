<?php
/**
 * Configuration du thème : supports, tailles d'images, menus, ACF local JSON, assets.
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Déclare les fonctionnalités du thème.
 */
function rp_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo' );

	// Tailles d'images dédiées (recadrage maîtrisé pour les cartes Avant/Après).
	add_image_size( 'rp_card', 640, 480, true );
	add_image_size( 'rp_hero', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'renovation-pro' ),
			'footer'  => __( 'Menu pied de page', 'renovation-pro' ),
		)
	);

	load_theme_textdomain( 'renovation-pro', RP_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'rp_theme_setup' );

/**
 * Active la synchronisation ACF Local JSON (versionné, zéro clic en prod).
 */
add_filter(
	'acf/settings/save_json',
	static fn(): string => RP_THEME_DIR . '/acf-json'
);
add_filter(
	'acf/settings/load_json',
	static function ( array $paths ): array {
		$paths[] = RP_THEME_DIR . '/acf-json';
		return $paths;
	}
);

/**
 * Charge les assets front (CSS/JS compilés). Versionnés via filemtime pour le cache-busting.
 */
function rp_enqueue_assets(): void {
	$css = '/assets/css/app.css';
	$js  = '/assets/js/app.js';

	if ( is_readable( RP_THEME_DIR . $css ) ) {
		wp_enqueue_style(
			'rp-app',
			RP_THEME_URI . $css,
			array(),
			(string) filemtime( RP_THEME_DIR . $css )
		);
	}

	if ( is_readable( RP_THEME_DIR . $js ) ) {
		wp_enqueue_script(
			'rp-app',
			RP_THEME_URI . $js,
			array(),
			(string) filemtime( RP_THEME_DIR . $js ),
			true // dans le footer
		);
	}
}
add_action( 'wp_enqueue_scripts', 'rp_enqueue_assets' );
