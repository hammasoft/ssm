<?php
/**
 * IPTV Pro — chargement des modules du thème.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

define( 'IPTVP_VERSION', '1.0.0' );
define( 'IPTVP_DIR', get_template_directory() );
define( 'IPTVP_URI', get_template_directory_uri() );
define( 'IPTVP_HOME_FAQ', 6 ); // Questions affichées (et balisées FAQPage) sur l'accueil.

foreach ( array(
	'setup',        // supports, menus, assets.
	'customizer',   // marque, prix, WhatsApp, réseaux sociaux.
	'data',         // offres, avantages, FAQ, guides (source unique de vérité).
	'helpers',      // fonctions d'affichage réutilisables.
	'reviews',      // avis clients réels (CPT + formulaire modéré).
	'articles',     // 19 articles SEO + outil d'import.
	'seo',          // title, meta description, canonical, Open Graph, Twitter, robots.
	'schema',       // JSON-LD.
	'performance',  // nettoyage wp_head, preload, defer.
	'activation',   // création auto des pages + menus à l'activation.
) as $iptvp_module ) {
	require_once IPTVP_DIR . '/inc/' . $iptvp_module . '.php';
}
