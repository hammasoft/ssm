<?php
/**
 * Liste d'articles (page Conseils, catégories, recherche) — gabarit de repli.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-head">
	<div class="container">
		<?php bd_breadcrumb(); ?>
		<h1>
			<?php
			if ( is_category() ) {
				single_cat_title();
			} elseif ( is_search() ) {
				printf( 'Résultats pour « %s »', esc_html( get_search_query() ) );
			} else {
				echo 'Conseils de plombier &amp; chauffagiste';
			}
			?>
		</h1>
		<?php if ( is_home() ) : ?>
			<p>Fuites, débouchage, chauffe-eau, chaudière : nos guides pour réagir vite et éviter les dégâts.</p>
		<?php elseif ( is_category() && category_description() ) : ?>
			<?php echo wp_kses_post( category_description() ); ?>
		<?php endif; ?>
	</div>
</section>

<div class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="cards">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => '← Précédent', 'next_text' => 'Suivant →' ) ); ?>
		<?php else : ?>
			<p>Aucun article pour le moment.</p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
