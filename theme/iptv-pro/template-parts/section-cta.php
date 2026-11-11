<?php
/**
 * Bandeau d'appel à l'action.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cta">
	<div class="container cta__inner">
		<div>
			<h2 class="cta__title"><?php esc_html_e( 'Prêt à profiter de l’IPTV en 4K ?', 'iptv-pro' ); ?></h2>
			<p><?php esc_html_e( 'Commandez maintenant et recevez vos identifiants en quelques minutes. Une question ? Notre équipe vous répond sur WhatsApp.', 'iptv-pro' ); ?></p>
		</div>
		<div class="cta__actions">
			<a class="btn btn--light btn--lg" href="<?php echo esc_url( home_url( '/#tarifs' ) ); ?>"><?php esc_html_e( 'Choisir mon offre', 'iptv-pro' ); ?></a>
			<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( iptvp_trial_url() ); ?>" target="_blank" rel="noopener nofollow"><?php echo iptvp_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Test gratuit', 'iptv-pro' ); ?></a>
		</div>
	</div>
</section>
