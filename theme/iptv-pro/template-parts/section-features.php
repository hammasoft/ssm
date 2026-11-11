<?php
/**
 * Avantages.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section features" id="avantages">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Pourquoi nous choisir', 'iptv-pro' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Le meilleur abonnement IPTV pour une expérience TV sans limites', 'iptv-pro' ); ?></h2>
		</header>
		<div class="features__grid">
			<?php foreach ( iptvp_features() as $feature ) : ?>
				<article class="feature">
					<span class="feature__icon"><?php echo iptvp_icon( $feature['icon'], 26 ); // phpcs:ignore ?></span>
					<h3 class="feature__title"><?php echo esc_html( $feature['title'] ); ?></h3>
					<p><?php echo esc_html( $feature['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
