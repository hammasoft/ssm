<?php
/**
 * Renovation Pro — bootstrap du thème.
 *
 * Chaque responsabilité est isolée dans un module de /inc pour rester lisible
 * et testable. Ordre de chargement volontaire : setup → data → sortie HTML.
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

define( 'RP_THEME_VERSION', '1.0.0' );
define( 'RP_THEME_DIR', get_stylesheet_directory() );
define( 'RP_THEME_URI', get_stylesheet_directory_uri() );

/**
 * Charge un module depuis /inc, en échouant proprement s'il manque.
 */
function rp_require( string $module ): void {
	$path = RP_THEME_DIR . '/inc/' . $module . '.php';
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

$rp_modules = array(
	'setup',            // supports, tailles d'images, menus, assets
	'taxonomies',       // type_travaux, zone
	'cpt-realisations', // CPT realisations
	'cpt-avis',         // CPT avis_clients
	'schema-jsonld',    // GeneralContractor JSON-LD dans <head>
	'security-cleanup', // XML-RPC, emojis, nettoyage wp_head
	'performance',      // preload, defer, hints
);

foreach ( $rp_modules as $rp_module ) {
	rp_require( $rp_module );
}
unset( $rp_module );
