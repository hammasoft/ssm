<?php
/**
 * Chiffres clés.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$stats = array_filter( array(
	array( 'tv', '+' . iptvp_opt( 'channels' ), __( 'Chaînes en direct', 'iptv-pro' ) ),
	array( 'film', '+' . iptvp_opt( 'vod' ), __( 'Films & séries VOD', 'iptv-pro' ) ),
	array( 'users', iptvp_opt( 'clients' ) ? '+' . iptvp_opt( 'clients' ) : '', __( 'Clients satisfaits', 'iptv-pro' ) ),
	array( 'bolt', '99,9 %', __( 'Disponibilité serveurs', 'iptv-pro' ) ),
), fn( $s ) => '' !== $s[1] && '+' !== $s[1] );
?>
<section class="stats" aria-label="<?php esc_attr_e( 'Chiffres clés', 'iptv-pro' ); ?>">
	<div class="container stats__grid">
		<?php foreach ( $stats as list( $icon, $value, $label ) ) : ?>
			<div class="stat">
				<?php echo iptvp_icon( $icon, 28 ); // phpcs:ignore ?>
				<strong><?php echo esc_html( $value ); ?></strong>
				<span><?php echo esc_html( $label ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>
