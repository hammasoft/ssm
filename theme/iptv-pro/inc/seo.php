<?php
/**
 * SEO on-page : title, meta description, canonical, robots, Open Graph, Twitter Cards,
 * hreflang, vérification moteurs, GA4.
 *
 * Compatible Yoast / Rank Math / SEOPress : si l'un d'eux est actif, ce module se désactive
 * automatiquement (le JSON-LD « métier » de inc/schema.php reste actif).
 *
 * Meta box « SEO » sur chaque page/article : titre SEO, meta description, noindex.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

/** Un plugin SEO tiers est-il actif ? */
function iptvp_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( 'All_in_One_SEO_Pack' ) || defined( 'AIOSEO_VERSION' );
}

/* -------------------------------------------------------------------------
 * Meta box SEO.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	if ( iptvp_seo_plugin_active() ) {
		return;
	}
	foreach ( array( 'post', 'page' ) as $type ) {
		add_meta_box( 'iptvp_seo', __( 'SEO (IPTV Pro)', 'iptv-pro' ), 'iptvp_seo_metabox', $type, 'normal', 'high' );
	}
} );

function iptvp_seo_metabox( $post ) {
	wp_nonce_field( 'iptvp_seo_save', 'iptvp_seo_nonce' );
	$title   = get_post_meta( $post->ID, '_iptvp_title', true );
	$desc    = get_post_meta( $post->ID, '_iptvp_desc', true );
	$noindex = get_post_meta( $post->ID, '_iptvp_noindex', true );
	?>
	<p><label for="iptvp_title"><strong><?php esc_html_e( 'Titre SEO', 'iptv-pro' ); ?></strong> <small>(50–60 caractères)</small></label>
	<input type="text" id="iptvp_title" name="iptvp_title" class="widefat" maxlength="70" value="<?php echo esc_attr( $title ); ?>"></p>
	<p><label for="iptvp_desc"><strong><?php esc_html_e( 'Meta description', 'iptv-pro' ); ?></strong> <small>(140–158 caractères)</small></label>
	<textarea id="iptvp_desc" name="iptvp_desc" class="widefat" rows="3" maxlength="170"><?php echo esc_textarea( $desc ); ?></textarea></p>
	<p><label><input type="checkbox" name="iptvp_noindex" value="1" <?php checked( $noindex, '1' ); ?>> <?php esc_html_e( 'Ne pas indexer cette page (noindex)', 'iptv-pro' ); ?></label></p>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['iptvp_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['iptvp_seo_nonce'] ), 'iptvp_seo_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_iptvp_title', sanitize_text_field( wp_unslash( $_POST['iptvp_title'] ?? '' ) ) );
	update_post_meta( $post_id, '_iptvp_desc', sanitize_textarea_field( wp_unslash( $_POST['iptvp_desc'] ?? '' ) ) );
	update_post_meta( $post_id, '_iptvp_noindex', empty( $_POST['iptvp_noindex'] ) ? '' : '1' );
} );

/* -------------------------------------------------------------------------
 * Title.
 * ---------------------------------------------------------------------- */
add_filter( 'document_title_separator', fn() => '|' );

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( iptvp_seo_plugin_active() ) {
		return $title;
	}
	if ( is_front_page() ) {
		$custom = is_page() ? get_post_meta( get_queried_object_id(), '_iptvp_title', true ) : '';
		/* translators: %s: brand */
		return $custom ?: sprintf( __( 'Abonnement IPTV Premium 4K – IPTV Smarters Pro | %s', 'iptv-pro' ), iptvp_opt( 'brand' ) );
	}
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_iptvp_title', true );
		if ( $custom ) {
			return $custom;
		}
	}
	return $title;
} );

/** Meta description de la page courante. */
function iptvp_meta_description() {
	$desc = '';
	if ( is_singular() ) {
		$desc = get_post_meta( get_queried_object_id(), '_iptvp_desc', true );
		if ( ! $desc && ! is_front_page() ) {
			$post = get_queried_object();
			$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		}
	}
	if ( ! $desc && is_front_page() ) {
		$desc = sprintf(
			/* translators: 1: channels, 2: vod, 3: price */
			__( 'Abonnement IPTV 4K : +%1$s chaînes, +%2$s films & séries, compatible IPTV Smarters Pro, Smart TV et Firestick. Activation immédiate dès %3$s.', 'iptv-pro' ),
			iptvp_opt( 'channels' ),
			iptvp_opt( 'vod' ),
			iptvp_price( iptvp_opt( 'price_1' ) )
		);
	}
	if ( ! $desc && ( is_category() || is_tag() || is_tax() ) ) {
		$desc = term_description();
	}
	if ( ! $desc ) {
		$desc = get_bloginfo( 'description' );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $desc ) ) );
	return mb_strlen( $desc ) > 158 ? rtrim( mb_substr( $desc, 0, 155 ) ) . '…' : $desc;
}

