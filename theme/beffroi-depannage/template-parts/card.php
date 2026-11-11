<?php
/**
 * Carte d'article (boucles blog, home, articles liés).
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;
$bd_cats = get_the_category();
?>
<article <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'bd_card', array( 'loading' => 'lazy' ) ); ?></a>
	<?php endif; ?>
	<div class="card__body">
		<?php if ( $bd_cats ) : ?>
			<p class="card__cat"><?php echo esc_html( $bd_cats[0]->name ); ?></p>
		<?php endif; ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<p class="card__meta"><?php echo (int) bd_reading_time(); ?> min de lecture</p>
	</div>
</article>
