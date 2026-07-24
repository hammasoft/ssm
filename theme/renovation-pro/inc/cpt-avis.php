<?php
/**
 * CPT "Avis clients" — témoignages vérifiés (alimente AggregateRating + Review).
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre le CPT `avis_clients`.
 */
function rp_register_cpt_avis(): void {
	$labels = array(
		'name'          => __( 'Avis clients', 'renovation-pro' ),
		'singular_name' => __( 'Avis client', 'renovation-pro' ),
		'menu_name'     => __( 'Avis clients', 'renovation-pro' ),
		'add_new_item'  => __( 'Ajouter un avis', 'renovation-pro' ),
		'edit_item'     => __( 'Modifier l\'avis', 'renovation-pro' ),
		'all_items'     => __( 'Tous les avis', 'renovation-pro' ),
		'not_found'     => __( 'Aucun avis', 'renovation-pro' ),
	);

	register_post_type(
		'avis_clients',
		array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-star-filled',
			'menu_position' => 21,
			'supports'      => array( 'title', 'revisions' ),
			'taxonomies'    => array( 'type_travaux', 'zone' ),
			'show_in_rest'  => true,
			'rewrite'       => array( 'slug' => 'avis', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'rp_register_cpt_avis', 10 );

/**
 * Calcule la note moyenne et le total des avis vérifiés (mise en cache 1 h).
 *
 * @return array{rating: float, count: int}
 */
function rp_get_aggregate_rating(): array {
	$cached = get_transient( 'rp_aggregate_rating' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$avis = get_posts(
		array(
			'post_type'      => 'avis_clients',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'rp_verifie',
					'value' => '1',
				),
			),
		)
	);

	$sum   = 0.0;
	$count = 0;
	foreach ( $avis as $id ) {
		$note = (float) get_post_meta( $id, 'rp_note', true );
		if ( $note > 0 ) {
			$sum += $note;
			++$count;
		}
	}

	$result = array(
		'rating' => $count > 0 ? round( $sum / $count, 1 ) : 0.0,
		'count'  => $count,
	);

	set_transient( 'rp_aggregate_rating', $result, HOUR_IN_SECONDS );
	return $result;
}

/**
 * Invalide le cache d'agrégat quand un avis est modifié.
 */
add_action(
	'save_post_avis_clients',
	static fn() => delete_transient( 'rp_aggregate_rating' )
);