/** URL canonique propre (sans paramètres de tracking). */
function iptvp_canonical() {
	if ( is_singular() ) {
		return wp_get_canonical_url();
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() ) {
		$url = get_permalink( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$url = get_term_link( get_queried_object() );
	} else {
		return '';
	}
	$paged = (int) get_query_var( 'paged' );
	return $paged > 1 ? trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged ) : $url;
}

/** Image de partage. */
function iptvp_og_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'iptvp-og' );
		if ( $img ) {
			return $img[0];
		}
	}
	return iptvp_opt( 'og_image' ) ?: IPTVP_URI . '/assets/img/og-default.png';
}

/* -------------------------------------------------------------------------
 * Robots : noindex des pages sans valeur SEO.
 * ---------------------------------------------------------------------- */
add_filter( 'wp_robots', function ( $robots ) {
	if ( iptvp_seo_plugin_active() ) {
		return $robots;
	}
	$noindex = is_search() || is_404() || is_author() || is_date() || is_attachment()
		|| ( is_singular() && get_post_meta( get_queried_object_id(), '_iptvp_noindex', true ) );
	if ( $noindex ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['max-image-preview'] );
	} else {
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = '-1';
		$robots['max-video-preview'] = '-1';
	}
	return $robots;
} );

/* -------------------------------------------------------------------------
 * Balises <head>.
 * ---------------------------------------------------------------------- */
add_action( 'wp_head', function () {
	if ( ! iptvp_seo_plugin_active() ) {
		$desc      = iptvp_meta_description();
		$canonical = iptvp_canonical();
		$title     = wp_get_document_title();
		$image     = iptvp_og_image();
		$type      = is_singular( 'post' ) ? 'article' : 'website';

		if ( $desc ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		// WordPress émet déjà rel=canonical sur les contenus singuliers.
		if ( $canonical && ! is_singular() ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
		}
		printf( '<link rel="alternate" hreflang="%1$s" href="%2$s">' . "\n", esc_attr( str_replace( '_', '-', get_locale() ) ), esc_url( $canonical ?: home_url( '/' ) ) );
		printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $canonical ?: home_url( '/' ) ) );

		$og = array(
			'og:locale'      => get_locale(),
			'og:type'        => $type,
			'og:site_name'   => iptvp_opt( 'brand' ),
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $canonical ?: home_url( '/' ),
			'og:image'       => $image,
			'og:image:width' => '1200',
			'og:image:height'=> '630',
		);
		if ( is_singular( 'post' ) ) {
			$og['article:published_time'] = get_the_date( 'c' );
			$og['article:modified_time']  = get_the_modified_date( 'c' );
		}
		foreach ( $og as $prop => $content ) {
			if ( '' !== (string) $content ) {
				printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $prop ), esc_attr( $content ) );
			}
		}
		printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}

	if ( iptvp_opt( 'gsc_verify' ) ) {
		printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( iptvp_opt( 'gsc_verify' ) ) );
	}
	if ( iptvp_opt( 'bing_verify' ) ) {
		printf( '<meta name="msvalidate.01" content="%s">' . "\n", esc_attr( iptvp_opt( 'bing_verify' ) ) );
	}
	echo '<meta name="theme-color" content="#0b0b1e">' . "\n";
}, 1 );

/** GA4 (chargé en différé, uniquement si un ID est renseigné). */
add_action( 'wp_footer', function () {
	$id = iptvp_opt( 'ga4_id' );
	if ( ! $id || is_user_logged_in() ) {
		return;
	}
	printf(
		'<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","%1$s",{anonymize_ip:true});</script>' . "\n",
		esc_attr( $id )
	);
}, 99 );

/** robots.txt enrichi (sitemap natif WordPress /wp-sitemap.xml). */
add_filter( 'robots_txt', function ( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}
	$output .= "Disallow: /?s=\nDisallow: /search/\nDisallow: /*?replytocom=\n";
	if ( ! iptvp_seo_plugin_active() ) {
		$output .= 'Sitemap: ' . home_url( '/wp-sitemap.xml' ) . "\n";
	}
	return $output;
}, 10, 2 );

/** Sitemap natif : exclut utilisateurs et pages noindex. */
add_filter( 'wp_sitemaps_add_provider', fn( $provider, $name ) => 'users' === $name ? false : $provider, 10, 2 );
add_filter( 'wp_sitemaps_posts_query_args', function ( $args ) {
	$args['meta_query'] = array(
		'relation' => 'OR',
		array( 'key' => '_iptvp_noindex', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_iptvp_noindex', 'value' => '1', 'compare' => '!=' ),
	);
	return $args;
} );

/** Redirige les pages pièces jointes vers le fichier (évite le contenu mince). */
add_action( 'template_redirect', function () {
	if ( is_attachment() ) {
		$url = wp_get_attachment_url( get_queried_object_id() );
		if ( $url ) {
			wp_safe_redirect( $url, 301 );
			exit;
		}
	}
} );
