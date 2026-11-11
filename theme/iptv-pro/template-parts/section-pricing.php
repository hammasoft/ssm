<?php
/**
 * Grille tarifaire.
 *
 * @package IPTV_Pro
 * @var array $args { heading_tag?: 'h1'|'h2', title?: string }
 */

defined( 'ABSPATH' ) || exit;
$tag   = ( isset( $args['heading_tag'] ) && 'h1' === $args['heading_tag'] ) ? 'h1' : 'h2';
$title = $args['title'] ?? __( 'Nos offres d’abonnement IPTV', 'iptv-pro' );
?>
<section class="section pricing" id="tarifs">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Tarifs', 'iptv-pro' ); ?></p>
			<<?php echo $tag; // phpcs:ignore ?> class="section__title"><?php echo esc_html( $title ); ?></<?php echo $tag; // phpcs:ignore ?>>
			<p class="section__lead"><?php esc_html_e( 'Toutes les formules incluent l’intégralité des chaînes, la VOD, la 4K et le support. Paiement unique, sans renouvellement automatique.', 'iptv-pro' ); ?></p>
		</header>

		<div class="pricing__grid">
			<?php foreach ( iptvp_plans() as $plan ) : ?>
				<article class="plan<?php echo $plan['featured'] ? ' plan--featured' : ''; ?>">
					<?php if ( $plan['badge'] ) : ?>
						<span class="plan__badge"><?php echo esc_html( $plan['badge'] ); ?></span>
					<?php endif; ?>
					<h3 class="plan__name"><?php echo esc_html( $plan['label'] ); ?></h3>
					<p class="plan__price"><?php echo esc_html( iptvp_price( $plan['price'] ) ); ?></p>
					<p class="plan__monthly">
						<?php
						/* translators: %s monthly price */
						printf( esc_html__( 'soit %s / mois', 'iptv-pro' ), esc_html( iptvp_price_per_month( $plan ) ) );
						?>
					</p>
					<ul class="plan__features">
						<?php foreach ( $plan['features'] as $feature ) : ?>
							<li><?php echo iptvp_icon( 'check', 18 ); // phpcs:ignore ?> <?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>
					<a class="btn <?php echo $plan['featured'] ? 'btn--primary' : 'btn--outline'; ?> btn--block" href="<?php echo esc_url( iptvp_order_url( $plan ) ); ?>" target="_blank" rel="noopener nofollow" data-plan="<?php echo esc_attr( $plan['id'] ); ?>">
						<?php esc_html_e( 'Commander maintenant', 'iptv-pro' ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="pricing__note"><?php echo iptvp_icon( 'shield', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Paiement sécurisé · Identifiants envoyés par e-mail ou WhatsApp · Besoin de plusieurs écrans ? Contactez-nous.', 'iptv-pro' ); ?></p>
	</div>
</section>
