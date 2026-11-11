<?php
/**
 * Page 404.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="section error-404">
	<div class="container container--narrow text-center">
		<p class="error-404__code">404</p>
		<h1 class="page-hero__title"><?php esc_html_e( 'Cette page n’existe pas (ou plus)', 'iptv-pro' ); ?></h1>
		<p><?php esc_html_e( 'Le lien est peut-être erroné. Voici quelques pages utiles :', 'iptv-pro' ); ?></p>
		<p class="hero__actions" style="justify-content:center">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'iptv-pro' ); ?></a>
			<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/#tarifs' ) ); ?>"><?php esc_html_e( 'Tarifs', 'iptv-pro' ); ?></a>
			<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/installation-iptv/' ) ); ?>"><?php esc_html_e( 'Installation', 'iptv-pro' ); ?></a>
		</p>
		<?php get_search_form(); ?>
	</div>
</section>
<?php
get_footer();
