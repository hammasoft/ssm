<?php
/**
 * Plugin Name:       IPTV Offer Booster
 * Plugin URI:        https://example.com/
 * Description:       Mettez en avant une offre promotionnelle sur votre site avec 5 modèles (barre en haut, popup, popup de sortie, slide-in, bannière), un compte à rebours sur une vraie date de fin, un code promo copiable, des règles d'affichage et des statistiques de vues et de clics.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Hammasoft
 * License:           GPL-2.0-or-later
 * Text Domain:       iptv-offer-booster
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

define( 'IOB_VERSION', '1.0.0' );
define( 'IOB_FILE', __FILE__ );
define( 'IOB_DIR', plugin_dir_path( __FILE__ ) );
define( 'IOB_URL', plugin_dir_url( __FILE__ ) );

require_once IOB_DIR . 'includes/options.php';
require_once IOB_DIR . 'includes/render.php';
require_once IOB_DIR . 'includes/frontend.php';
require_once IOB_DIR . 'includes/stats.php';

if ( is_admin() ) {
	require_once IOB_DIR . 'includes/admin.php';
}

register_activation_hook( __FILE__, function () {
	if ( false === get_option( 'iob_settings' ) ) {
		add_option( 'iob_settings', iob_defaults() );
	}
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=iob' ) ) . '">' . esc_html__( 'Configurer', 'iptv-offer-booster' ) . '</a>' );
	return $links;
} );
