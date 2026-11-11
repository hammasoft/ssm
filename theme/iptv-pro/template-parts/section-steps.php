<?php
/**
 * Comment ça marche.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section steps" id="comment-ca-marche">
	<div class="container">
		<header class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Simple & rapide', 'iptv-pro' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Comment fonctionne votre abonnement IPTV ?', 'iptv-pro' ); ?></h2>
		</header>
		<ol class="steps__list">
			<?php foreach ( iptvp_steps() as $i => $step ) : ?>
				<li class="step">
					<span class="step__num"><?php echo (int) $i + 1; ?></span>
					<h3 class="step__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
