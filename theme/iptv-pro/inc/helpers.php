<?php
/**
 * Helpers d'affichage.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

/** Prix formaté à la française : 69,99 €. */
function iptvp_price( $amount ) {
	$amount = (float) str_replace( ',', '.', (string) $amount );
	return number_format_i18n( $amount, 2 ) . "\u{00A0}" . iptvp_opt( 'currency' );
}

/** Prix mensuel équivalent. */
function iptvp_price_per_month( array $plan ) {
	$amount = (float) str_replace( ',', '.', (string) $plan['price'] );
	return iptvp_price( $amount / max( 1, (int) $plan['months'] ) );
}

/** Lien WhatsApp avec message prérempli. */
function iptvp_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/\D+/', '', (string) iptvp_opt( 'whatsapp' ) );
	if ( ! $number ) {
		return home_url( '/contact/' );
	}
	$url = 'https://wa.me/' . $number;
	return $message ? $url . '?text=' . rawurlencode( $message ) : $url;
}

/** URL de commande d'une offre (checkout externe ou WhatsApp). */
function iptvp_order_url( array $plan ) {
	$checkout = iptvp_opt( 'checkout_url' );
	if ( $checkout ) {
		return add_query_arg( 'plan', $plan['id'], $checkout );
	}
	/* translators: 1: plan name, 2: price */
	return iptvp_whatsapp_url( sprintf( __( 'Bonjour, je souhaite commander : %1$s (%2$s).', 'iptv-pro' ), $plan['name'], iptvp_price( $plan['price'] ) ) );
}

/** Lien WhatsApp « demander un test ». */
function iptvp_trial_url() {
	return iptvp_whatsapp_url( __( 'Bonjour, je souhaite un test IPTV. Mon appareil : ', 'iptv-pro' ) );
}

/** Icônes SVG inline (aucune requête HTTP, aucun font-icon). */
function iptvp_icon( $name, $size = 24 ) {
	$paths = array(
		'bolt'     => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
		'hd'       => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M7 9v6M11 9v6M7 12h4M15 9v6h2a3 3 0 0 0 0-6h-2z"/>',
		'devices'  => '<rect x="2" y="4" width="14" height="10" rx="1"/><path d="M6 18h6M9 14v4"/><rect x="17" y="8" width="5" height="12" rx="1"/>',
		'rocket'   => '<path d="M4.5 16.5c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.7-.8.7-2.1-.1-2.9a2.2 2.2 0 0 0-2.9-.1z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.9A12.9 12.9 0 0 1 22 2c0 2.7-.8 7.5-6 11a22.4 22.4 0 0 1-4 2z"/><path d="M9 12H4s.6-3 2-4c1.6-1.1 5 0 5 0M12 15v5s3-.6 4-2c1.1-1.6 0-5 0-5"/>',
		'guide'    => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h4M8 17h8"/>',
		'support'  => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
		'check'    => '<path d="M20 6 9 17l-5-5"/>',
		'tv'       => '<rect x="2" y="7" width="20" height="13" rx="2"/><path d="m17 2-5 5-5-5"/>',
		'film'     => '<rect x="2" y="2" width="20" height="20" rx="2.2"/><path d="M7 2v20M17 2v20M2 12h20M2 7h5M2 17h5M17 17h5M17 7h5"/>',
		'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
		'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'whatsapp' => '<path d="M3 21l1.6-4.7A8.5 8.5 0 1 1 8 19.6L3 21z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-2-2l.8-1-1-2L9 9.5z"/>',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		'menu'     => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close'    => '<path d="M18 6 6 18M6 6l12 12"/>',
		'arrow'    => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'play'     => '<polygon points="6 3 20 12 6 21 6 3"/>',
		'star'     => '<polygon points="12 2 15.1 8.3 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 8.9 8.3 12 2"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ] // Chaînes statiques internes.
	);
}

/** Fil d'Ariane (le schéma BreadcrumbList est émis par inc/schema.php). */
function iptvp_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = iptvp_breadcrumb_items();
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Fil d’Ariane', 'iptv-pro' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last ) {
			printf( '<li aria-current="page">%s</li>', esc_html( $item['name'] ) );
		} else {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
		}
	}
	echo '</ol></nav>';
}

/** Éléments du fil d'Ariane. */
function iptvp_breadcrumb_items() {
	$items = array( array( 'name' => __( 'Accueil', 'iptv-pro' ), 'url' => home_url( '/' ) ) );

	if ( is_home() ) {
		$items[] = array( 'name' => get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Blog', 'iptv-pro' ), 'url' => get_permalink( (int) get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) );
	} elseif ( is_singular( 'post' ) ) {
		$blog_id = (int) get_option( 'page_for_posts' );
		if ( $blog_id ) {
			$items[] = array( 'name' => get_the_title( $blog_id ), 'url' => get_permalink( $blog_id ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( 'name' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
		}
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array( 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
		}
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term    = get_queried_object();
		$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
	} elseif ( is_search() ) {
		/* translators: %s: search query */
		$items[] = array( 'name' => sprintf( __( 'Recherche : %s', 'iptv-pro' ), get_search_query() ), 'url' => get_search_link() );
	} elseif ( is_404() ) {
		$items[] = array( 'name' => __( 'Page introuvable', 'iptv-pro' ), 'url' => home_url( add_query_arg( array() ) ) );
	} elseif ( is_archive() ) {
		$items[] = array( 'name' => wp_strip_all_tags( get_the_archive_title() ), 'url' => home_url( add_query_arg( array() ) ) );
	}

	return $items;
}

/** Retourne le slug de guide associé à la page courante (meta _iptvp_guide). */
function iptvp_current_guide_slug() {
	if ( ! is_page() ) {
		return '';
	}
	$slug = get_post_meta( get_the_ID(), '_iptvp_guide', true );
	return $slug && isset( iptvp_guides()[ $slug ] ) ? $slug : '';
}

/** Rend une section de template-parts avec arguments. */
function iptvp_section( $name, array $args = array() ) {
	get_template_part( 'template-parts/section', $name, $args );
}
