<?php
/**
 * Fonctions d'affichage réutilisées par les gabarits.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lien tel: au format international.
 */
function bd_tel_href(): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', bd_business( 'phone_e164' ) );
}

/**
 * Bouton d'appel. $context sert au suivi des conversions (data-call).
 */
function bd_call_button( string $context = 'generic', string $label = '', string $class = 'btn btn--call' ): string {
	$label = '' !== $label ? $label : 'Appeler le ' . bd_business( 'phone_display' );
	return sprintf(
		'<a class="%1$s" href="%2$s" data-call="%3$s"><span aria-hidden="true">📞</span> %4$s</a>',
		esc_attr( $class ),
		esc_url( bd_tel_href(), array( 'tel' ) ),
		esc_attr( $context ),
		esc_html( $label )
	);
}

/**
 * URL d'un article par slug (ou du blog si l'article n'est pas encore importé).
 */
function bd_post_url( string $slug, string $post_type = 'post' ): string {
	$post = get_page_by_path( $slug, OBJECT, $post_type );
	if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
		return (string) get_permalink( $post );
	}
	$blog = (int) get_option( 'page_for_posts' );
	return $blog ? (string) get_permalink( $blog ) : home_url( '/' );
}

/**
 * Fil d'Ariane simple (balisé BreadcrumbList dans inc/schema.php).
 *
 * @return array<int, array{label:string,url:string}>
 */
function bd_breadcrumb_items(): array {
	$items = array( array( 'label' => 'Accueil', 'url' => home_url( '/' ) ) );

	if ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( 'label' => get_the_title( $blog ), 'url' => (string) get_permalink( $blog ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( 'label' => $cats[0]->name, 'url' => (string) get_category_link( $cats[0] ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => (string) get_permalink() );
	} elseif ( is_page() ) {
		$items[] = array( 'label' => get_the_title(), 'url' => (string) get_permalink() );
	} elseif ( is_category() ) {
		$items[] = array( 'label' => single_cat_title( '', false ), 'url' => (string) get_category_link( get_queried_object_id() ) );
	} elseif ( is_home() ) {
		$items[] = array( 'label' => 'Conseils', 'url' => (string) get_permalink( (int) get_option( 'page_for_posts' ) ) );
	}

	return $items;
}

/**
 * Affiche le fil d'Ariane.
 */
function bd_breadcrumb(): void {
	$items = bd_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<nav class="breadcrumb" aria-label="Fil d\'Ariane"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last ) {
			printf( '<li aria-current="page">%s</li>', esc_html( $item['label'] ) );
		} else {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * Temps de lecture estimé (200 mots/min).
 */
function bd_reading_time( ?int $post_id = null ): int {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ?? get_the_ID() ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}
