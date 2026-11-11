<?php
/**
 * Options, modèles d'affichage, styles et offres prédéfinies.
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

/** Modèles d'affichage. */
function iob_templates() {
	return array(
		'topbar'  => array( __( 'Barre d’annonce', 'iptv-offer-booster' ), __( 'Bandeau fin et fixe en haut de page. Discret, toujours visible, idéal pour un code promo.', 'iptv-offer-booster' ) ),
		'popup'   => array( __( 'Popup centrée', 'iptv-offer-booster' ), __( 'Fenêtre au centre de l’écran après quelques secondes. Le plus visible, à réserver aux offres fortes.', 'iptv-offer-booster' ) ),
		'exit'    => array( __( 'Popup de sortie', 'iptv-offer-booster' ), __( 'S’ouvre quand le visiteur s’apprête à quitter la page (souris vers le haut, ou défilement rapide vers le haut sur mobile).', 'iptv-offer-booster' ) ),
		'slidein' => array( __( 'Slide-in (coin)', 'iptv-offer-booster' ), __( 'Carte qui glisse dans le coin inférieur après défilement. N’interrompt pas la lecture.', 'iptv-offer-booster' ) ),
		'banner'  => array( __( 'Bannière de page', 'iptv-offer-booster' ), __( 'Grande carte promotionnelle insérée sous l’en-tête de la page d’accueil.', 'iptv-offer-booster' ) ),
	);
}

/** Palettes de couleurs. */
function iob_styles() {
	return array(
		'violet' => array( __( 'Violet néon', 'iptv-offer-booster' ), '#7c3aed', '#2563eb', '#ffffff' ),
		'promo'  => array( __( 'Rouge promo', 'iptv-offer-booster' ), '#ef4444', '#f97316', '#ffffff' ),
		'gold'   => array( __( 'Noir & or', 'iptv-offer-booster' ), '#111827', '#1f2937', '#fbbf24' ),
		'green'  => array( __( 'Vert succès', 'iptv-offer-booster' ), '#059669', '#10b981', '#ffffff' ),
		'blue'   => array( __( 'Bleu confiance', 'iptv-offer-booster' ), '#1d4ed8', '#0ea5e9', '#ffffff' ),
	);
}

/** Offres prédéfinies (préremplissent le contenu dans l'admin). */
function iob_presets() {
	return array(
		'welcome' => array(
			'label'     => __( 'Offre de bienvenue', 'iptv-offer-booster' ),
			'badge'     => __( 'Nouveau client', 'iptv-offer-booster' ),
			'title'     => __( '-15 % sur votre premier abonnement', 'iptv-offer-booster' ),
			'text'      => __( 'Profitez de l’IPTV 4K sur tous vos écrans à prix réduit. Code valable sur toutes les formules.', 'iptv-offer-booster' ),
			'coupon'    => 'BIENVENUE15',
			'button'    => __( 'J’en profite', 'iptv-offer-booster' ),
			'style'     => 'violet',
			'template'  => 'popup',
		),
		'annual' => array(
			'label'     => __( '12 mois + 1 mois offert', 'iptv-offer-booster' ),
			'badge'     => __( 'Meilleure offre', 'iptv-offer-booster' ),
			'title'     => __( '12 mois achetés = 1 mois offert', 'iptv-offer-booster' ),
			'text'      => __( 'Le meilleur prix mensuel de l’année : 13 mois de TV en 4K pour le prix de 12, sans engagement.', 'iptv-offer-booster' ),
			'old_price' => '',
			'new_price' => '',
			'coupon'    => '',
			'button'    => __( 'Voir l’offre 12 mois', 'iptv-offer-booster' ),
			'style'     => 'gold',
			'template'  => 'banner',
		),
		'flash' => array(
			'label'     => __( 'Vente flash / Black Friday', 'iptv-offer-booster' ),
			'badge'     => __( 'Vente flash', 'iptv-offer-booster' ),
			'title'     => __( 'Jusqu’à -30 % sur les abonnements longs', 'iptv-offer-booster' ),
			'text'      => __( 'Offre limitée dans le temps sur les formules 12 et 24 mois.', 'iptv-offer-booster' ),
			'coupon'    => 'FLASH30',
			'button'    => __( 'Je réserve mon prix', 'iptv-offer-booster' ),
			'style'     => 'promo',
			'template'  => 'topbar',
			'countdown' => 1,
		),
		'trial' => array(
			'label'     => __( 'Test gratuit', 'iptv-offer-booster' ),
			'badge'     => __( 'Sans carte bancaire', 'iptv-offer-booster' ),
			'title'     => __( 'Testez gratuitement avant de vous abonner', 'iptv-offer-booster' ),
			'text'      => __( 'Vérifiez la qualité sur votre propre TV et votre connexion. Demande en 30 secondes sur WhatsApp.', 'iptv-offer-booster' ),
			'coupon'    => '',
			'button'    => __( 'Demander mon test', 'iptv-offer-booster' ),
			'style'     => 'green',
			'template'  => 'slidein',
		),
		'exit' => array(
			'label'     => __( 'Rattrapage avant départ', 'iptv-offer-booster' ),
			'badge'     => __( 'Attendez !', 'iptv-offer-booster' ),
			'title'     => __( '-10 % si vous commandez aujourd’hui', 'iptv-offer-booster' ),
			'text'      => __( 'Une question avant de vous décider ? Notre équipe vous répond en quelques minutes.', 'iptv-offer-booster' ),
			'coupon'    => 'RESTE10',
			'button'    => __( 'Utiliser mon code', 'iptv-offer-booster' ),
			'style'     => 'blue',
			'template'  => 'exit',
		),
	);
}

