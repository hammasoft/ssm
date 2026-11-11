<?php
/**
 * Page « Tarifs » (slug : tarifs).
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

iptvp_section( 'pricing', array( 'title' => __( 'Choisissez la durée de votre abonnement', 'iptv-pro' ) ) );
iptvp_section( 'features' );
iptvp_section( 'faq', array( 'limit' => 5 ) );
iptvp_section( 'cta' );

get_footer();
