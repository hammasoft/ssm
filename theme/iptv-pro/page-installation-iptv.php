<?php
/**
 * Hub des guides d'installation (slug : installation-iptv) — page pilier du cocon sémantique.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<header class="page-hero">
		<div class="container container--narrow">
			<?php iptvp_breadcrumbs(); ?>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<div class="page-hero__lead entry-content"><?php the_content(); ?></div>
		</div>
	</header>
	<?php
endwhile;
?>
<section class="section section--tight">
	<div class="container">
		<div class="guides__grid">
			<?php foreach ( iptvp_guides() as $slug => $guide ) : ?>
				<article class="guide-card">
					<span class="feature__icon"><?php echo iptvp_icon( 'devices', 26 ); // phpcs:ignore ?></span>
					<h2 class="guide-card__title"><a href="<?php echo esc_url( home_url( '/installation-iptv/' . $slug . '/' ) ); ?>"><?php echo esc_html( $guide['title'] ); ?></a></h2>
					<p><?php echo esc_html( $guide['intro'] ); ?></p>
					<p class="guide-card__app"><?php echo esc_html( $guide['app'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
iptvp_section( 'steps' );
iptvp_section( 'cta' );

get_footer();
