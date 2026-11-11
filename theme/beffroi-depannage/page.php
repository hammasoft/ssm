<?php
/**
 * Page standard (Tarifs, Zones, Contact, Mentions légales…).
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="page-head">
		<div class="container">
			<?php bd_breadcrumb(); ?>
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<div class="section">
		<div class="container container--narrow prose">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
