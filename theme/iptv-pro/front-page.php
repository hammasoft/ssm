<?php
/**
 * Page d'accueil (landing page de conversion).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();

iptvp_section( 'hero' );
iptvp_section( 'stats' );
iptvp_section( 'pricing' );
iptvp_section( 'features' );
iptvp_section( 'devices' );
iptvp_section( 'steps' );
iptvp_section( 'testimonials' );
iptvp_section( 'faq', array( 'limit' => IPTVP_HOME_FAQ ) );
iptvp_section( 'seo' );
iptvp_section( 'cta' );

get_footer();
