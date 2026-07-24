<?php
/**
 * Taxonomies partagées : "Type de travaux" et "Ville / Zone".
 *
 * Elles relient réalisations, avis et pages services (maillage + SEO local).
 *
 * @package RenovationPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre les taxonomies personnalisées.
 */
function rp_register_taxonomies(): void {
	$objects = array( 'realisations', 'avis_clients' );

	// Type de travaux (silo par service).
	register_taxonomy(
		'type_travaux',
		$objects,
		array(
			'labels'            => array(
				'name'          => __( 'Types de travaux', 'renovation-pro' ),
				'singular_name' => __( 'Type de travaux', 'renovation-pro' ),
				'menu_name'     => __( 'Types de travaux', 'renovation-pro' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'realisations/type', 'with_front' => false ),
		)
	);

	// Ville / Zone (SEO local).
	register_taxonomy(
		'zone',
		$objects,
		array(
			'labels'            => array(
				'name'          => __( 'Zones', 'renovation-pro' ),
				'singular_name' => __( 'Zone', 'renovation-pro' ),
				'menu_name'     => __( 'Villes / Zones', 'renovation-pro' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'realisations/zone', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'rp_register_taxonomies', 5 ); // avant les CPT (priorité 10)
