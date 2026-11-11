<?php
/**
 * Pied de page + barre d'appel collante mobile.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php get_template_part( 'template-parts/cta' ); ?>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div>
			<p class="site-footer__brand"><strong><?php echo esc_html( bd_business( 'name' ) ); ?></strong></p>
			<p><?php echo esc_html( bd_business( 'tagline' ) ); ?></p>
			<p>
				<?php echo esc_html( bd_business( 'street' ) ); ?><br>
				<?php echo esc_html( bd_business( 'postal' ) . ' ' . bd_business( 'city' ) ); ?><br>
				<a href="<?php echo esc_url( bd_tel_href(), array( 'tel' ) ); ?>" data-call="footer"><?php echo esc_html( bd_business( 'phone_display' ) ); ?></a><br>
				<a href="mailto:<?php echo esc_attr( bd_business( 'email' ) ); ?>"><?php echo esc_html( bd_business( 'email' ) ); ?></a>
			</p>
		</div>
		<div>
			<p class="site-footer__title">Dépannages urgents</p>
			<ul>
				<?php foreach ( array_slice( bd_services(), 0, 6 ) as $service ) : ?>
					<li><a href="<?php echo esc_url( bd_post_url( $service['article'] ) ); ?>"><?php echo esc_html( $service['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<p class="site-footer__title">Zones d'intervention</p>
			<?php foreach ( bd_zones() as $dept => $cities ) : ?>
				<p><strong><?php echo esc_html( $dept ); ?></strong> : <?php echo esc_html( implode( ', ', array_slice( $cities, 0, 7 ) ) ); ?>…</p>
			<?php endforeach; ?>
		</div>
		<div>
			<p class="site-footer__title">Informations</p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'fallback_cb'    => 'bd_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
			<p><a href="<?php echo esc_url( bd_post_url( 'mentions-legales', 'page' ) ); ?>">Mentions légales</a></p>
		</div>
	</div>
	<p class="container site-footer__legal">© <?php echo esc_html( gmdate( 'Y' ) . ' ' . bd_business( 'name' ) ); ?> · SIRET <?php echo esc_html( bd_business( 'siret' ) ); ?> · Devis gratuit avant toute intervention</p>
</footer>

<div class="callbar" role="region" aria-label="Appel d'urgence">
	<?php echo bd_call_button( 'sticky_mobile', 'Appeler un plombier', 'btn btn--call callbar__btn' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
