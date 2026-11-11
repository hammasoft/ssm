<?php
/**
 * Page introuvable : on renvoie vers l'appel et les dépannages.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="section">
	<div class="container container--narrow center">
		<h1>Page introuvable</h1>
		<p>Cette page n'existe plus, mais votre urgence, elle, n'attend pas.</p>
		<p><?php echo bd_call_button( '404', '', 'btn btn--call btn--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<p><a href="<?php echo esc_url( home_url( '/#depannages' ) ); ?>">Voir les dépannages les plus demandés</a></p>
	</div>
</section>
<?php
get_footer();
