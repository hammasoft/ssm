<?php
/**
 * Optimisations Core Web Vitals côté thème (le reste = cache serveur + CDN, voir docs).
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Précharge l'image du hero (LCP) sur l'accueil pour améliorer le LCP.
 * À adapter : ici on précharge la miniature du premier post "featured", sinon un asset fixe.
 */
function rp_preload_lcp(): void {
	if ( ! is_front_page() ) {
		return;
	}
	$hero = RP_THEME_URI . '/assets/img/hero.avif';
	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" type="image/avif">' . "\n",
		esc_url( $hero )
	);
}
add_action( 'wp_head', 'rp_preload_lcp', 1 );

/**
 * Ajoute defer aux scripts front non critiques (hors admin/login).
 *
 * @param string $tag
 * @param string $handle
 * @return string
 */
function rp_defer_scripts( string $tag, string $handle ): string {
	if ( is_admin() ) {
		return $tag;
	}
	$deferred = array( 'rp-app' );
	if ( in_array( $handle, $deferred, true ) && ! str_contains( $tag, 'defer' ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'rp_defer_scripts', 10, 2 );

/**
 * Force le lazy-loading natif + dimensions sur les images de contenu (évite le CLS).
 */
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

/**
 * Retire les tailles d'images WP inutiles à la génération (économise le disque et le temps d'upload).
 *
 * @param array<string, mixed> $sizes
 * @return array<string, mixed>
 */
function rp_prune_image_sizes( array $sizes ): array {
	unset( $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048'] );
	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'rp_prune_image_sizes' );

/**
 * Coupe le heartbeat en front (réduit les requêtes admin-ajax).
 */
add_action(
	'init',
	static function (): void {
		if ( ! is_admin() ) {
			wp_deregister_script( 'heartbeat' );
		}
	}
);
