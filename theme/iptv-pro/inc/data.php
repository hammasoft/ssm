<?php
/**
 * Contenus structurés du site (source unique : utilisés par les templates ET le JSON-LD).
 * Chaque bloc est filtrable : add_filter( 'iptvp_plans', fn( $plans ) => ... ).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

/** Offres d'abonnement. */
function iptvp_plans() {
	$common = array(
		__( '+%s chaînes TV en direct', 'iptv-pro' ),
		__( '+%s films & séries (VOD)', 'iptv-pro' ),
		__( 'Qualité 4K / FHD / HD', 'iptv-pro' ),
		__( 'Technologie anti-coupure', 'iptv-pro' ),
		__( 'Guide TV (EPG) intégré', 'iptv-pro' ),
		__( 'Compatible tous appareils', 'iptv-pro' ),
		__( 'Support client 7j/7', 'iptv-pro' ),
	);
	$features = array_map( function ( $f ) {
		if ( false !== strpos( $f, '%s chaînes' ) ) {
			return sprintf( $f, iptvp_opt( 'channels' ) );
		}
		if ( false !== strpos( $f, '%s films' ) ) {
			return sprintf( $f, iptvp_opt( 'vod' ) );
		}
		return $f;
	}, $common );

	$plans = array(
		array(
			'id'       => '1m',
			'months'   => 1,
			'name'     => __( 'Abonnement IPTV 1 mois', 'iptv-pro' ),
			'label'    => __( '1 Mois', 'iptv-pro' ),
			'price'    => iptvp_opt( 'price_1' ),
			'badge'    => '',
			'featured' => false,
			'features' => $features,
		),
		array(
			'id'       => '3m',
			'months'   => 3,
			'name'     => __( 'Abonnement IPTV 3 mois', 'iptv-pro' ),
			'label'    => __( '3 Mois', 'iptv-pro' ),
			'price'    => iptvp_opt( 'price_3' ),
			'badge'    => '',
			'featured' => false,
			'features' => $features,
		),
		array(
			'id'       => '6m',
			'months'   => 6,
			'name'     => __( 'Abonnement IPTV 6 mois', 'iptv-pro' ),
			'label'    => __( '6 Mois', 'iptv-pro' ),
			'price'    => iptvp_opt( 'price_6' ),
			'badge'    => __( 'Populaire', 'iptv-pro' ),
			'featured' => false,
			'features' => $features,
		),
		array(
			'id'       => '12m',
			'months'   => 12,
			'name'     => __( 'Abonnement IPTV 12 mois', 'iptv-pro' ),
			'label'    => __( '12 Mois', 'iptv-pro' ),
			'price'    => iptvp_opt( 'price_12' ),
			'badge'    => __( 'Meilleure offre', 'iptv-pro' ),
			'featured' => true,
			'features' => $features,
		),
		array(
			'id'       => '24m',
			'months'   => 24,
			'name'     => __( 'Abonnement IPTV 24 mois', 'iptv-pro' ),
			'label'    => __( '24 Mois', 'iptv-pro' ),
			'price'    => iptvp_opt( 'price_24' ),
			'badge'    => __( 'Économie max', 'iptv-pro' ),
			'featured' => false,
			'features' => $features,
		),
	);

	// Retire les offres sans prix (permet de n'afficher que 4 formules, par ex.).
	$plans = array_values( array_filter( $plans, fn( $p ) => '' !== trim( (string) $p['price'] ) ) );

	return apply_filters( 'iptvp_plans', $plans );
}

