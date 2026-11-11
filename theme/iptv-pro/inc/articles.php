<?php
/**
 * 19 articles SEO (inc/articles-content-*.php) + outil d'import dans
 * Apparence > Contenus IPTV Pro (utile si le thème était déjà activé).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

/** Catégories du blog (slug => nom, description SEO). */
function iptvp_article_categories() {
	return array(
		'guides-iptv'       => array( 'Guides IPTV', 'Tutoriels pas à pas pour installer et configurer votre abonnement IPTV sur Smart TV, Fire TV Stick, Android, Apple TV, PC et box MAG.' ),
		'applications-iptv' => array( 'Applications IPTV', 'Comparatifs et réglages des meilleures applications IPTV : IPTV Smarters Pro, TiviMate, IBO Player, GSE Smart IPTV, VLC.' ),
		'depannage-iptv'    => array( 'Dépannage IPTV', 'Solutions aux problèmes IPTV les plus fréquents : coupures, écran noir, identifiants refusés, guide TV vide.' ),
		'bien-choisir-iptv' => array( 'Bien choisir son IPTV', 'Conseils pour choisir un abonnement IPTV fiable : critères, durées, multi-écrans, test et cadre légal.' ),
	);
}

/** Les 19 articles. */
function iptvp_articles() {
	static $articles = null;
	if ( null === $articles ) {
		$articles = array_merge(
			require IPTVP_DIR . '/inc/articles-content-1.php',
			require IPTVP_DIR . '/inc/articles-content-2.php'
		);
		$faq = require IPTVP_DIR . '/inc/articles-faq.php';
		foreach ( $articles as $slug => &$article ) {
			$article['faq'] = $faq[ $slug ] ?? array();
		}
		unset( $article );
	}
	return apply_filters( 'iptvp_articles', $articles );
}

/** Remplace les jetons de liens internes par les vraies URL. */
function iptvp_article_links( $html ) {
	$map = array(
		'{tarifs}'  => home_url( '/tarifs/' ),
		'{install}' => home_url( '/installation-iptv/' ),
		'{contact}' => home_url( '/contact/' ),
		'{faq}'     => home_url( '/faq/' ),
		'{avis}'    => home_url( '/laisser-un-avis/' ),
	);
	$html = strtr( $html, $map );
	return preg_replace_callback( '/\{(guide|post):([a-z0-9-]+)\}/', function ( $m ) {
		return 'guide' === $m[1] ? home_url( '/installation-iptv/' . $m[2] . '/' ) : home_url( '/' . $m[2] . '/' );
	}, $html );
}

/** Crée (ou retrouve) une catégorie et retourne son ID. */
function iptvp_ensure_category( $slug ) {
	$cats = iptvp_article_categories();
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term ) {
		return (int) $term->term_id;
	}
	$res = wp_insert_term( $cats[ $slug ][0] ?? $slug, 'category', array(
		'slug'        => $slug,
		'description' => $cats[ $slug ][1] ?? '',
	) );
	return is_wp_error( $res ) ? 0 : (int) $res['term_id'];
}

/**
 * Importe les articles manquants.
 *
 * @param string $mode 'publish' = tout publier maintenant ; 'schedule' = 1 article tous les $every jours.
 * @param int    $every Intervalle en jours pour le mode programmé.
 * @return int Nombre d'articles créés.
 */
