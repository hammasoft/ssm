<?php
/**
 * Carte article (blog, archives, recherche).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<a class="card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'iptvp-card', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="card__placeholder"><?php echo iptvp_icon( 'tv', 40 ); // phpcs:ignore ?></span>
		<?php endif; ?>
	</a>
	<div class="card__body">
		<p class="card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
		<a class="card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lire la suite', 'iptv-pro' ); ?> <span class="screen-reader-text"><?php the_title(); ?></span> →</a>
	</div>
</article>
