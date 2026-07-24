<?php
/**
 * Durcissement & nettoyage : XML-RPC, emojis, <head>, en-têtes, exposition de version.
 *
 * Objectif double : réduire la surface d'attaque et alléger le HTML (perf).
 * NB : le durcissement réseau/serveur (WAF, 2FA, en-têtes HSTS) reste au niveau
 * plugin sécurité + serveur — voir docs/05-plugins.md.
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. XML-RPC — désactivé (vecteur de brute-force et d'amplification DDoS).
 * ---------------------------------------------------------------------- */
add_filter( 'xmlrpc_enabled', '__return_false' );

// Neutralise l'en-tête Pingback et les méthodes pingback.
add_filter(
	'wp_headers',
	static function ( array $headers ): array {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);
add_filter(
	'xmlrpc_methods',
	static function ( array $methods ): array {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
);

/* -------------------------------------------------------------------------
 * 2. Emojis — script/style inutiles retirés (moins de requêtes, HTML plus léger).
 * ---------------------------------------------------------------------- */
function rp_disable_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', static function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	} );
	// Empêche le préchargement DNS de s.w.org pour les emojis.
	add_filter( 'wp_resource_hints', static function ( array $hints, string $relation ): array {
		if ( 'dns-prefetch' === $relation ) {
			$hints = array_filter(
				$hints,
				static fn( $h ) => ! ( is_string( $h ) && str_contains( $h, 's.w.org' ) )
			);
		}
		return $hints;
	}, 10, 2 );
}
add_action( 'init', 'rp_disable_emojis' );

/* -------------------------------------------------------------------------
 * 3. Nettoyage de wp_head — retire les balises superflues / révélatrices.
 * ---------------------------------------------------------------------- */
function rp_clean_wp_head(): void {
	remove_action( 'wp_head', 'wp_generator' );                       // version WP
	remove_action( 'wp_head', 'rsd_link' );                           // Really Simple Discovery
	remove_action( 'wp_head', 'wlwmanifest_link' );                   // Windows Live Writer
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );               // ?p= shortlink
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );    // prev/next
	remove_action( 'wp_head', 'feed_links_extra', 3 );                // flux catégories/commentaires
	remove_action( 'wp_head', 'rest_output_link_wp_head' );           // lien REST
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );      // oEmbed discovery
	remove_action( 'template_redirect', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'rp_clean_wp_head' );

// Masque la version WP dans les URL d'assets (styles/scripts) — anti fingerprinting.
add_filter( 'style_loader_src', 'rp_strip_version_query', 15 );
add_filter( 'script_loader_src', 'rp_strip_version_query', 15 );
/**
 * @param string $src
 * @return string
 */
function rp_strip_version_query( string $src ): string {
	if ( str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}

/* -------------------------------------------------------------------------
 * 4. Divers durcissements légers.
 * ---------------------------------------------------------------------- */

// Désactive l'édition de fichiers depuis l'admin (à mettre aussi dans wp-config.php).
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Messages d'erreur de login génériques (n'indique pas si le login existe).
add_filter( 'login_errors', static fn(): string => __( 'Identifiants invalides.', 'renovation-pro' ) );

// Retire la version WP des flux RSS.
add_filter( 'the_generator', '__return_empty_string' );
