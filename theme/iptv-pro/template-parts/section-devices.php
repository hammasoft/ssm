<?php
/**
 * Appareils compatibles (maillage interne vers les guides).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section devices" id="appareils">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Compatibilité', 'iptv-pro' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Compatible avec IPTV Smarters Pro et tous vos appareils', 'iptv-pro' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Un seul abonnement, utilisable sur l’écran de votre choix. Cliquez sur votre appareil pour accéder au guide d’installation.', 'iptv-pro' ); ?></p>
		</header>
		<ul class="devices__grid">
			<?php foreach ( iptvp_devices() as $device ) : ?>
				<li>
					<a class="device" href="<?php echo esc_url( home_url( '/installation-iptv/' . $device['slug'] . '/' ) ); ?>">
						<?php echo iptvp_icon( 'devices', 30 ); // phpcs:ignore ?>
						<strong><?php echo esc_html( $device['name'] ); ?></strong>
						<span><?php echo esc_html( $device['sub'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
