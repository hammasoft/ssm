<?php
/**
 * CPT "Réalisations" — portfolio de projets (Avant/Après, budget, durée, zone).
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre le CPT `realisations`.
 */
function rp_register_cpt_realisations(): void {
	$labels = array(
		'name'                  => __( 'Réalisations', 'renovation-pro' ),
		'singular_name'         => __( 'Réalisation', 'renovation-pro' ),
		'menu_name'             => __( 'Réalisations', 'renovation-pro' ),
		'add_new'               => __( 'Ajouter', 'renovation-pro' ),
		'add_new_item'          => __( 'Ajouter une réalisation', 'renovation-pro' ),
		'edit_item'             => __( 'Modifier la réalisation', 'renovation-pro' ),
		'new_item'              => __( 'Nouvelle réalisation', 'renovation-pro' ),
		'view_item'             => __( 'Voir la réalisation', 'renovation-pro' ),
		'search_items'          => __( 'Rechercher une réalisation', 'renovation-pro' ),
		'not_found'             => __( 'Aucune réalisation', 'renovation-pro' ),
		'all_items'             => __( 'Toutes les réalisations', 'renovation-pro' ),
		'featured_image'        => __( 'Photo de couverture', 'renovation-pro' ),
	);

	register_post_type(
		'realisations',
		array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-hammer',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'taxonomies'    => array( 'type_travaux', 'zone' ),
			'show_in_rest'  => true, // éditeur Gutenberg
			'rewrite'       => array( 'slug' => 'realisations', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'rp_register_cpt_realisations', 10 );
