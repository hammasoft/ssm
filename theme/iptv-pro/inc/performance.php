<?php
/**
 * Performance & nettoyage (Core Web Vitals).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

// Nettoyage du <head>.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'feed_links_extra', 3 );
add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'xmlrpc_enabled', '__return_false' );

// Pas de CSS des blocs Gutenberg sur l'accueil (100 % template).
add_action( 'wp_enqueue_scripts', function () {
	if ( is_front_page() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 100 );

// Preload de la feuille de style principale.
add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="style">' . "\n", esc_url( IPTVP_URI . '/assets/css/main.css?ver=' . IPTVP_VERSION ) );
}, 2 );

// Images lazy + decoding async par défaut (WordPress gère le fetchpriority du LCP).
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
	$attr['decoding'] = 'async';
	return $attr;
} );

// Limite les révisions stockées si non défini.
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}