/** Valeurs par défaut. */
function iob_defaults() {
	return array(
		'enabled'      => 0,
		'template'     => 'popup',
		'style'        => 'violet',
		'badge'        => __( 'Offre spéciale', 'iptv-offer-booster' ),
		'title'        => __( '-15 % sur votre premier abonnement', 'iptv-offer-booster' ),
		'text'         => __( 'Profitez de l’IPTV 4K sur tous vos écrans à prix réduit.', 'iptv-offer-booster' ),
		'old_price'    => '',
		'new_price'    => '',
		'price_note'   => '',
		'coupon'       => 'BIENVENUE15',
		'button'       => __( 'J’en profite', 'iptv-offer-booster' ),
		'url'          => home_url( '/#tarifs' ),
		'new_tab'      => 0,
		'image'        => '',
		'end_date'     => '',
		'countdown'    => 0,
		'pages'        => 'home',
		'delay'        => 5,
		'scroll'       => 30,
		'frequency'    => 3,
		'devices'      => 'all',
		'legal'        => '',
	);
}

/** Réglages courants fusionnés avec les valeurs par défaut. */
function iob_settings() {
	return wp_parse_args( (array) get_option( 'iob_settings', array() ), iob_defaults() );
}

/** Timestamp de fin (0 si aucune date). Interprété dans le fuseau du site. */
function iob_end_timestamp( array $s ) {
	if ( empty( $s['end_date'] ) ) {
		return 0;
	}
	try {
		$dt = new DateTimeImmutable( $s['end_date'], wp_timezone() );
		return $dt->getTimestamp();
	} catch ( Exception $e ) {
		return 0;
	}
}

/** L'offre est-elle expirée ? */
function iob_is_expired( array $s ) {
	$end = iob_end_timestamp( $s );
	return $end && $end <= time();
}

/** Nettoyage des réglages enregistrés. */
function iob_sanitize( $input ) {
	$d   = iob_defaults();
	$out = array();
	$out['enabled']    = empty( $input['enabled'] ) ? 0 : 1;
	$out['template']   = isset( iob_templates()[ $input['template'] ?? '' ] ) ? $input['template'] : $d['template'];
	$out['style']      = isset( iob_styles()[ $input['style'] ?? '' ] ) ? $input['style'] : $d['style'];
	foreach ( array( 'badge', 'title', 'old_price', 'new_price', 'price_note', 'coupon', 'button' ) as $k ) {
		$out[ $k ] = sanitize_text_field( $input[ $k ] ?? '' );
	}
	$out['text']       = sanitize_textarea_field( $input['text'] ?? '' );
	$out['legal']      = sanitize_textarea_field( $input['legal'] ?? '' );
	$out['url']        = esc_url_raw( $input['url'] ?? '' ) ?: $d['url'];
	$out['image']      = esc_url_raw( $input['image'] ?? '' );
	$out['new_tab']    = empty( $input['new_tab'] ) ? 0 : 1;
	$out['countdown']  = empty( $input['countdown'] ) ? 0 : 1;
	$out['end_date']   = preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input['end_date'] ?? '' ) ? $input['end_date'] : '';
	$out['pages']      = in_array( $input['pages'] ?? '', array( 'home', 'all', 'all_but_home' ), true ) ? $input['pages'] : 'home';
	$out['devices']    = in_array( $input['devices'] ?? '', array( 'all', 'desktop', 'mobile' ), true ) ? $input['devices'] : 'all';
	$out['delay']      = max( 0, min( 120, (int) ( $input['delay'] ?? 5 ) ) );
	$out['scroll']     = max( 0, min( 100, (int) ( $input['scroll'] ?? 30 ) ) );
	$out['frequency']  = max( 0, min( 365, (int) ( $input['frequency'] ?? 3 ) ) );

	// Un compte à rebours sans vraie date de fin serait une fausse urgence : on le désactive.
	if ( ! $out['end_date'] ) {
		$out['countdown'] = 0;
	}
	// Remise à zéro des statistiques si le contenu de l'offre change.
	$old = iob_settings();
	if ( $old['title'] !== $out['title'] || $old['coupon'] !== $out['coupon'] ) {
		update_option( 'iob_stats', array( 'views' => 0, 'clicks' => 0, 'since' => time() ) );
	}
	return $out;
}
