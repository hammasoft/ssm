<?php
/**
 * Avis clients (9 derniers avis publiés dans « Avis clients »). Masqué tant qu'aucun avis réel n'est publié.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$items = iptvp_testimonials();
if ( ! $items ) {
	return;
}
$stats = function_exists( 'iptvp_review_stats' ) ? iptvp_review_stats() : array( 'count' => 0, 'average' => 0 );
?>
<section class="section testimonials" id="avis">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Avis clients', 'iptv-pro' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Ce que nos abonnés pensent de notre IPTV', 'iptv-pro' ); ?></h2>
			<?php if ( $stats['count'] ) : ?>
				<p class="testimonials__summary">
					<span class="testimonials__avg"><?php echo esc_html( number_format_i18n( $stats['average'], 1 ) ); ?>/5</span>
					<span class="testimonial__stars" aria-hidden="true"><?php echo str_repeat( iptvp_icon( 'star', 18 ), (int) round( $stats['average'] ) ); // phpcs:ignore ?></span>
					<span>
						<?php
						/* translators: %d number of reviews */
						printf( esc_html( _n( 'sur %d avis client', 'sur %d avis clients', $stats['count'], 'iptv-pro' ) ), (int) $stats['count'] );
						?>
					</span>
				</p>
			<?php endif; ?>
		</header>
		<div class="testimonials__grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="testimonial">
					<div class="testimonial__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Note : %d sur 5', 'iptv-pro' ), (int) ( $item['rating'] ?? 5 ) ) ); ?>">
						<?php echo str_repeat( iptvp_icon( 'star', 16 ), (int) ( $item['rating'] ?? 5 ) ); // phpcs:ignore ?>
					</div>
					<blockquote><p><?php echo esc_html( $item['text'] ); ?></p></blockquote>
					<figcaption>
						<strong><?php echo esc_html( $item['name'] ); ?></strong>
						<?php if ( ! empty( $item['device'] ) ) : ?>
							<span class="testimonial__device"><?php echo esc_html( $item['device'] ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<p class="text-center testimonials__cta"><a class="btn btn--outline" href="<?php echo esc_url( home_url( '/laisser-un-avis/' ) ); ?>"><?php esc_html_e( 'Donner mon avis', 'iptv-pro' ); ?></a></p>
	</div>
</section>
