<?php
/**
 * Customizer : coordonnées de l'entreprise modifiables sans code.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'bd_business',
			array(
				'title'       => 'Beffroi : entreprise',
				'description' => 'Coordonnées affichées partout (en-tête, bouton d\'appel, pied de page, données structurées). Gardez-les identiques à votre fiche Google.',
				'priority'    => 30,
			)
		);

		$labels = array(
			'name'          => 'Nom commercial',
			'tagline'       => 'Signature',
			'phone_display' => 'Téléphone affiché (ex. 03 20 12 34 56)',
			'phone_e164'    => 'Téléphone international (ex. +33320123456)',
			'email'         => 'E-mail',
			'street'        => 'Adresse',
			'postal'        => 'Code postal',
			'city'          => 'Ville',
			'lat'           => 'Latitude',
			'lng'           => 'Longitude',
			'siret'         => 'SIRET',
			'delay'         => 'Délai moyen d\'intervention',
			'price_from'    => 'Prix du déplacement + diagnostic (€)',
			'rating'        => 'Note moyenne Google (ex. 4.9)',
			'review_count'  => 'Nombre d\'avis Google (0 = masqué)',
			'google_url'    => 'Lien vers la fiche Google (avis)',
		);

		$defaults = bd_business_defaults();
		foreach ( $labels as $key => $label ) {
			$wp_customize->add_setting(
				'bd_' . $key,
				array(
					'default'           => $defaults[ $key ],
					'sanitize_callback' => 'google_url' === $key ? 'esc_url_raw' : 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'bd_' . $key,
				array(
					'label'   => $label,
					'section' => 'bd_business',
					'type'    => 'email' === $key ? 'email' : ( 'google_url' === $key ? 'url' : 'text' ),
				)
			);
		}
	}
);