function iptvp_import_articles( $mode = 'publish', $every = 2 ) {
	$created = 0;
	$i       = 0;
	foreach ( iptvp_articles() as $slug => $article ) {
		if ( get_page_by_path( $slug, OBJECT, 'post' ) ) {
			continue;
		}
		$args = array(
			'post_type'     => 'post',
			'post_excerpt'  => $article['excerpt'],
			'post_category' => array_filter( array( iptvp_ensure_category( $article['cat'] ) ) ),
			'meta'          => array( '_iptvp_desc' => $article['excerpt'] ),
		);
		if ( 'schedule' === $mode ) {
			$time                  = time() + ( $i * max( 1, (int) $every ) * DAY_IN_SECONDS ) + HOUR_IN_SECONDS;
			$args['post_status']   = $i ? 'future' : 'publish';
			$args['post_date']     = wp_date( 'Y-m-d H:i:s', $time );
			$args['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $time );
		}
		$parts = $article['content'];
		if ( $article['faq'] ) {
			$parts[] = '## Questions fréquentes';
			foreach ( $article['faq'] as list( $q, $a ) ) {
				$parts[] = '### ' . $q;
				$parts[] = $a;
			}
		}
		$id = iptvp_ensure_post( $slug, $article['title'], iptvp_article_links( iptvp_blocks( $parts ) ), $args );
		if ( $id ) {
			++$created;
			++$i;
		}
	}
	return $created;
}

/* -------------------------------------------------------------------------
 * Page d'admin : Apparence > Contenus IPTV Pro.
 * ---------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_theme_page( __( 'Contenus IPTV Pro', 'iptv-pro' ), __( 'Contenus IPTV Pro', 'iptv-pro' ), 'manage_options', 'iptvp-content', 'iptvp_content_page' );
} );

add_action( 'admin_post_iptvp_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'iptvp_import' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'iptv-pro' ) );
	}
	$what  = sanitize_key( $_POST['what'] ?? 'articles' );
	$mode  = 'schedule' === ( $_POST['mode'] ?? '' ) ? 'schedule' : 'publish';
	$every = max( 1, min( 14, (int) ( $_POST['every'] ?? 2 ) ) );
	$count = 0;
	if ( 'pages' === $what ) {
		iptvp_install_content( true );
	} else {
		$count = iptvp_import_articles( $mode, $every );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'iptvp-content', 'done' => $what, 'count' => $count ), admin_url( 'themes.php' ) ) );
	exit;
} );

function iptvp_content_page() {
	$articles = iptvp_articles();
	$cats     = iptvp_article_categories();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contenus IPTV Pro', 'iptv-pro' ); ?></h1>
		<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				echo 'pages' === $_GET['done'] // phpcs:ignore
					? esc_html__( 'Pages, menus et avis : contenu manquant créé.', 'iptv-pro' )
					/* translators: %d count */
					: esc_html( sprintf( __( '%d article(s) importé(s).', 'iptv-pro' ), (int) ( $_GET['count'] ?? 0 ) ) ); // phpcs:ignore
				?>
			</p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( '1. Pages du site', 'iptv-pro' ); ?></h2>
		<p><?php esc_html_e( 'Crée les pages manquantes (Tarifs, Installation + guides, FAQ, Contact, Laisser un avis, pages légales) et les menus. Les pages existantes ne sont jamais modifiées.', 'iptv-pro' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'iptvp_import' ); ?>
			<input type="hidden" name="action" value="iptvp_import"><input type="hidden" name="what" value="pages">
			<?php submit_button( __( 'Créer les pages manquantes', 'iptv-pro' ), 'secondary', 'submit', false ); ?>
		</form>

		<h2 style="margin-top:2em"><?php esc_html_e( '2. Articles SEO du blog', 'iptv-pro' ); ?></h2>
		<p><?php esc_html_e( 'Publier régulièrement envoie un signal de fraîcheur à Google : la programmation progressive est recommandée.', 'iptv-pro' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'iptvp_import' ); ?>
			<input type="hidden" name="action" value="iptvp_import"><input type="hidden" name="what" value="articles">
			<p>
				<label><input type="radio" name="mode" value="schedule" checked> <?php esc_html_e( 'Programmer : 1 article publié tous les', 'iptv-pro' ); ?></label>
				<input type="number" name="every" value="2" min="1" max="14" style="width:4em"> <?php esc_html_e( 'jours (le 1er est publié tout de suite)', 'iptv-pro' ); ?><br>
				<label><input type="radio" name="mode" value="publish"> <?php esc_html_e( 'Tout publier maintenant', 'iptv-pro' ); ?></label>
			</p>
			<?php submit_button( __( 'Importer les articles manquants', 'iptv-pro' ), 'primary', 'submit', false ); ?>
		</form>

		<table class="widefat striped" style="margin-top:1.5em;max-width:1000px">
			<thead><tr><th><?php esc_html_e( 'Article', 'iptv-pro' ); ?></th><th><?php esc_html_e( 'Catégorie', 'iptv-pro' ); ?></th><th><?php esc_html_e( 'Statut', 'iptv-pro' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $articles as $slug => $a ) : $p = get_page_by_path( $slug, OBJECT, 'post' ); ?>
				<tr>
					<td><?php echo $p ? '<a href="' . esc_url( get_edit_post_link( $p ) ) . '">' . esc_html( $a['title'] ) . '</a>' : esc_html( $a['title'] ); ?></td>
					<td><?php echo esc_html( $cats[ $a['cat'] ][0] ?? $a['cat'] ); ?></td>
					<td><?php echo $p ? esc_html( 'future' === $p->post_status ? sprintf( __( 'Programmé le %s', 'iptv-pro' ), get_the_date( '', $p ) ) : __( 'Publié', 'iptv-pro' ) ) : '—'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