/** Avantages (section « Pourquoi nous choisir »). */
function iptvp_features() {
	return apply_filters( 'iptvp_features', array(
		array( 'icon' => 'bolt', 'title' => __( 'Stabilité anti-freeze', 'iptv-pro' ), 'text' => __( 'Serveurs haute disponibilité répartis en Europe et technologie anti-coupure : vos matchs et vos films sans gel d’image, même aux heures de pointe.', 'iptv-pro' ) ),
		array( 'icon' => 'hd', 'title' => __( 'Qualité 4K, FHD & HD', 'iptv-pro' ), 'text' => __( 'Flux adaptatifs qui s’ajustent à votre connexion : la meilleure image possible, de la fibre à l’ADSL (8 Mb/s recommandés pour la HD).', 'iptv-pro' ) ),
		array( 'icon' => 'devices', 'title' => __( 'Tous vos écrans', 'iptv-pro' ), 'text' => __( 'Smart TV Samsung & LG, Android TV, Fire TV Stick, box Android, MAG, Apple TV, iPhone, iPad, PC et Mac : un seul abonnement, compatible partout.', 'iptv-pro' ) ),
		array( 'icon' => 'rocket', 'title' => __( 'Activation immédiate', 'iptv-pro' ), 'text' => __( 'Vos identifiants (Xtream Codes / M3U) sont envoyés par e-mail ou WhatsApp en quelques minutes après la commande. Pas de matériel à acheter.', 'iptv-pro' ) ),
		array( 'icon' => 'guide', 'title' => __( 'Guide TV & Replay', 'iptv-pro' ), 'text' => __( 'Programme TV (EPG) complet, catch-up sur une sélection de chaînes et catalogue VOD mis à jour chaque semaine.', 'iptv-pro' ) ),
		array( 'icon' => 'support', 'title' => __( 'Support 7j/7', 'iptv-pro' ), 'text' => __( 'Une équipe francophone joignable par WhatsApp, e-mail et Telegram pour l’installation et le dépannage, 7 jours sur 7.', 'iptv-pro' ) ),
	) );
}

/** Appareils compatibles (liens vers les guides). */
function iptvp_devices() {
	return apply_filters( 'iptvp_devices', array(
		array( 'slug' => 'smart-tv', 'name' => 'Smart TV', 'sub' => 'Samsung · LG · Sony · Philips' ),
		array( 'slug' => 'fire-tv-stick', 'name' => 'Fire TV Stick', 'sub' => 'Amazon Fire TV · Cube' ),
		array( 'slug' => 'android', 'name' => 'Android', 'sub' => 'Box Android · Android TV · Smartphone' ),
		array( 'slug' => 'ios-apple-tv', 'name' => 'iOS & Apple TV', 'sub' => 'iPhone · iPad · Apple TV' ),
		array( 'slug' => 'pc-mac', 'name' => 'PC & Mac', 'sub' => 'Windows · macOS · VLC' ),
		array( 'slug' => 'mag', 'name' => 'Box MAG', 'sub' => 'MAG 322 · 424 · 524' ),
	) );
}

/** Étapes « Comment ça marche ». */
function iptvp_steps() {
	return apply_filters( 'iptvp_steps', array(
		array( 'title' => __( 'Choisissez votre formule', 'iptv-pro' ), 'text' => __( 'Sélectionnez la durée qui vous convient : 1, 3, 6, 12 ou 24 mois. Plus la durée est longue, plus le prix mensuel baisse.', 'iptv-pro' ) ),
		array( 'title' => __( 'Recevez vos identifiants', 'iptv-pro' ), 'text' => __( 'Après paiement sécurisé, vous recevez vos accès (Xtream Codes, lien M3U ou adresse MAC) par e-mail ou WhatsApp.', 'iptv-pro' ) ),
		array( 'title' => __( 'Installez l’application', 'iptv-pro' ), 'text' => __( 'Installez IPTV Smarters Pro ou le lecteur de votre choix et saisissez vos identifiants en suivant notre guide pas-à-pas.', 'iptv-pro' ) ),
		array( 'title' => __( 'Profitez !', 'iptv-pro' ), 'text' => __( 'Chaînes en direct, films, séries et replay sur tous vos écrans, sans engagement et sans parabole.', 'iptv-pro' ) ),
	) );
}

