<?php
/**
 * FAQ en accordéon natif (<details>, sans JS) — balisage FAQPage dans inc/schema.php.
 *
 * @package IPTV_Pro
 * @var array $args { limit?: int, heading?: bool }
 */

defined( 'ABSPATH' ) || exit;
$faq   = iptvp_faq();
$limit = (int) ( $args['limit'] ?? 0 );
if ( $limit ) {
	$faq = array_slice( $faq, 0, $limit );
}
?>
<section class="section faq" id="faq">
	<div class="container container--narrow">
		<?php if ( $args['heading'] ?? true ) : ?>
			<header class="section__head">
				<p class="eyebrow"><?php esc_html_e( 'FAQ', 'iptv-pro' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'Questions fréquentes sur l’abonnement IPTV', 'iptv-pro' ); ?></h2>
			</header>
		<?php endif; ?>
		<div class="faq__list">
			<?php foreach ( $faq as $i => $item ) : ?>
				<details class="faq__item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><h3><?php echo esc_html( $item['q'] ); ?></h3></summary>
					<div class="faq__answer"><p><?php echo esc_html( $item['a'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
		<?php if ( $limit ) : ?>
			<p class="text-center"><a class="btn btn--outline" href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'Voir toutes les questions', 'iptv-pro' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
