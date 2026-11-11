<?php
/**
 * Pied de page.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__about">
			<a class="brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( IPTVP_URI . '/assets/img/logo.svg' ); ?>" width="32" height="32" alt="" loading="lazy">
				<span><?php echo esc_html( iptvp_opt( 'brand' ) ); ?></span>
			</a>
			<p><?php esc_html_e( 'Votre abonnement IPTV premium : chaînes en direct, films et séries en 4K sur tous vos écrans, compatible IPTV Smarters Pro. Activation immédiate et support 7j/7.', 'iptv-pro' ); ?></p>
			<ul class="social">
				<?php
				foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'X', 'youtube' => 'YouTube', 'telegram' => 'Telegram' ) as $key => $label ) :
					if ( iptvp_opt( $key ) ) :
						?>
						<li><a href="<?php echo esc_url( iptvp_opt( $key ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a></li>
						<?php
					endif;
				endforeach;
				?>
			</ul>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Liens utiles', 'iptv-pro' ); ?></h2>
			<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1, 'fallback_cb' => 'iptvp_menu_fallback' ) ); ?>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Installation', 'iptv-pro' ); ?></h2>
			<ul class="footer-menu">
				<?php foreach ( iptvp_devices() as $device ) : ?>
					<li><a href="<?php echo esc_url( home_url( '/installation-iptv/' . $device['slug'] . '/' ) ); ?>"><?php
						/* translators: %s device */
						printf( esc_html__( 'IPTV sur %s', 'iptv-pro' ), esc_html( $device['name'] ) );
					?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Contact', 'iptv-pro' ); ?></h2>
			<ul class="footer-menu footer-menu--contact">
				<li><a href="<?php echo esc_url( iptvp_whatsapp_url() ); ?>" target="_blank" rel="noopener nofollow"><?php echo iptvp_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a></li>
				<?php if ( iptvp_opt( 'email' ) ) : ?>
					<li><a href="mailto:<?php echo esc_attr( antispambot( iptvp_opt( 'email' ) ) ); ?>"><?php echo iptvp_icon( 'mail', 18 ); // phpcs:ignore ?> <?php echo esc_html( antispambot( iptvp_opt( 'email' ) ) ); ?></a></li>
				<?php endif; ?>
			</ul>
			<p class="site-footer__secure"><?php echo iptvp_icon( 'shield', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Paiement 100 % sécurisé', 'iptv-pro' ); ?></p>
		</div>
	</div>

	<div class="container site-footer__bottom">
		<p>© <?php echo esc_html( wp_date( 'Y' ) . ' ' . iptvp_opt( 'brand' ) ); ?>. <?php esc_html_e( 'Tous droits réservés.', 'iptv-pro' ); ?></p>
		<?php wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'menu_class' => 'legal-menu', 'depth' => 1, 'fallback_cb' => '__return_false' ) ); ?>
		<p class="site-footer__disclaimer"><?php esc_html_e( 'IPTV Smarters Pro et les autres applications citées sont des marques de leurs éditeurs respectifs ; elles sont mentionnées uniquement pour indiquer la compatibilité. Nous ne sommes affiliés à aucun de ces éditeurs.', 'iptv-pro' ); ?></p>
	</div>
</footer>

<a class="wa-float" href="<?php echo esc_url( iptvp_whatsapp_url( __( 'Bonjour, j’ai une question sur vos abonnements IPTV.', 'iptv-pro' ) ) ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php esc_attr_e( 'Nous contacter sur WhatsApp', 'iptv-pro' ); ?>">
	<?php echo iptvp_icon( 'whatsapp', 28 ); // phpcs:ignore ?>
</a>

<?php wp_footer(); ?>
</body>
</html>
