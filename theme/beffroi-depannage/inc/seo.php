<?php
/**
 * Balises SEO de base (meta description, Open Graph, titre).
 * Désactivées automatiquement si Yoast SEO, Rank Math ou SEOPress est actif
 * (le seeder renseigne aussi leurs champs de meta description).
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Un plugin SEO gère-t-il déjà les métadonnées ?
 */
function bd_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Meta description de la page courante.
 */
function bd_meta_description(): string {
	if ( is_front_page() ) {
		return sprintf(
			'Plombier chauffagiste en urgence 24h/24 dans le Nord-Pas-de-Calais : fuite d\'eau, WC bouché, chauffe-eau, chaudière. Intervention en %s, devis gratuit. ☎ %s',
			bd_business( 'delay' ),
			bd_business( 'phone_display' )
		);
	}
	if ( is_singular() ) {
		$custom = (string) get_post_meta( get_queried_object_id(), '_bd_meta_description', true );
		if ( '' !== $custom ) {
			return $custom;
		}
		$excerpt = get_the_excerpt( get_queried_object_id() );
		return wp_trim_words( wp_strip_all_tags( $excerpt ), 28, '…' );
	}
	if ( is_category() ) {
		return wp_strip_all_tags( category_description() ) ?: 'Conseils de plombier et chauffagiste : ' . single_cat_title( '', false ) . '.';
	}
	return (string) get_bloginfo( 'description' );
}

add_action(
	'wp_head',
	static function (): void {
		if ( bd_seo_plugin_active() ) {
			return;
		}
		$desc  = bd_meta_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? (string) get_permalink() : home_url( '/' . ltrim( (string) ( $GLOBALS['wp']->request ?? '' ), '/' ) );

		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
		printf( "<meta property=\"og:type\" content=\"%s\">\n", is_singular( 'post' ) ? 'article' : 'website' );
		printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $desc ) );
		printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
		printf( "<meta property=\"og:locale\" content=\"fr_FR\">\n" );
		printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( bd_business( 'name' ) ) );
		if ( is_singular() && has_post_thumbnail() ) {
			printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( (string) get_the_post_thumbnail_url( null, 'full' ) ) );
		}
	},
	1
);

/**
 * Titre de la page d'accueil orienté mot-clé n°1.
 */
add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		if ( bd_seo_plugin_active() || ! is_front_page() ) {
			return $title;
		}
		return 'Plombier Chauffagiste Urgence 24h/24 Nord-Pas-de-Calais | ' . bd_business( 'name' );
	}
);
