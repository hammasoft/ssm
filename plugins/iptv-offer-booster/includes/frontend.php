<?php
/**
 * Affichage public : règles, chargement des assets, shortcode [iptv_offre].
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

/** Mode aperçu (administrateurs uniquement) : ?iob_preview=1[&iob_template=popup]. */
function iob_is_preview() {
	return isset( $_GET['iob_preview'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification
}

/** L'offre doit-elle s'afficher sur la page courante ? */
function iob_should_display() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
		return false;
	}
	if ( iob_is_preview() ) {
		return true;
	}
	$s = iob_settings();
	if ( ! $s['enabled'] || iob_is_expired( $s ) ) {
		return false;
	}
	$home = is_front_page();
	switch ( $s['pages'] ) {
		case 'home':
			$ok = $home;
			break;
		case 'all_but_home':
			$ok = ! $home;
			break;
		default:
			$ok = ! is_404();
	}
	return (bool) apply_filters( 'iob_should_display', $ok, $s );
}

/** Réglages effectifs (aperçu : modèle forcé par l'URL). */
function iob_effective_settings() {
	$s = iob_settings();
	if ( iob_is_preview() ) {
		$tpl = sanitize_key( $_GET['iob_template'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( iob_templates()[ $tpl ] ) ) {
			$s['template'] = $tpl;
		}
	}
	return $s;
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! iob_should_display() && ! iob_page_has_shortcode() ) {
		return;
	}
	wp_enqueue_style( 'iob', IOB_URL . 'assets/offer.css', array(), IOB_VERSION );
	wp_enqueue_script( 'iob', IOB_URL . 'assets/offer.js', array(), IOB_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	$s = iob_effective_settings();
	// JSON (et non wp_localize_script, qui convertit tout en chaînes).
	wp_add_inline_script( 'iob', 'window.IOB = ' . wp_json_encode( array(
		'template'  => $s['template'],
		'delay'     => (int) $s['delay'],
		'scroll'    => (int) $s['scroll'],
		'frequency' => (int) $s['frequency'],
		'devices'   => $s['devices'],
		'key'       => 'iob_' . substr( md5( $s['title'] . '|' . $s['coupon'] . '|' . $s['template'] ), 0, 10 ),
		'preview'   => iob_is_preview(),
		'track'     => current_user_can( 'manage_options' ) ? '' : esc_url_raw( rest_url( 'iob/v1/track' ) ),
	) ) . ';', 'before' );
} );

/** La page courante contient-elle le shortcode ? */
function iob_page_has_shortcode() {
	$post = get_post();
	return is_singular() && $post && has_shortcode( $post->post_content, 'iptv_offre' );
}

add_action( 'wp_footer', function () {
	if ( iob_should_display() ) {
		echo iob_render( iob_effective_settings() ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans iob_render().
	}
}, 20 );

/** Shortcode [iptv_offre] : carte d'offre insérée dans un contenu. */
add_shortcode( 'iptv_offre', function () {
	$s = iob_settings();
	if ( iob_is_expired( $s ) || ( ! $s['enabled'] && ! iob_is_preview() ) ) {
		return '';
	}
	return iob_render( $s, 'inline' );
} );
