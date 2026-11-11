<?php
/**
 * Témoignages (affichés uniquement si de vrais avis sont fournis via le filtre iptvp_testimonials).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$items = iptvp_testimonials();
if ( ! $items ) {
	return;
}
?>
<section class="section testimonials" id="avis">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Avis clients', 'iptv-pro' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Ils regardent la TV avec nous', 'iptv-pro' ); ?></h2>
		</header>
		<div class="testimonials__grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="testimonial">
					<div class="testimonial__stars" aria-label="<?php echo esc_attr( (int) ( $item['rating'] ?? 5 ) . '/5' ); ?>">
						<?php echo str_repeat( iptvp_icon( 'star', 16 ), (int) ( $item['rating'] ?? 5 ) ); // phpcs:ignore ?>
					</div>
					<blockquote><p><?php echo esc_html( $item['text'] ); ?></p></blockquote>
					<figcaption><?php echo esc_html( $item['name'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
