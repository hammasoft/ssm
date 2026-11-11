<?php
/**
 * Formulaire de recherche.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$iptvp_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $iptvp_sid ); ?>"><?php esc_html_e( 'Rechercher', 'iptv-pro' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $iptvp_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un guide, une question…', 'iptv-pro' ); ?>">
	<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Rechercher', 'iptv-pro' ); ?></button>
</form>
