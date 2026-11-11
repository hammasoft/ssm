<?php
/**
 * À l'activation du thème : création automatique des pages, guides, articles de blog,
 * menus, page d'accueil statique et permaliens SEO (/%postname%/).
 * Idempotent : ne recrée jamais une page existante (recherche par slug).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'iptvp_install_content' );

/** Crée une page/article si le slug n'existe pas encore. Retourne l'ID. */
function iptvp_ensure_post( $slug, $title, $content = '', array $extra = array() ) {
	$type   = $extra['post_type'] ?? 'page';
	$parent = (int) ( $extra['post_parent'] ?? 0 );
	$found  = get_posts( array(
		'name'        => $slug,
		'post_type'   => $type,
		'post_status' => array( 'publish', 'draft', 'private' ),
		'post_parent' => 'page' === $type ? $parent : '',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$meta = $extra['meta'] ?? array();
	unset( $extra['meta'] );
	$id = wp_insert_post( array_merge( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	), $extra ) );
	if ( $id && ! is_wp_error( $id ) ) {
		foreach ( $meta as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
		return (int) $id;
	}
	return 0;
}

/** Paragraphes HTML → blocs Gutenberg simples. */
function iptvp_blocks( array $parts ) {
	$out = '';
	foreach ( $parts as $p ) {
		if ( 0 === strpos( $p, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( substr( $p, 3 ) ) . "</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $p, '### ' ) ) {
			$out .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html( substr( $p, 4 ) ) . "</h3>\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $p, '- ' ) ) {
			$items = array_map( fn( $li ) => '<li>' . wp_kses_post( ltrim( $li, '- ' ) ) . '</li>', explode( "\n", $p ) );
			$out  .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( '', $items ) . "</ul>\n<!-- /wp:list -->\n\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
}

function iptvp_install_content() {
	if ( get_option( 'iptvp_installed' ) ) {
		return;
	}
	$brand = iptvp_opt( 'brand' );
	$email = iptvp_opt( 'email' );

	// --- Pages principales -------------------------------------------------
	$home = iptvp_ensure_post( 'accueil', __( 'Accueil', 'iptv-pro' ), '', array(
		'meta' => array(
			/* translators: %s brand */
			'_iptvp_title' => sprintf( 'Abonnement IPTV Premium 4K – IPTV Smarters Pro | %s', $brand ),
		),
	) );

	$tarifs = iptvp_ensure_post( 'tarifs', __( 'Tarifs abonnement IPTV', 'iptv-pro' ), iptvp_blocks( array(
		'Comparez nos formules d’abonnement IPTV de 1 à 24 mois. Toutes incluent l’ensemble des chaînes, la VOD, la qualité 4K/FHD, le guide TV et le support 7j/7. Seule la durée change : plus elle est longue, plus le prix mensuel est bas.',
	) ), array( 'meta' => array(
		'_iptvp_title' => sprintf( 'Prix Abonnement IPTV : 1, 3, 6, 12 mois | %s', $brand ),
		'_iptvp_desc'  => 'Tarifs abonnement IPTV sans engagement : formules 1, 3, 6, 12 et 24 mois, chaînes en direct + VOD 4K, compatible IPTV Smarters Pro. Activation immédiate.',
	) ) );

	$install = iptvp_ensure_post( 'installation-iptv', __( 'Installation IPTV : guides par appareil', 'iptv-pro' ), iptvp_blocks( array(
		'Retrouvez nos tutoriels pas-à-pas pour installer votre abonnement IPTV sur chaque appareil. Comptez moins de 5 minutes : il suffit d’installer une application lecteur (comme IPTV Smarters Pro) et d’y saisir les identifiants reçus après votre commande.',
		'Besoin d’aide ? Notre support vous accompagne gratuitement par WhatsApp, 7 jours sur 7.',
	) ), array( 'meta' => array(
		'_iptvp_title' => sprintf( 'Installation IPTV Smarters Pro : Smart TV, Firestick, Android | %s', $brand ),
		'_iptvp_desc'  => 'Guides d’installation IPTV pas-à-pas : IPTV Smarters Pro sur Smart TV Samsung & LG, Fire TV Stick, Android, iPhone, Apple TV, PC, Mac et box MAG.',
	) ) );

	foreach ( iptvp_guides() as $slug => $guide ) {
		iptvp_ensure_post( $slug, $guide['title'], iptvp_blocks( array( $guide['intro'] ) ), array(
			'post_parent' => $install,
			'meta'        => array(
				'_iptvp_guide' => $slug,
				'_iptvp_title' => $guide['title'] . ' (guide ' . wp_date( 'Y' ) . ')',
				'_iptvp_desc'  => wp_trim_words( $guide['intro'] . ' Tutoriel complet en 5 étapes avec ' . $guide['app'] . '.', 24, '…' ),
			),
		) );
	}

	$faq = iptvp_ensure_post( 'faq', __( 'FAQ – Questions fréquentes IPTV', 'iptv-pro' ), iptvp_blocks( array(
		'Toutes les réponses à vos questions sur l’abonnement IPTV, l’application IPTV Smarters Pro, la compatibilité de vos appareils, l’activation et le support.',
	) ), array( 'meta' => array(
		'_iptvp_title' => sprintf( 'FAQ Abonnement IPTV & IPTV Smarters Pro | %s', $brand ),
		'_iptvp_desc'  => 'Questions fréquentes sur l’abonnement IPTV : fonctionnement, appareils compatibles, débit requis, activation, multi-écrans, paiement et dépannage.',
	) ) );

	$contact = iptvp_ensure_post( 'contact', __( 'Contact', 'iptv-pro' ), iptvp_blocks( array(
		'Une question avant de commander, besoin d’aide pour l’installation ou envie de tester le service ? Notre équipe vous répond 7 jours sur 7, généralement en moins de 15 minutes sur WhatsApp.',
	) ), array( 'meta' => array(
		'_iptvp_title' => sprintf( 'Contact & Support IPTV 7j/7 | %s', $brand ),
		'_iptvp_desc'  => 'Contactez notre support IPTV par WhatsApp, e-mail ou Telegram : aide à l’installation, test gratuit, questions sur les abonnements. Réponse rapide 7j/7.',
	) ) );

	$blog = iptvp_ensure_post( 'blog', __( 'Blog IPTV', 'iptv-pro' ), '', array( 'meta' => array(
		'_iptvp_title' => sprintf( 'Blog IPTV : guides, astuces et actualités | %s', $brand ),
		'_iptvp_desc'  => 'Guides, comparatifs et astuces pour profiter au mieux de votre abonnement IPTV : applications, appareils, qualité d’image et dépannage.',
	) ) );

	// --- Pages légales (à compléter avec vos informations) ------------------
	$legal = array(
		'mentions-legales' => array( __( 'Mentions légales', 'iptv-pro' ), array(
			'## Éditeur du site',
			'[Raison sociale] – [Forme juridique] au capital de [montant] – Siège : [adresse] – Immatriculation : [RCS / numéro] – Directeur de la publication : [nom] – Contact : ' . $email . '.',
			'## Hébergement',
			'[Nom de l’hébergeur] – [adresse] – [téléphone].',
			'## Propriété intellectuelle',
			'L’ensemble des textes, visuels et éléments graphiques de ce site est la propriété exclusive de ' . $brand . ', sauf mention contraire. Les marques et logos cités appartiennent à leurs propriétaires respectifs et ne sont mentionnés qu’à titre informatif (compatibilité).',
		) ),
		'cgv' => array( __( 'Conditions générales de vente', 'iptv-pro' ), array(
			'## Objet',
			'Les présentes conditions régissent la vente des abonnements proposés par ' . $brand . ' sur ce site.',
			'## Commande et activation',
			'L’abonnement est activé après confirmation du paiement. Les identifiants sont transmis par e-mail ou messagerie. Le client est responsable de la compatibilité de son équipement et de sa connexion Internet (débit minimum recommandé : 8 Mb/s).',
			'## Durée',
			'L’abonnement est conclu pour la durée choisie, sans reconduction automatique.',
			'## Usage',
			'L’abonnement est strictement personnel et limité au nombre de connexions simultanées prévu par la formule. Toute revente ou partage public des identifiants entraîne la suspension du service sans remboursement.',
			'## Droit de rétractation',
			'Conformément à l’article L221-28 du Code de la consommation, le droit de rétractation ne peut être exercé pour un contenu numérique dont l’exécution a commencé avec l’accord exprès du consommateur. Voir notre politique de remboursement.',
			'## Droit applicable',
			'Les présentes CGV sont soumises au droit français. En cas de litige, le client peut recourir gratuitement à un médiateur de la consommation : [nom et coordonnées du médiateur].',
		) ),
		'politique-de-remboursement' => array( __( 'Politique de remboursement', 'iptv-pro' ), array(
			'Nous recommandons de demander un test avant tout achat afin de vérifier la compatibilité de votre appareil et de votre connexion.',
			'En cas de dysfonctionnement imputable à notre service et que notre support ne parvient pas à résoudre sous 72 h, vous pouvez demander un remboursement au prorata de la durée restante en écrivant à ' . $email . '.',
		) ),
		'politique-de-confidentialite' => array( __( 'Politique de confidentialité', 'iptv-pro' ), array(
			'## Données collectées',
			'Nous collectons uniquement les données nécessaires au traitement de votre commande et au support : nom, adresse e-mail, numéro de téléphone/WhatsApp, et le cas échéant l’adresse MAC de votre appareil.',
			'## Finalités et base légale',
			'Exécution du contrat (activation, support) et obligations légales (facturation). Aucune donnée n’est vendue à des tiers.',
			'## Durée de conservation',
			'Les données sont conservées pendant la durée de l’abonnement puis 3 ans à des fins de prospection, ou selon les durées légales pour la facturation.',
			'## Vos droits (RGPD)',
			'Vous disposez d’un droit d’accès, de rectification, d’effacement, d’opposition et de portabilité. Contact : ' . $email . '. Vous pouvez introduire une réclamation auprès de la CNIL.',
			'## Cookies',
			'Ce site utilise des cookies de mesure d’audience uniquement après votre consentement.',
		) ),
	);
	$legal_ids = array();
	foreach ( $legal as $slug => list( $title, $parts ) ) {
		$legal_ids[ $slug ] = iptvp_ensure_post( $slug, $title, iptvp_blocks( $parts ), array( 'meta' => array( '_iptvp_noindex' => 'mentions-legales' === $slug ? '1' : '' ) ) );
	}
	if ( ! empty( $legal_ids['politique-de-confidentialite'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $legal_ids['politique-de-confidentialite'] );
	}

	// --- Articles de blog SEO (contenu original) ---------------------------
	$cat = term_exists( 'guides-iptv', 'category' );
	$cat = $cat ? (int) $cat['term_id'] : (int) ( wp_insert_term( 'Guides IPTV', 'category', array( 'slug' => 'guides-iptv' ) )['term_id'] ?? 0 );
	foreach ( iptvp_seed_posts() as $slug => $post ) {
		iptvp_ensure_post( $slug, $post['title'], iptvp_blocks( $post['content'] ), array(
			'post_type'     => 'post',
			'post_excerpt'  => $post['excerpt'],
			'post_category' => $cat ? array( $cat ) : array(),
			'meta'          => array( '_iptvp_desc' => $post['excerpt'] ),
		) );
	}

	// --- Lecture & permaliens ---------------------------------------------
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}
	if ( $blog ) {
		update_option( 'page_for_posts', $blog );
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	update_option( 'blogdescription', 'Abonnement IPTV premium 4K compatible IPTV Smarters Pro' );
	flush_rewrite_rules();

	// --- Menus -------------------------------------------------------------
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menus     = array(
		'primary' => array( __( 'Menu principal', 'iptv-pro' ), array(
			array( 'page', $home, __( 'Accueil', 'iptv-pro' ) ),
			array( 'page', $tarifs, __( 'Tarifs', 'iptv-pro' ) ),
			array( 'page', $install, __( 'Installation', 'iptv-pro' ) ),
			array( 'page', $faq, __( 'FAQ', 'iptv-pro' ) ),
			array( 'page', $blog, __( 'Blog', 'iptv-pro' ) ),
			array( 'page', $contact, __( 'Contact', 'iptv-pro' ) ),
		) ),
		'footer'  => array( __( 'Liens utiles', 'iptv-pro' ), array(
			array( 'page', $tarifs, __( 'Abonnement IPTV', 'iptv-pro' ) ),
			array( 'page', $install, __( 'Installer IPTV Smarters Pro', 'iptv-pro' ) ),
			array( 'page', $faq, __( 'FAQ IPTV', 'iptv-pro' ) ),
			array( 'page', $contact, __( 'Support', 'iptv-pro' ) ),
		) ),
		'legal'   => array( __( 'Informations légales', 'iptv-pro' ), array_map(
			fn( $slug ) => array( 'page', $legal_ids[ $slug ] ?? 0, $legal[ $slug ][0] ),
			array_keys( $legal )
		) ),
	);
	foreach ( $menus as $location => list( $name, $items ) ) {
		if ( ! empty( $locations[ $location ] ) ) {
			continue;
		}
		$menu_id = wp_get_nav_menu_object( $name ) ? wp_get_nav_menu_object( $name )->term_id : wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! wp_get_nav_menu_items( $menu_id ) ) {
			foreach ( $items as list( $type, $object_id, $label ) ) {
				if ( $object_id ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $label,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $object_id,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					) );
				}
			}
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'iptvp_installed', IPTVP_VERSION );
}

/** Articles de blog de départ — ciblage de requêtes informationnelles. */
function iptvp_seed_posts() {
	return array(
		'comment-installer-iptv-smarters-pro' => array(
			'title'   => 'Comment installer IPTV Smarters Pro en 5 minutes (tous appareils)',
			'excerpt' => 'Tutoriel complet pour installer et configurer IPTV Smarters Pro sur Smart TV, Fire TV Stick, Android, iPhone et PC avec vos identifiants Xtream Codes.',
			'content' => array(
				'IPTV Smarters Pro est l’un des lecteurs IPTV les plus utilisés au monde. Gratuit, ergonomique et disponible sur la plupart des écrans, il permet de lire un abonnement IPTV via les identifiants Xtream Codes ou un lien M3U. Voici comment l’installer et le configurer, étape par étape.',
				'## Avant de commencer',
				'- Un abonnement IPTV actif (vous recevez un nom d’utilisateur, un mot de passe et une URL de serveur).
- Une connexion Internet d’au moins 8 Mb/s (15 Mb/s pour la Full HD, 25 Mb/s pour la 4K).
- Un appareil compatible : Android, Fire TV, iOS, Windows, macOS ou Smart TV.',
				'## Étape 1 : télécharger l’application',
				'Sur Android et Android TV, l’application est disponible sur le Google Play Store. Sur Fire TV Stick, on l’installe via l’application Downloader après avoir autorisé les sources inconnues. Sur iPhone, iPad et Apple TV, la version « Smarters Player Lite » est disponible dans l’App Store. Sur PC et Mac, téléchargez la version officielle depuis le site de l’éditeur.',
				'## Étape 2 : choisir la méthode de connexion',
				'Au premier lancement, acceptez les conditions puis choisissez « Login with Xtream Codes API ». Cette méthode est recommandée car elle charge automatiquement les catégories, le guide TV (EPG) et la VOD.',
				'## Étape 3 : saisir vos identifiants',
				'Renseignez un nom de profil (au choix), puis le nom d’utilisateur, le mot de passe et l’URL du serveur exactement comme indiqués dans votre e-mail d’activation. Attention aux majuscules et aux espaces superflus.',
				'## Étape 4 : profiter du contenu',
				'Après quelques secondes de chargement, vous accédez aux rubriques Live TV, Films, Séries et Catch-up. Pensez à mettre à jour le guide TV depuis les paramètres pour afficher les programmes.',
				'## Problèmes fréquents',
				'Si un message « Invalid credentials » s’affiche, vérifiez la saisie de l’URL (avec http:// et le port éventuel). En cas de coupures, privilégiez une connexion filaire et changez le format de lecture (lecteur natif ou VLC) dans les paramètres. Notre support reste disponible 7j/7 pour vous aider.',
			),
		),
		'quel-debit-internet-pour-iptv' => array(
			'title'   => 'Quel débit Internet pour l’IPTV en HD, Full HD et 4K ?',
			'excerpt' => 'Débit minimum pour regarder l’IPTV sans coupure en HD, Full HD et 4K, et 7 astuces pour améliorer la stabilité de votre connexion.',
			'content' => array(
				'La qualité d’un abonnement IPTV dépend autant du serveur que de votre connexion. Un débit insuffisant ou instable provoque des mises en mémoire tampon (buffering). Voici les valeurs à connaître.',
				'## Débits recommandés',
				'- SD : 4 Mb/s minimum.
- HD (720p) : 8 Mb/s.
- Full HD (1080p) : 15 Mb/s.
- 4K (2160p) : 25 Mb/s et plus.',
				'Ces valeurs s’entendent par écran : si plusieurs personnes utilisent Internet en même temps, prévoyez une marge.',
				'## Comment mesurer votre débit réel',
				'Faites un test de vitesse directement depuis l’appareil qui lit l’IPTV (navigateur de la Smart TV ou application de test sur Fire TV), et non depuis votre smartphone, car le Wi-Fi peut être très différent d’une pièce à l’autre.',
				'## 7 astuces pour éviter les coupures',
				'- Branchez votre appareil en Ethernet plutôt qu’en Wi-Fi.
- En Wi-Fi, utilisez la bande 5 GHz et rapprochez la box.
- Redémarrez votre box Internet régulièrement.
- Fermez les applications qui consomment de la bande passante (téléchargements, mises à jour).
- Videz le cache de votre application IPTV.
- Changez le lecteur vidéo dans les paramètres de l’application.
- Si votre FAI bride certains flux, un DNS public peut améliorer la résolution des adresses.',
				'## Conclusion',
				'Avec 15 Mb/s stables et une connexion filaire, la grande majorité des utilisateurs profitent d’une image Full HD fluide. Avant de vous abonner, demandez un test pour valider votre installation.',
			),
		),
		'meilleur-appareil-pour-iptv' => array(
			'title'   => 'Smart TV, Fire TV Stick ou box Android : quel appareil choisir pour l’IPTV ?',
			'excerpt' => 'Comparatif des meilleurs appareils pour regarder l’IPTV : Smart TV, Fire TV Stick, box Android, Apple TV et MAG. Avantages, limites et notre recommandation.',
			'content' => array(
				'Tous les appareils ne se valent pas pour regarder l’IPTV. Puissance du processeur, compatibilité des applications et qualité du Wi-Fi font la différence. Voici notre comparatif.',
				'## Smart TV (Samsung, LG)',
				'Avantage : aucun boîtier supplémentaire. Limite : le choix d’applications IPTV est plus restreint et certains modèles anciens manquent de puissance pour la 4K.',
				'## Fire TV Stick 4K',
				'Le meilleur rapport qualité/prix : compact, puissant, compatible avec IPTV Smarters Pro via Downloader. Idéal pour transformer n’importe quel téléviseur HDMI en écran IPTV.',
				'## Box Android / Android TV',
				'Grande liberté d’installation et port Ethernet sur la plupart des modèles, ce qui garantit une excellente stabilité. Choisissez un modèle récent avec au moins 2 Go de RAM.',
				'## Apple TV',
				'Très fluide et soignée, mais plus chère. Les applications compatibles sont disponibles sur l’App Store.',
				'## Box MAG',
				'Conçue spécifiquement pour l’IPTV, simple d’utilisation, mais moins polyvalente qu’une box Android.',
				'## Notre recommandation',
				'Pour la majorité des foyers, un Fire TV Stick 4K ou une box Android récente reliée en Ethernet offre la meilleure expérience. Si votre Smart TV est récente, commencez par tester l’application native : c’est gratuit.',
			),
		),
	);
}
