<?php
/**
 * Avis clients réels : type de contenu « Avis clients », formulaire public modéré
 * (shortcode [iptv_avis_form]), affichage sur l'accueil et note agrégée calculée
 * automatiquement pour le JSON-LD.
 *
 * Les avis déposés arrivent « En attente » : vous les validez dans Avis clients.
 * Ne publiez jamais d'avis inventés (art. L121-4 du Code de la consommation).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

const IPTVP_REVIEW_CPT = 'iptvp_avis';

add_action( 'init', function () {
	register_post_type( IPTVP_REVIEW_CPT, array(
		'labels'          => array(
			'name'               => __( 'Avis clients', 'iptv-pro' ),
			'singular_name'      => __( 'Avis client', 'iptv-pro' ),
			'add_new_item'       => __( 'Ajouter un avis reçu', 'iptv-pro' ),
			'edit_item'          => __( 'Modifier l’avis', 'iptv-pro' ),
			'menu_name'          => __( 'Avis clients', 'iptv-pro' ),
			'not_found'          => __( 'Aucun avis pour le moment.', 'iptv-pro' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-star-filled',
		'menu_position'   => 25,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
	) );
} );

/** Badge « en attente » dans le menu admin. */
add_action( 'admin_menu', function () {
	global $menu;
	$pending = (int) wp_count_posts( IPTVP_REVIEW_CPT )->pending;
	if ( ! $pending || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=' . IPTVP_REVIEW_CPT === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod">' . $pending . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
	}
}, 99 );

/* -------------------------------------------------------------------------
 * Meta box : note, ville, appareil, e-mail (privé).
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'iptvp_review', __( 'Détails de l’avis', 'iptv-pro' ), 'iptvp_review_metabox', IPTVP_REVIEW_CPT, 'side', 'high' );
} );

function iptvp_review_metabox( $post ) {
	wp_nonce_field( 'iptvp_review_save', 'iptvp_review_nonce' );
	$rating = (int) get_post_meta( $post->ID, '_iptvp_rating', true ) ?: 5;
	$city   = get_post_meta( $post->ID, '_iptvp_city', true );
	$device = get_post_meta( $post->ID, '_iptvp_device', true );
	$email  = get_post_meta( $post->ID, '_iptvp_email', true );
	?>
	<p><label for="iptvp_rating"><strong><?php esc_html_e( 'Note', 'iptv-pro' ); ?></strong></label><br>
	<select id="iptvp_rating" name="iptvp_rating">
		<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
			<option value="<?php echo (int) $i; ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) ); ?></option>
		<?php endfor; ?>
	</select></p>
	<p><label for="iptvp_city"><?php esc_html_e( 'Ville', 'iptv-pro' ); ?></label><br>
	<input type="text" id="iptvp_city" name="iptvp_city" class="widefat" value="<?php echo esc_attr( $city ); ?>"></p>
	<p><label for="iptvp_device"><?php esc_html_e( 'Appareil utilisé', 'iptv-pro' ); ?></label><br>
	<input type="text" id="iptvp_device" name="iptvp_device" class="widefat" value="<?php echo esc_attr( $device ); ?>"></p>
	<?php if ( $email ) : ?>
		<p><?php esc_html_e( 'E-mail (privé) :', 'iptv-pro' ); ?> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
	<?php endif; ?>
	<p class="description"><?php esc_html_e( 'Publiez uniquement des avis réellement reçus de clients.', 'iptv-pro' ); ?></p>
	<?php
}

add_action( 'save_post_' . IPTVP_REVIEW_CPT, function ( $post_id ) {
	if ( ! isset( $_POST['iptvp_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['iptvp_review_nonce'] ), 'iptvp_review_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_iptvp_rating', max( 1, min( 5, (int) ( $_POST['iptvp_rating'] ?? 5 ) ) ) );
	update_post_meta( $post_id, '_iptvp_city', sanitize_text_field( wp_unslash( $_POST['iptvp_city'] ?? '' ) ) );
	update_post_meta( $post_id, '_iptvp_device', sanitize_text_field( wp_unslash( $_POST['iptvp_device'] ?? '' ) ) );
	delete_transient( 'iptvp_review_stats' );
} );
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( IPTVP_REVIEW_CPT === $post->post_type ) {
		delete_transient( 'iptvp_review_stats' );
	}
}, 10, 3 );

/** Colonnes de la liste admin. */
add_filter( 'manage_' . IPTVP_REVIEW_CPT . '_posts_columns', function ( $cols ) {
	return array_slice( $cols, 0, 2, true ) + array( 'iptvp_rating' => __( 'Note', 'iptv-pro' ), 'iptvp_device' => __( 'Appareil', 'iptv-pro' ) ) + $cols;
} );
add_action( 'manage_' . IPTVP_REVIEW_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	if ( 'iptvp_rating' === $col ) {
		$r = (int) get_post_meta( $post_id, '_iptvp_rating', true );
		echo esc_html( str_repeat( '★', $r ) . str_repeat( '☆', 5 - $r ) );
	} elseif ( 'iptvp_device' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_iptvp_device', true ) );
	}
}, 10, 2 );

