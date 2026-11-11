<?php
/**
 * Blog, archives, recherche (fallback universel).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_home() ) {
	$heading = get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Blog IPTV', 'iptv-pro' );
} elseif ( is_search() ) {
	/* translators: %s query */
	$heading = sprintf( __( 'Résultats pour « %s »', 'iptv-pro' ), get_search_query() );
} elseif ( is_archive() ) {
	$heading = wp_strip_all_tags( get_the_archive_title() );
} else {
	$heading = __( 'Articles', 'iptv-pro' );
}
?>
<header class="page-hero">
	<div class="container">
		<?php iptvp_breadcrumbs(); ?>
		<h1 class="page-hero__title"><?php echo esc_html( $heading ); ?></h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="page-hero__lead"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
		<?php endif; ?>
	</div>
</header>

<section class="section section--tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="cards">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => '←',
				'next_text' => '→',
			) );
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu trouvé.', 'iptv-pro' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
