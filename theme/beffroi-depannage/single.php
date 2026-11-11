<?php
/**
 * Article de blog : contenu + encart d'appel latéral + articles liés.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$bd_cats = get_the_category();
	?>
	<section class="page-head">
		<div class="container">
			<?php bd_breadcrumb(); ?>
			<h1><?php the_title(); ?></h1>
			<p class="page-head__meta">Mis à jour le <?php echo esc_html( get_the_modified_date() ); ?> · <?php echo (int) bd_reading_time(); ?> min de lecture · par l'équipe <?php echo esc_html( bd_business( 'name' ) ); ?></p>
		</div>
	</section>

	<div class="section">
		<div class="container layout-article">
			<article class="prose">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="prose__hero"><?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high' ) ); ?></figure>
				<?php endif; ?>

				<div class="urgent-box">
					<p><strong>Urgence en cours ?</strong> Ne perdez pas de temps à lire : un plombier peut être chez vous en <?php echo esc_html( bd_business( 'delay' ) ); ?>.</p>
					<?php echo bd_call_button( 'article_top', '', 'btn btn--call' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<?php the_content(); ?>
			</article>

			<aside class="sidebar" aria-label="Contact rapide">
				<div class="sidebar__box">
					<p class="sidebar__title">Plombier disponible maintenant</p>
					<p>24h/24 · 7j/7 dans le Nord et le Pas-de-Calais. Devis gratuit, prix annoncé avant le départ.</p>
					<?php echo bd_call_button( 'article_sidebar', '', 'btn btn--call btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
				<div class="sidebar__box">
					<p class="sidebar__title">Dépannages fréquents</p>
					<ul>
						<?php foreach ( array_slice( bd_services(), 0, 6 ) as $service ) : ?>
							<li><a href="<?php echo esc_url( bd_post_url( $service['article'] ) ); ?>"><?php echo esc_html( $service['icon'] . ' ' . $service['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</aside>
		</div>
	</div>

	<?php
	$bd_related = new WP_Query(
		array(
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'category__in'        => $bd_cats ? wp_list_pluck( $bd_cats, 'term_id' ) : array(),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'rand',
		)
	);
	if ( $bd_related->have_posts() ) :
		?>
		<section class="section section--alt">
			<div class="container">
				<h2>À lire aussi</h2>
				<div class="cards">
					<?php
					while ( $bd_related->have_posts() ) :
						$bd_related->the_post();
						get_template_part( 'template-parts/card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