/* -------------------------------------------------------------------------
 * Lecture des avis publiés.
 * ---------------------------------------------------------------------- */

/** Avis publiés au format attendu par la section témoignages. */
function iptvp_published_reviews( $limit = 9 ) {
	$posts = get_posts( array(
		'post_type'      => IPTVP_REVIEW_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
	) );
	return array_map( function ( $p ) {
		$city = get_post_meta( $p->ID, '_iptvp_city', true );
		return array(
			'name'   => $p->post_title . ( $city ? ', ' . $city : '' ),
			'author' => $p->post_title,
			'text'   => wp_strip_all_tags( $p->post_content ),
			'rating' => (int) get_post_meta( $p->ID, '_iptvp_rating', true ) ?: 5,
			'device' => get_post_meta( $p->ID, '_iptvp_device', true ),
			'date'   => get_the_date( 'Y-m-d', $p ),
		);
	}, $posts );
}

/** Statistiques (moyenne, nombre) de tous les avis publiés — mises en cache. */
function iptvp_review_stats() {
	$stats = get_transient( 'iptvp_review_stats' );
	if ( false !== $stats ) {
		return $stats;
	}
	$ids   = get_posts( array( 'post_type' => IPTVP_REVIEW_CPT, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
	$sum   = 0;
	foreach ( $ids as $id ) {
		$sum += (int) get_post_meta( $id, '_iptvp_rating', true ) ?: 5;
	}
	$stats = array(
		'count'   => count( $ids ),
		'average' => $ids ? round( $sum / count( $ids ), 1 ) : 0,
	);
	set_transient( 'iptvp_review_stats', $stats, DAY_IN_SECONDS );
	return $stats;
}

/* -------------------------------------------------------------------------
 * Formulaire public [iptv_avis_form].
 * ---------------------------------------------------------------------- */
add_shortcode( 'iptv_avis_form', function () {
	$status = isset( $_GET['avis'] ) ? sanitize_key( $_GET['avis'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	ob_start();
	if ( 'merci' === $status ) {
		echo '<div class="notice notice--success"><p>' . esc_html__( 'Merci ! Votre avis a bien été envoyé. Il sera publié après vérification.', 'iptv-pro' ) . '</p></div>';
	} elseif ( 'erreur' === $status ) {
		echo '<div class="notice notice--error"><p>' . esc_html__( 'Merci de remplir tous les champs obligatoires (avis de 30 caractères minimum).', 'iptv-pro' ) . '</p></div>';
	}
	?>
	<form class="review-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="iptvp_submit_review">
		<?php wp_nonce_field( 'iptvp_submit_review', 'iptvp_nonce' ); ?>
		<p class="review-form__hp" aria-hidden="true"><label>Site web <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

		<fieldset class="review-form__stars">
			<legend><?php esc_html_e( 'Votre note *', 'iptv-pro' ); ?></legend>
			<div class="review-form__stars-row">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<input type="radio" id="rv-star-<?php echo (int) $i; ?>" name="rating" value="<?php echo (int) $i; ?>" <?php checked( 5, $i ); ?> required>
				<label for="rv-star-<?php echo (int) $i; ?>" title="<?php echo esc_attr( $i . '/5' ); ?>">★</label>
			<?php endfor; ?>
			</div>
		</fieldset>

		<div class="review-form__grid">
			<p><label for="rv-name"><?php esc_html_e( 'Prénom *', 'iptv-pro' ); ?></label>
			<input type="text" id="rv-name" name="name" maxlength="40" required></p>
			<p><label for="rv-city"><?php esc_html_e( 'Ville', 'iptv-pro' ); ?></label>
			<input type="text" id="rv-city" name="city" maxlength="40"></p>
			<p><label for="rv-email"><?php esc_html_e( 'E-mail * (jamais publié)', 'iptv-pro' ); ?></label>
			<input type="email" id="rv-email" name="email" required></p>
			<p><label for="rv-device"><?php esc_html_e( 'Appareil', 'iptv-pro' ); ?></label>
			<select id="rv-device" name="device">
				<option value=""><?php esc_html_e( '— Choisir —', 'iptv-pro' ); ?></option>
				<?php foreach ( iptvp_devices() as $d ) : ?>
					<option><?php echo esc_html( $d['name'] ); ?></option>
				<?php endforeach; ?>
			</select></p>
		</div>
		<p><label for="rv-text"><?php esc_html_e( 'Votre avis *', 'iptv-pro' ); ?></label>
		<textarea id="rv-text" name="text" rows="5" minlength="30" maxlength="1200" required></textarea></p>
		<p class="review-form__consent"><label><input type="checkbox" name="consent" value="1" required> <?php esc_html_e( 'J’accepte que mon prénom, ma ville et mon avis soient publiés sur ce site. *', 'iptv-pro' ); ?></label></p>
		<p><button type="submit" class="btn btn--primary"><?php esc_html_e( 'Envoyer mon avis', 'iptv-pro' ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
} );

function iptvp_handle_review() {
	$back = wp_get_referer() ?: home_url( '/laisser-un-avis/' );
	$back = remove_query_arg( 'avis', $back );
	if ( ! isset( $_POST['iptvp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['iptvp_nonce'] ), 'iptvp_submit_review' ) ) {
		wp_safe_redirect( add_query_arg( 'avis', 'erreur', $back ) );
		exit;
	}
	// Pot de miel anti-spam : on fait semblant que tout va bien.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'avis', 'merci', $back ) );
		exit;
	}
	$name   = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$text   = sanitize_textarea_field( wp_unslash( $_POST['text'] ?? '' ) );
	$rating = max( 1, min( 5, (int) ( $_POST['rating'] ?? 5 ) ) );
	if ( ! $name || ! is_email( $email ) || mb_strlen( $text ) < 30 || empty( $_POST['consent'] ) ) {
		wp_safe_redirect( add_query_arg( 'avis', 'erreur', $back ) );
		exit;
	}
	// Limite : 1 avis par adresse IP toutes les 10 minutes.
	$ip_key = 'iptvp_rv_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	if ( get_transient( $ip_key ) ) {
		wp_safe_redirect( add_query_arg( 'avis', 'merci', $back ) );
		exit;
	}
	set_transient( $ip_key, 1, 10 * MINUTE_IN_SECONDS );

	$id = wp_insert_post( array(
		'post_type'    => IPTVP_REVIEW_CPT,
		'post_status'  => 'pending',
		'post_title'   => $name,
		'post_content' => $text,
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_iptvp_rating', $rating );
		update_post_meta( $id, '_iptvp_email', $email );
		update_post_meta( $id, '_iptvp_city', sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ) );
		update_post_meta( $id, '_iptvp_device', sanitize_text_field( wp_unslash( $_POST['device'] ?? '' ) ) );
		wp_mail(
			get_option( 'admin_email' ),
			/* translators: %s: name */
			sprintf( __( '[Avis client] Nouvel avis de %s à valider', 'iptv-pro' ), $name ),
			sprintf( "%s/5\n\n%s\n\n%s", $rating, $text, admin_url( 'post.php?action=edit&post=' . $id ) )
		);
	}
	wp_safe_redirect( add_query_arg( 'avis', 'merci', $back ) );
	exit;
}
add_action( 'admin_post_iptvp_submit_review', 'iptvp_handle_review' );
add_action( 'admin_post_nopriv_iptvp_submit_review', 'iptvp_handle_review' );
