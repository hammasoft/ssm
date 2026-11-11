<?php
/**
 * Import du contenu livré avec le thème (articles + pages) depuis /content.
 *
 * Format d'un fichier : un commentaire HTML contenant du JSON (métadonnées),
 * suivi du corps de l'article en HTML.
 *
 *   <!--
 *   { "title": "…", "slug": "…", "category": "…", "excerpt": "…",
 *     "meta_description": "…", "focus_keyword": "…" }
 *   -->
 *   <p>Contenu…</p>
 *
 * Déclenchement :
 *   - Apparence → « Importer le contenu Beffroi » (bouton), ou
 *   - WP-CLI : `wp beffroi import` (option --update pour réécrire l'existant).
 *
 * Idempotent : un contenu dont le slug existe déjà est ignoré (sauf --update).
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lit un fichier de contenu.
 *
 * @return array{meta: array<string, mixed>, body: string}|null
 */
function bd_parse_content_file( string $file ): ?array {
	$raw = (string) file_get_contents( $file );
	if ( ! preg_match( '/^\s*<!--(.*?)-->\s*(.*)$/s', $raw, $m ) ) {
		return null;
	}
	$meta = json_decode( trim( $m[1] ), true );
	if ( ! is_array( $meta ) || empty( $meta['title'] ) || empty( $meta['slug'] ) ) {
		return null;
	}
	return array( 'meta' => $meta, 'body' => trim( $m[2] ) );
}

/**
 * Remplace les jetons {{phone}}, {{brand}}… par les données entreprise.
 */
function bd_replace_tokens( string $html ): string {
	$tokens = array(
		'{{brand}}'      => bd_business( 'name' ),
		'{{phone}}'      => bd_business( 'phone_display' ),
		'{{tel}}'        => bd_tel_href(),
		'{{delay}}'      => bd_business( 'delay' ),
		'{{price_from}}' => bd_business( 'price_from' ),
		'{{email}}'      => bd_business( 'email' ),
		'{{siret}}'      => bd_business( 'siret' ),
		'{{address}}'    => bd_business( 'street' ) . ', ' . bd_business( 'postal' ) . ' ' . bd_business( 'city' ),
	);
	return strtr( $html, $tokens );
}

/**
 * Lien interne vers un autre contenu importé : {{link:slug|texte}}.
 * Résolu à l'affichage (les slugs peuvent être importés dans n'importe quel ordre).
 */
add_filter(
	'the_content',
	static function ( string $content ): string {
		$content = bd_replace_tokens( $content );
		return (string) preg_replace_callback(
			'/\{\{link:([a-z0-9-]+)\|([^}]+)\}\}/',
			static function ( array $m ): string {
				$post = get_page_by_path( $m[1], OBJECT, array( 'post', 'page' ) );
				$url  = $post instanceof WP_Post ? (string) get_permalink( $post ) : home_url( '/' . $m[1] . '/' );
				return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $m[2] ) );
			},
			$content
		);
	},
	5
);

/**
 * Importe un type de contenu.
 *
 * @return array{created:int, updated:int, skipped:int}
 */
function bd_import_dir( string $dir, string $post_type, bool $update = false ): array {
	$stats = array( 'created' => 0, 'updated' => 0, 'skipped' => 0 );
	$files = glob( BD_DIR . '/content/' . $dir . '/*.html' ) ?: array();
	sort( $files );

	// Dates échelonnées dans le passé : un article tous les 4 jours.
	$offset = count( $files ) * 4;

	foreach ( $files as $file ) {
		$parsed = bd_parse_content_file( $file );
		if ( null === $parsed ) {
			++$stats['skipped'];
			continue;
		}
		$meta     = $parsed['meta'];
		foreach ( array( 'meta_description', 'seo_title', 'excerpt' ) as $key ) {
			if ( isset( $meta[ $key ] ) ) {
				$meta[ $key ] = bd_replace_tokens( (string) $meta[ $key ] );
			}
		}
		$existing = get_page_by_path( $meta['slug'], OBJECT, $post_type );
		$offset  -= 4;

		if ( $existing && ! $update ) {
			++$stats['skipped'];
			continue;
		}

		$postarr = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_title'   => wp_strip_all_tags( $meta['title'] ),
			'post_name'    => sanitize_title( $meta['slug'] ),
			'post_content' => $parsed['body'],
			'post_excerpt' => $meta['excerpt'] ?? '',
		);

		if ( 'post' === $post_type && ! $existing ) {
			$postarr['post_date'] = gmdate( 'Y-m-d H:i:s', time() - max( 0, $offset ) * DAY_IN_SECONDS );
		}
		if ( 'post' === $post_type && ! empty( $meta['category'] ) ) {
			$term = term_exists( $meta['category'], 'category' );
			if ( ! $term ) {
				$term = wp_insert_term( $meta['category'], 'category' );
			}
			if ( ! is_wp_error( $term ) ) {
				$postarr['post_category'] = array( (int) $term['term_id'] );
			}
		}

		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( $postarr, true );
			++$stats['updated'];
		} else {
			$id = wp_insert_post( $postarr, true );
			++$stats['created'];
		}

		if ( is_wp_error( $id ) ) {
			continue;
		}

		if ( ! empty( $meta['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $meta['template'] );
		}
		if ( ! empty( $meta['meta_description'] ) ) {
			update_post_meta( $id, '_bd_meta_description', $meta['meta_description'] );
			// Compatibilité plugins SEO s'ils sont installés plus tard.
			update_post_meta( $id, '_yoast_wpseo_metadesc', $meta['meta_description'] );
			update_post_meta( $id, 'rank_math_description', $meta['meta_description'] );
		}
		if ( ! empty( $meta['focus_keyword'] ) ) {
			update_post_meta( $id, '_bd_focus_keyword', $meta['focus_keyword'] );
			update_post_meta( $id, '_yoast_wpseo_focuskw', $meta['focus_keyword'] );
			update_post_meta( $id, 'rank_math_focus_keyword', $meta['focus_keyword'] );
		}
		if ( ! empty( $meta['seo_title'] ) ) {
			update_post_meta( $id, '_yoast_wpseo_title', $meta['seo_title'] );
			update_post_meta( $id, 'rank_math_title', $meta['seo_title'] );
		}
	}

	return $stats;
}

