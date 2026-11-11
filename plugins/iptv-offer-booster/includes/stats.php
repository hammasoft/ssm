<?php
/**
 * Statistiques simples : vues et clics (sans cookie, sans donnée personnelle).
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

function iob_stats() {
	return wp_parse_args( (array) get_option( 'iob_stats', array() ), array( 'views' => 0, 'clicks' => 0, 'since' => time() ) );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'iob/v1', '/track', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array( 'e' => array( 'required' => true, 'enum' => array( 'view', 'click' ) ) ),
		'callback'            => function ( WP_REST_Request $req ) {
			$event = $req['e'];
			// Anti-gonflage : 1 événement de chaque type par visiteur (IP hachée) et par heure.
			$key = 'iob_t_' . $event . '_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt( 'nonce' ) );
			if ( get_transient( $key ) ) {
				return array( 'ok' => true );
			}
			set_transient( $key, 1, HOUR_IN_SECONDS );
			$stats = iob_stats();
			$field = 'view' === $event ? 'views' : 'clicks';
			++$stats[ $field ];
			update_option( 'iob_stats', $stats, false );
			return array( 'ok' => true );
		},
	) );
} );
