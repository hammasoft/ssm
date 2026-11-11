<?php
/**
 * Supports du thème, menus, chargement des assets.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'iptv-pro', IPTVP_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_image_size( 'iptvp-card', 640, 360, true );
	add_image_size( 'iptvp-og', 1200, 630, true );

	register_nav_menus( array(
		'primary' => __( 'Menu principal', 'iptv-pro' ),
		'footer'  => __( 'Menu pied de page (liens utiles)', 'iptv-pro' ),
		'legal'   => __( 'Menu légal', 'iptv-pro' ),
	) );
} );

add_action( 'widgets_init', function () {
	register_sidebar( array(
		'name'          => __( 'Barre latérale blog', 'iptv-pro' ),
		'id'            => 'blog',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'iptvp-main', IPTVP_URI . '/assets/css/main.css', array(), IPTVP_VERSION );
	wp_enqueue_script( 'iptvp-main', IPTVP_URI . '/assets/js/main.js', array(), IPTVP_VERSION, array(
		'in_footer' => true,
		'strategy'  => 'defer',
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

/** Largeur de contenu pour les embeds. */
if ( ! isset( $content_width ) ) {
	$content_width = 760;
}

/** Fallback du menu principal si aucun menu n'est assigné. */
function iptvp_menu_fallback() {
	echo '<ul class="nav__list">';
	foreach ( iptvp_default_menu_items() as $item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
	}
	echo '</ul>';
}

function iptvp_default_menu_items() {
	return array(
		array( 'label' => __( 'Accueil', 'iptv-pro' ), 'url' => home_url( '/' ) ),
		array( 'label' => __( 'Tarifs', 'iptv-pro' ), 'url' => home_url( '/#tarifs' ) ),
		array( 'label' => __( 'Installation', 'iptv-pro' ), 'url' => home_url( '/installation-iptv/' ) ),
		array( 'label' => __( 'FAQ', 'iptv-pro' ), 'url' => home_url( '/faq/' ) ),
		array( 'label' => __( 'Blog', 'iptv-pro' ), 'url' => home_url( '/blog/' ) ),
		array( 'label' => __( 'Contact', 'iptv-pro' ), 'url' => home_url( '/contact/' ) ),
	);
}
