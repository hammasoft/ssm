<?php
/**
 * Bloc d'appel à l'action réutilisé en bas de chaque page.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cta-band">
	<div class="container cta-band__inner">
		<div>
			<p class="cta-band__title">Une urgence plomberie ou chauffage ?</p>
			<p>Un artisan vous rappelle, vous annonce le prix et part immédiatement. Intervention en <?php echo esc_html( bd_business( 'delay' ) ); ?>.</p>
		</div>
		<?php echo bd_call_button( 'cta_band', '', 'btn btn--call btn--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