/**
 * Import complet + réglages de lecture (accueil statique, page des articles, permaliens).
 *
 * @return array<string, array{created:int, updated:int, skipped:int}>
 */
function bd_import_all( bool $update = false ): array {
	$result = array(
		'pages'    => bd_import_dir( 'pages', 'page', $update ),
		'articles' => bd_import_dir( 'articles', 'post', $update ),
	);

	$home = get_page_by_path( 'accueil', OBJECT, 'page' );
	$blog = get_page_by_path( 'conseils', OBJECT, 'page' );
	if ( $home && $blog ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
		update_option( 'page_for_posts', $blog->ID );
	}

	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	update_option( 'blogname', bd_business( 'name' ) );
	update_option( 'blogdescription', bd_business( 'tagline' ) );

	// Supprime le contenu de démonstration WordPress s'il est intact.
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' ) ?: get_page_by_path( 'bonjour-tout-le-monde', OBJECT, 'post' );
	if ( $hello ) {
		wp_trash_post( $hello->ID );
	}

	flush_rewrite_rules();
	update_option( 'bd_content_imported', BD_VERSION );

	return $result;
}

/* ---------- Interface d'administration ---------- */

add_action(
	'admin_menu',
	static function (): void {
		add_theme_page( 'Importer le contenu Beffroi', 'Importer le contenu Beffroi', 'manage_options', 'bd-import', 'bd_import_page' );
	}
);

/**
 * Page d'admin de l'import.
 */
function bd_import_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Importer le contenu Beffroi Dépannage</h1>';

	if ( isset( $_POST['bd_import'] ) && check_admin_referer( 'bd_import' ) ) {
		$res = bd_import_all( ! empty( $_POST['bd_update'] ) );
		echo '<div class="notice notice-success"><p>';
		foreach ( $res as $type => $s ) {
			printf( '%s : %d créé(s), %d mis à jour, %d ignoré(s).<br>', esc_html( ucfirst( $type ) ), (int) $s['created'], (int) $s['updated'], (int) $s['skipped'] );
		}
		echo '</p></div>';
	}

	$count = count( glob( BD_DIR . '/content/articles/*.html' ) ?: array() );
	printf( '<p>Le thème contient <strong>%d articles</strong> optimisés SEO et les pages Accueil, Conseils, Tarifs, Zones d\'intervention, Contact et Mentions légales.</p>', (int) $count );
	echo '<p>L\'import définit aussi la page d\'accueil statique, la page des articles et les permaliens <code>/%postname%/</code>.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'bd_import' );
	echo '<p><label><input type="checkbox" name="bd_update" value="1"> Écraser les contenus existants portant le même slug</label></p>';
	submit_button( 'Lancer l\'import', 'primary', 'bd_import' );
	echo '</form></div>';
}

add_action(
	'admin_notices',
	static function (): void {
		if ( get_option( 'bd_content_imported' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p><strong>Beffroi Dépannage :</strong> importez les articles et pages fournis avec le thème. <a class="button button-primary" href="%s">Importer maintenant</a></p></div>',
			esc_url( admin_url( 'themes.php?page=bd-import' ) )
		);
	}
);

/* ---------- WP-CLI ---------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'beffroi import',
		static function ( array $args, array $assoc ): void {
			$res = bd_import_all( isset( $assoc['update'] ) );
			foreach ( $res as $type => $s ) {
				WP_CLI::log( sprintf( '%s : %d créé(s), %d mis à jour, %d ignoré(s)', $type, $s['created'], $s['updated'], $s['skipped'] ) );
			}
			WP_CLI::success( 'Contenu Beffroi importé.' );
		},
		array( 'shortdesc' => 'Importe les articles et pages livrés avec le thème. --update pour écraser.' )
	);
}
