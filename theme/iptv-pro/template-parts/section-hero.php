<?php
/**
 * Hero de l'accueil (H1 unique, ciblage « abonnement IPTV »).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$plans    = iptvp_plans();
$min_plan = $plans ? array_reduce( $plans, fn( $carry, $p ) => ( ! $carry || ( (float) $p['price'] / $p['months'] ) < ( (float) $carry['price'] / $carry['months'] ) ) ? $p : $carry ) : null;
?>
<section class="hero">
	<div class="container hero__grid">
		<div class="hero__content">
			<p class="eyebrow"><?php echo iptvp_icon( 'star', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Service IPTV premium · 4K · Sans engagement', 'iptv-pro' ); ?></p>
			<h1 class="hero__title">
				<?php esc_html_e( 'Abonnement IPTV Premium', 'iptv-pro' ); ?>
				<span class="gradient-text"><?php esc_html_e( '4K & Full HD sans coupure', 'iptv-pro' ); ?></span>
			</h1>
			<p class="hero__lead">
				<?php
				printf(
					/* translators: 1: channels, 2: vod */
					esc_html__( 'Plus de %1$s chaînes en direct et %2$s films & séries sur Smart TV, Fire TV Stick, Android, iPhone et PC. Compatible IPTV Smarters Pro, activation en quelques minutes, sans engagement.', 'iptv-pro' ),
					'<strong>' . esc_html( iptvp_opt( 'channels' ) ) . '</strong>',
					'<strong>' . esc_html( iptvp_opt( 'vod' ) ) . '</strong>'
				);
				?>
			</p>
			<div class="hero__actions">
				<a class="btn btn--primary btn--lg" href="#tarifs"><?php esc_html_e( 'Voir les offres', 'iptv-pro' ); ?> <?php echo iptvp_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
				<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( iptvp_trial_url() ); ?>" target="_blank" rel="noopener nofollow"><?php echo iptvp_icon( 'play', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Test gratuit', 'iptv-pro' ); ?></a>
			</div>
			<ul class="hero__trust">
				<li><?php echo iptvp_icon( 'check', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Activation immédiate', 'iptv-pro' ); ?></li>
				<li><?php echo iptvp_icon( 'check', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Sans engagement', 'iptv-pro' ); ?></li>
				<li><?php echo iptvp_icon( 'check', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Support 7j/7', 'iptv-pro' ); ?></li>
			</ul>
		</div>
		<div class="hero__visual" aria-hidden="true">
			<div class="tv-mock">
				<div class="tv-mock__screen">
					<div class="tv-mock__bar"><span></span><span></span><span></span></div>
					<div class="tv-mock__grid">
						<?php for ( $i = 0; $i < 9; $i++ ) : ?><div class="tv-mock__tile"></div><?php endfor; ?>
					</div>
					<div class="tv-mock__live"><span class="dot"></span> LIVE · 4K</div>
				</div>
				<div class="tv-mock__stand"></div>
			</div>
			<?php if ( $min_plan ) : ?>
				<div class="price-bubble">
					<small><?php esc_html_e( 'à partir de', 'iptv-pro' ); ?></small>
					<strong><?php echo esc_html( iptvp_price_per_month( $min_plan ) ); ?></strong>
					<small><?php esc_html_e( '/ mois', 'iptv-pro' ); ?></small>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
