<?php
/**
 * Supports du thème, menus, assets, nettoyage du <head>.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 220, 'flex-width' => true ) );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_image_size( 'bd_card', 640, 360, true );

		register_nav_menus(
			array(
				'primary' => 'Menu principal',
				'footer'  => 'Menu pied de page',
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$css = '/assets/css/app.css';
		$js  = '/assets/js/app.js';
		wp_enqueue_style( 'bd-app', BD_URI . $css, array(), (string) filemtime( BD_DIR . $css ) );
		wp_enqueue_script( 'bd-app', BD_URI . $js, array(), (string) filemtime( BD_DIR . $js ), array( 'strategy' => 'defer', 'in_footer' => true ) );

		// Pas besoin de la bibliothèque de blocs complète sur la home (gain LCP).
		if ( is_front_page() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'global-styles' );
		}
	}
);

/* Nettoyage du <head> : moins de requêtes, moins d'informations exposées. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Menu de repli si aucun menu n'est assigné.
 */
function bd_fallback_menu(): void {
	$links = array(
		'Dépannages' => home_url( '/#depannages' ),
		'Tarifs'     => bd_post_url( 'tarifs', 'page' ),
		'Zones'      => bd_post_url( 'zones-intervention', 'page' ),
		'Conseils'   => get_option( 'page_for_posts' ) ? (string) get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/#conseils' ),
		'Contact'    => bd_post_url( 'contact', 'page' ),
	);
	echo '<ul class="menu">';
	foreach ( $links as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Extrait plus court sur les cartes.
 */
add_filter( 'excerpt_length', static fn(): int => 26 );
add_filter( 'excerpt_more', static fn(): string => '…' );
