<?php
/**
 * Article de blog.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$words   = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$reading = max( 1, (int) round( $words / 220 ) );
	?>
	<article <?php post_class(); ?>>
		<header class="page-hero">
			<div class="container container--narrow">
				<?php iptvp_breadcrumbs(); ?>
				<h1 class="page-hero__title"><?php the_title(); ?></h1>
				<p class="page-hero__meta">
					<?php
					printf(
						/* translators: 1: date, 2: modified date, 3: minutes */
						esc_html__( 'Publié le %1$s · Mis à jour le %2$s · %3$d min de lecture', 'iptv-pro' ),
						'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>',
						'<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>',
						(int) $reading
					);
					?>
				</p>
			</div>
		</header>

		<div class="section section--tight">
			<div class="container container--narrow">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="post-thumb"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
				<?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>

				<aside class="notice">
					<?php echo iptvp_icon( 'bolt', 22 ); // phpcs:ignore ?>
					<p><?php esc_html_e( 'Envie de tester ? Découvrez nos abonnements IPTV 4K compatibles IPTV Smarters Pro, activés en quelques minutes.', 'iptv-pro' ); ?>
					<a href="<?php echo esc_url( home_url( '/#tarifs' ) ); ?>"><?php esc_html_e( 'Voir les offres', 'iptv-pro' ); ?> →</a></p>
				</aside>

				<?php
				$related = get_posts( array(
					'post__not_in'   => array( get_the_ID() ),
					'posts_per_page' => 3,
					'category__in'   => wp_get_post_categories( get_the_ID() ),
					'no_found_rows'  => true,
				) );
				if ( $related ) :
					?>
					<h2><?php esc_html_e( 'À lire aussi', 'iptv-pro' ); ?></h2>
					<ul class="link-grid">
						<?php foreach ( $related as $rel ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $rel ) ); ?>"><?php echo esc_html( get_the_title( $rel ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>
	</article>
	<?php
endwhile;
get_footer();