/** FAQ (utilisée sur l'accueil, la page FAQ et dans le schéma FAQPage). */
function iptvp_faq() {
	$brand = iptvp_opt( 'brand' );
	return apply_filters( 'iptvp_faq', array(
		array(
			'q' => __( 'Qu’est-ce qu’un abonnement IPTV ?', 'iptv-pro' ),
			'a' => __( 'L’IPTV (Internet Protocol Television) permet de regarder la télévision en direct et des vidéos à la demande via votre connexion Internet, au lieu d’une antenne, d’une parabole ou d’un décodeur. Un abonnement IPTV vous donne des identifiants à saisir dans une application (comme IPTV Smarters Pro) pour accéder aux contenus sur tous vos écrans.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Qu’est-ce que IPTV Smarters Pro ?', 'iptv-pro' ),
			'a' => __( 'IPTV Smarters Pro est une application lecteur IPTV gratuite, disponible sur Android, iOS, Windows, macOS, Fire TV et certaines Smart TV. Elle ne fournit aucun contenu par elle-même : elle lit le flux de votre fournisseur grâce à vos identifiants Xtream Codes ou à votre lien M3U. Notre abonnement est entièrement compatible.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Quels appareils sont compatibles ?', 'iptv-pro' ),
			'a' => __( 'Smart TV Samsung (Tizen) et LG (webOS), Android TV, Fire TV Stick, box Android, box MAG, Apple TV, iPhone, iPad, smartphones Android, PC Windows et Mac. Un guide d’installation dédié est disponible pour chaque appareil.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Quel débit Internet faut-il ?', 'iptv-pro' ),
			'a' => __( 'Nous recommandons au minimum 8 Mb/s pour la HD, 15 Mb/s pour la Full HD et 25 Mb/s pour la 4K. Une connexion filaire (Ethernet) ou un Wi-Fi 5 GHz offre la meilleure stabilité.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'En combien de temps mon abonnement est-il activé ?', 'iptv-pro' ),
			'a' => __( 'L’activation est généralement réalisée en quelques minutes après la confirmation du paiement (au maximum quelques heures en période de forte affluence). Vos identifiants sont envoyés par e-mail et/ou WhatsApp.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Puis-je utiliser mon abonnement sur plusieurs écrans ?', 'iptv-pro' ),
			'a' => __( 'Un abonnement standard correspond à une connexion simultanée. Vous pouvez installer l’application sur plusieurs appareils, mais ne regarder que sur un écran à la fois. Des formules multi-écrans sont disponibles sur demande auprès du support.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Y a-t-il un engagement ?', 'iptv-pro' ),
			'a' => __( 'Non. Vous payez une seule fois pour la durée choisie, sans renouvellement automatique ni engagement. À l’échéance, vous êtes libre de prolonger ou non.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Quels moyens de paiement acceptez-vous ?', 'iptv-pro' ),
			'a' => __( 'Carte bancaire et autres moyens de paiement sécurisés indiqués lors de la commande. Aucune donnée bancaire n’est stockée sur notre site.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Que faire si l’image se fige ou coupe ?', 'iptv-pro' ),
			'a' => __( 'Redémarrez votre box Internet et votre appareil, privilégiez une connexion filaire, puis videz le cache de l’application. Si le problème persiste, contactez notre support : nous vous aidons en direct sur WhatsApp.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'Qu’est-ce qu’un code IPTV (Xtream Codes ou M3U) ?', 'iptv-pro' ),
			'a' => __( 'Le « code IPTV » désigne vos identifiants d’accès. Le format Xtream Codes comprend une URL de serveur, un nom d’utilisateur et un mot de passe ; le lien M3U est une adresse unique qui contient la playlist. Les deux fonctionnent avec IPTV Smarters Pro ; Xtream Codes est recommandé car il charge aussi le guide TV et la VOD.', 'iptv-pro' ),
		),
		array(
			'q' => __( 'L’IPTV est-elle légale en France ?', 'iptv-pro' ),
			'a' => __( 'Oui, la technologie IPTV est légale : c’est elle qu’utilisent les box des opérateurs. Ce qui compte, c’est que les contenus soient diffusés avec l’autorisation des ayants droit. Les applications comme IPTV Smarters Pro sont de simples lecteurs, légaux eux aussi.', 'iptv-pro' ),
		),
		array(
			/* translators: %s: brand name */
			'q' => sprintf( __( 'Proposez-vous un test avant d’acheter chez %s ?', 'iptv-pro' ), $brand ),
			'a' => __( 'Oui, un test de courte durée peut être demandé via WhatsApp afin de vérifier la compatibilité de votre appareil et la qualité de votre connexion avant de vous abonner.', 'iptv-pro' ),
		),
	) );
}

/**
 * Témoignages : les 9 derniers avis publiés dans « Avis clients » (inc/reviews.php).
 * N'ajoutez que de VRAIS avis clients (faux avis interdits — art. L121-4 C. conso).
 *
 * add_filter( 'iptvp_testimonials', fn() => [ [ 'name' => 'Karim, Lyon', 'text' => '...', 'rating' => 5 ] ] );
 */
function iptvp_testimonials() {
	$items = function_exists( 'iptvp_published_reviews' ) ? iptvp_published_reviews( 9 ) : array();
	return apply_filters( 'iptvp_testimonials', $items );
}

/**
 * Guides d'installation : contenu générique pas-à-pas (schéma HowTo).
 *
 * @return array<string, array{title:string, intro:string, app:string, steps:string[]}>
 */
function iptvp_guides() {
	$login = __( 'Choisissez « Login with Xtream Codes API », puis saisissez un nom de profil, le nom d’utilisateur, le mot de passe et l’URL du serveur reçus par e-mail. Validez avec « Add User ».', 'iptv-pro' );
	return apply_filters( 'iptvp_guides', array(
		'smart-tv' => array(
			'title' => __( 'Installer l’IPTV sur Smart TV Samsung & LG', 'iptv-pro' ),
			'intro' => __( 'Les Smart TV Samsung (Tizen) et LG (webOS) permettent d’installer un lecteur IPTV directement depuis leur magasin d’applications, sans box supplémentaire.', 'iptv-pro' ),
			'app'   => 'IPTV Smarters Pro / IBO Player / Smart IPTV',
			'steps' => array(
				__( 'Connectez votre téléviseur à Internet (câble Ethernet recommandé).', 'iptv-pro' ),
				__( 'Ouvrez le Samsung Smart Hub ou le LG Content Store et recherchez votre lecteur IPTV (IPTV Smarters Pro, IBO Player…).', 'iptv-pro' ),
				__( 'Installez l’application puis ouvrez-la ; notez l’adresse MAC affichée si le lecteur en demande une.', 'iptv-pro' ),
				__( 'Envoyez l’adresse MAC à notre support ou saisissez vos identifiants Xtream Codes directement dans l’application.', 'iptv-pro' ),
				__( 'Redémarrez l’application : les chaînes, films et séries se chargent automatiquement.', 'iptv-pro' ),
			),
		),
		'fire-tv-stick' => array(
			'title' => __( 'Installer IPTV Smarters Pro sur Fire TV Stick', 'iptv-pro' ),
			'intro' => __( 'Le Fire TV Stick d’Amazon est l’un des moyens les plus simples et économiques de regarder l’IPTV sur n’importe quel téléviseur HDMI.', 'iptv-pro' ),
			'app'   => 'IPTV Smarters Pro (via Downloader)',
			'steps' => array(
				__( 'Depuis l’accueil, allez dans Paramètres > Ma Fire TV > Options pour les développeurs et activez « Applications de sources inconnues ».', 'iptv-pro' ),
				__( 'Recherchez et installez l’application gratuite « Downloader » depuis l’Amazon Appstore.', 'iptv-pro' ),
				__( 'Dans Downloader, saisissez l’adresse de téléchargement officielle de l’APK IPTV Smarters Pro et installez-le.', 'iptv-pro' ),
				__( 'Ouvrez IPTV Smarters Pro, acceptez les conditions puis sélectionnez « Login with Xtream Codes API ».', 'iptv-pro' ),
				$login,
			),
		),
		'android' => array(
			'title' => __( 'Installer IPTV Smarters Pro sur Android & Android TV', 'iptv-pro' ),
			'intro' => __( 'Smartphones, tablettes, box Android et téléviseurs Android TV / Google TV : l’installation prend moins de deux minutes.', 'iptv-pro' ),
			'app'   => 'IPTV Smarters Pro',
			'steps' => array(
				__( 'Ouvrez le Google Play Store et recherchez « IPTV Smarters Pro » (ou installez l’APK officiel sur box Android).', 'iptv-pro' ),
				__( 'Installez puis ouvrez l’application et acceptez les conditions d’utilisation.', 'iptv-pro' ),
				__( 'Sélectionnez « Login with Xtream Codes API ».', 'iptv-pro' ),
				$login,
				__( 'Patientez pendant le chargement des chaînes, de la VOD et du guide TV, puis lancez votre programme.', 'iptv-pro' ),
			),
		),
		'ios-apple-tv' => array(
			'title' => __( 'Installer l’IPTV sur iPhone, iPad & Apple TV', 'iptv-pro' ),
			'intro' => __( 'Sur les appareils Apple, plusieurs lecteurs IPTV de l’App Store sont compatibles avec votre abonnement.', 'iptv-pro' ),
			'app'   => 'IPTV Smarters Player Lite / GSE Smart IPTV / iPlayTV',
			'steps' => array(
				__( 'Ouvrez l’App Store et recherchez « Smarters Player Lite » (ou GSE Smart IPTV, iPlayTV).', 'iptv-pro' ),
				__( 'Téléchargez et ouvrez l’application.', 'iptv-pro' ),
				__( 'Choisissez la connexion par Xtream Codes API (ou par lien M3U).', 'iptv-pro' ),
				$login,
				__( 'Sur Apple TV, vous pouvez aussi utiliser AirPlay depuis votre iPhone ou iPad.', 'iptv-pro' ),
			),
		),
		'pc-mac' => array(
			'title' => __( 'Regarder l’IPTV sur PC Windows & Mac', 'iptv-pro' ),
			'intro' => __( 'Sur ordinateur, utilisez l’application IPTV Smarters pour Windows/macOS ou simplement le lecteur VLC avec votre lien M3U.', 'iptv-pro' ),
			'app'   => 'IPTV Smarters (Windows/macOS) ou VLC',
			'steps' => array(
				__( 'Téléchargez IPTV Smarters depuis le site officiel de l’éditeur (ou installez VLC Media Player).', 'iptv-pro' ),
				__( 'Installez puis ouvrez l’application.', 'iptv-pro' ),
				__( 'Avec IPTV Smarters : choisissez « Login with Xtream Codes API » et saisissez vos identifiants.', 'iptv-pro' ),
				__( 'Avec VLC : menu Média > Ouvrir un flux réseau, collez votre lien M3U puis cliquez sur « Lire ».', 'iptv-pro' ),
				__( 'Affichez la playlist (Ctrl+L) pour naviguer entre les chaînes.', 'iptv-pro' ),
			),
		),
		'mag' => array(
			'title' => __( 'Configurer l’IPTV sur box MAG', 'iptv-pro' ),
			'intro' => __( 'Les box MAG (Infomir) fonctionnent avec un portail lié à leur adresse MAC.', 'iptv-pro' ),
			'app'   => 'Portail Stalker (intégré)',
			'steps' => array(
				__( 'Relevez l’adresse MAC inscrite sous la box (commence par 00:1A:79) et communiquez-la au support lors de la commande.', 'iptv-pro' ),
				__( 'Allumez la box puis allez dans Settings > System settings > Servers > Portals.', 'iptv-pro' ),
				__( 'Saisissez un nom pour « Portal 1 name » et collez l’URL du portail reçue par e-mail dans « Portal 1 URL ».', 'iptv-pro' ),
				__( 'Enregistrez avec OK puis redémarrez la box.', 'iptv-pro' ),
				__( 'Le portail se charge automatiquement avec toutes vos chaînes.', 'iptv-pro' ),
			),
		),
	) );
}
