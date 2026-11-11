<?php
/**
 * Page FAQ (slug : faq) — schéma FAQPage émis par inc/schema.php.
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

iptvp_section( 'faq', array( 'heading' => false ) );
iptvp_section( 'cta' );

get_footer();
