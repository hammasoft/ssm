<?php
/**
 * Articles SEO 11 à 19 (contenu original).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

return array(

	'iptv-pc-vlc-guide' => array(
		'cat'     => 'guides-iptv',
		'title'   => 'Regarder l’IPTV sur PC avec VLC : guide pas à pas (Windows et Mac)',
		'excerpt' => 'Ouvrir un lien M3U dans VLC, afficher la playlist, ajouter le guide TV et résoudre les erreurs courantes : l’IPTV sur ordinateur en 3 minutes.',
		'content' => array(
			'VLC est le moyen le plus rapide de regarder l’IPTV sur un ordinateur : gratuit, sans inscription et compatible Windows, macOS et Linux.',
			'## Étape 1 : installer VLC',
			'Téléchargez VLC Media Player depuis le site officiel de VideoLAN (videolan.org) et installez-le. Évitez les sites de téléchargement tiers qui ajoutent des logiciels indésirables.',
			'## Étape 2 : ouvrir votre lien M3U',
			'- Menu <strong>Média > Ouvrir un flux réseau</strong> (Ctrl+N sous Windows, Cmd+N sous Mac).
- Collez votre lien M3U reçu par e-mail.
- Cliquez sur <strong>Lire</strong>.',
			'## Étape 3 : naviguer dans les chaînes',
			'Affichez la liste de lecture avec <strong>Ctrl+L</strong> (Cmd+Maj+P sur Mac). Utilisez le champ de recherche pour trouver une chaîne. Double-cliquez pour la lancer.',
			'## Enregistrer la playlist',
			'Menu Média > Enregistrer la liste de lecture : vous la rouvrirez ensuite en un clic sans recopier le lien.',
			'## Ajouter le guide TV',
			'VLC n’affiche pas d’EPG complet. Pour profiter du guide des programmes sur PC, utilisez plutôt l’application IPTV Smarters pour Windows ou macOS avec la connexion Xtream Codes.',
			'## Erreurs fréquentes',
			'- « VLC n’arrive pas à ouvrir le MRL » : lien mal copié ou abonnement expiré.
- Saccades : Outils > Préférences > Tout afficher > Entrée/Codecs > augmentez la « Mise en cache réseau » à 3000 ms.
- Pas de son : Audio > Piste audio, choisissez une autre piste.',
			'Pour comprendre la différence entre lien M3U et Xtream Codes, lisez <a href="{post:code-iptv-xtream-codes-m3u}">notre article sur le code IPTV</a>. Guide synthétique : <a href="{guide:pc-mac}">IPTV sur PC et Mac</a>.',
		),
	),

	'iptv-qui-coupe-buffering-solutions' => array(
		'cat'     => 'depannage-iptv',
		'title'   => 'IPTV qui coupe ou qui freeze : causes et 9 solutions efficaces',
		'excerpt' => 'Image qui gèle, chargement permanent, coupures le soir : comprendre l’origine du buffering IPTV et appliquer les bonnes solutions (Wi-Fi, box, lecteur, DNS).',
		'content' => array(
			'Le « buffering » (mise en mémoire tampon) est le problème le plus fréquent en IPTV. Il peut venir de votre réseau, de votre appareil ou du serveur. Voici comment identifier la cause et la corriger.',
			'## Identifier l’origine du problème',
			'- <strong>Toutes les chaînes coupent</strong> : cause probable côté réseau ou appareil.
- <strong>Une seule chaîne coupe</strong> : cause probable côté flux, signalez-la au support.
- <strong>Uniquement le soir</strong> : saturation du Wi-Fi ou du réseau de votre fournisseur d’accès.',
			'## 9 solutions à appliquer',
			'### 1. Passer en Ethernet',
			'Un câble supprime les interférences Wi-Fi. À défaut, des adaptateurs CPL donnent de bons résultats.',
			'### 2. Utiliser le Wi-Fi 5 GHz',
			'Plus rapide et moins encombré que le 2,4 GHz, à condition d’être proche de la box.',
			'### 3. Redémarrer la box Internet',
			'Débranchez-la 30 secondes : la connexion est renégociée et les tables réseau sont vidées.',
			'### 4. Libérer de la bande passante',
			'Mettez en pause les téléchargements, les sauvegardes cloud et les mises à jour sur les autres appareils.',
			'### 5. Changer de lecteur vidéo',
			'Dans IPTV Smarters Pro : Paramètres > Player Selection, essayez « Native » puis « VLC ».',
			'### 6. Augmenter le tampon',
			'Certaines applications (TiviMate, VLC) permettent d’augmenter la taille du tampon : la lecture démarre une seconde plus tard mais devient plus stable.',
			'### 7. Baisser la qualité',
			'Si votre débit est limité, choisissez la version HD plutôt que 4K d’une chaîne.',
			'### 8. Changer de DNS',
			'Un DNS public peut améliorer la résolution des adresses de serveurs.',
			'### 9. Vérifier les performances de l’appareil',
			'Un vieux boîtier avec 1 Go de RAM peine en 4K. Videz le cache et fermez les applications en arrière-plan.',
			'## Mesurer votre débit',
			'Faites un test de vitesse depuis l’appareil qui lit l’IPTV. Les seuils recommandés sont détaillés dans <a href="{post:quel-debit-internet-pour-iptv}">quel débit pour l’IPTV</a>.',
			'Le problème persiste ? Notre <a href="{contact}">support</a> analyse votre cas gratuitement.',
		),
	),

	'abonnement-iptv-multi-ecrans' => array(
		'cat'     => 'bien-choisir-iptv',
		'title'   => 'Abonnement IPTV multi-écrans : comment regarder sur plusieurs TV en même temps',
		'excerpt' => 'Connexions simultanées, installation sur plusieurs appareils, partage familial : tout savoir sur les abonnements IPTV multi-écrans avant de choisir.',
		'content' => array(
			'Dans un foyer, il est fréquent de vouloir regarder deux programmes différents au même moment. C’est le rôle des abonnements IPTV multi-écrans.',
			'## Installation vs connexions simultanées',
			'Il faut distinguer deux notions :',
			'- <strong>Installation</strong> : vous pouvez installer l’application et vos identifiants sur autant d’appareils que vous le souhaitez.
- <strong>Connexion simultanée</strong> : c’est le nombre d’écrans qui peuvent lire un flux <em>en même temps</em>. Un abonnement standard en autorise une.',
			'## Que se passe-t-il si je dépasse ?',
			'Si un deuxième écran lance une chaîne alors que le premier est déjà en lecture, l’une des deux connexions est refusée ou coupée. Ce n’est pas une panne : c’est la limite de l’abonnement.',
			'## Quelle formule pour quel foyer ?',
			'- Personne seule ou couple regardant ensemble : 1 connexion.
- Famille avec une TV au salon et une en chambre : 2 connexions.
- Grande famille ou colocation : 3 à 4 connexions.',
			'## Astuces pour bien utiliser le multi-écrans',
			'- Pensez à fermer l’application sur un appareil avant de l’ouvrir sur un autre.
- Créez un profil par membre de la famille dans IPTV Smarters Pro.
- Activez le <a href="{post:controle-parental-iptv}">contrôle parental</a> sur les écrans des enfants.',
			'## Le débit nécessaire',
			'Additionnez les débits : deux flux Full HD demandent environ 30 Mb/s. Voir <a href="{post:quel-debit-internet-pour-iptv}">quel débit pour l’IPTV</a>.',
			'Les formules multi-écrans sont disponibles sur demande : <a href="{contact}">contactez notre support</a> ou consultez nos <a href="{tarifs}">tarifs</a>.',
		),
	),

	'test-iptv-comment-evaluer' => array(
		'cat'     => 'bien-choisir-iptv',
		'title'   => 'Test IPTV : comment évaluer un service avant de s’abonner (checklist)',
		'excerpt' => 'Un test IPTV de quelques heures suffit si vous savez quoi vérifier. Notre checklist en 8 points : stabilité aux heures de pointe, 4K, EPG, VOD, support.',
		'content' => array(
			'La plupart des fournisseurs sérieux proposent un test de courte durée. Encore faut-il l’utiliser intelligemment : voici la méthode pour tirer le maximum de quelques heures d’essai.',
			'## Préparer le test',
			'- Installez l’application à l’avance (IPTV Smarters Pro ou TiviMate).
- Demandez le test un jour où vous serez disponible en soirée.
- Utilisez l’appareil et la connexion que vous utiliserez au quotidien.',
			'## La checklist en 8 points',
			'### 1. Stabilité aux heures de pointe',
			'Regardez au moins 30 minutes entre 20 h et 22 h, quand les serveurs sont le plus sollicités.',
			'### 2. Temps de zapping',
			'Une chaîne doit démarrer en 1 à 3 secondes.',
			'### 3. Qualité HD et 4K',
			'Comparez les versions HD et 4K d’une même chaîne et vérifiez la fluidité des mouvements rapides.',
			'### 4. Guide TV',
			'L’EPG doit être rempli pour les chaînes principales et cohérent avec l’heure réelle.',
			'### 5. VOD',
			'Lancez un film et une série : vérifiez le démarrage, l’avance rapide et les pistes audio/sous-titres.',
			'### 6. Catch-up',
			'Si le replay est proposé, testez-le sur une chaîne compatible.',
			'### 7. Réactivité du support',
			'Posez une vraie question technique et chronométrez la réponse.',
			'### 8. Clarté de l’offre',
			'Le prix, la durée, le nombre de connexions et les conditions de remboursement doivent être écrits noir sur blanc.',
			'## Après le test',
			'Si tous les voyants sont au vert, commencez par une formule courte, puis passez à une durée plus longue pour réduire le prix mensuel. Lire <a href="{post:abonnement-iptv-12-mois-ou-1-mois}">1 mois ou 12 mois ?</a>',
			'Demandez votre test directement à notre <a href="{contact}">équipe sur WhatsApp</a>.',
		),
	),

	'iptv-legal-france' => array(
		'cat'     => 'bien-choisir-iptv',
		'title'   => 'L’IPTV est-elle légale en France ? Ce qu’il faut savoir en 2026',
		'excerpt' => 'Technologie, applications, contenus : ce qui est légal et ce qui ne l’est pas avec l’IPTV en France, le rôle de l’Arcom et comment reconnaître un service fiable.',
		'content' => array(
			'« L’IPTV, c’est légal ? » C’est l’une des questions les plus posées. La réponse courte : la technologie est légale, ce qui compte c’est l’origine des contenus.',
			'## La technologie IPTV est légale',
			'Selon l’Arcom (Autorité de régulation de la communication audiovisuelle et numérique), l’IPTV « n’est pas illégale en soi » : c’est une technologie neutre, utilisée notamment par les box de tous les opérateurs français.',
			'## Les applications sont légales',
			'IPTV Smarters Pro, TiviMate, VLC ou GSE Smart IPTV sont des lecteurs. Ils ne fournissent aucun contenu et sont disponibles sur les stores officiels.',
			'## Ce qui peut être illégal : les contenus',
			'Un service devient illicite lorsqu’il donne accès à des chaînes ou à des programmes sans l’autorisation des ayants droit. C’est notamment le cas des retransmissions sportives proposées hors des diffuseurs officiels. L’Arcom peut faire bloquer ces services et les utilisateurs s’exposent eux aussi à des risques.',
			'## Comment reconnaître un service sérieux ?',
			'- Une entreprise identifiable : mentions légales, CGV, coordonnées.
- Une offre claire, sans promesses irréalistes.
- Un paiement sécurisé et une politique de remboursement.
- Pas de publicité intrusive ni de pop-ups douteuses.',
			'L’Arcom publie par ailleurs une liste de services respectueux du droit d’auteur, consultable sur son site.',
			'## Notre engagement',
			'Nous détaillons nos conditions dans nos <a href="{faq}">questions fréquentes</a> et nos conditions générales de vente. Pour toute question, notre <a href="{contact}">équipe</a> vous répond.',
			'Pour aller plus loin, relisez notre guide <a href="{post:iptv-c-est-quoi}">IPTV : c’est quoi ?</a>',
		),
	),

	'epg-guide-tv-iptv' => array(
		'cat'     => 'guides-iptv',
		'title'   => 'EPG IPTV : afficher et mettre à jour le guide TV dans IPTV Smarters Pro',
		'excerpt' => 'Qu’est-ce que l’EPG, pourquoi il est vide, comment le mettre à jour et le décaler dans IPTV Smarters Pro, TiviMate et VLC.',
		'content' => array(
			'L’EPG (Electronic Program Guide) est le guide des programmes de votre IPTV : il indique ce qui passe maintenant et ensuite sur chaque chaîne. Voici comment le faire fonctionner parfaitement.',
			'## D’où vient l’EPG ?',
			'Avec une connexion Xtream Codes, l’EPG est fourni automatiquement par le serveur. Avec un lien M3U, il faut parfois ajouter une URL EPG séparée, au format XMLTV.',
			'## Mettre à jour l’EPG dans IPTV Smarters Pro',
			'- Ouvrez l’application et allez dans <strong>Paramètres</strong>.
- Sélectionnez <strong>EPG</strong> puis <strong>Mettre à jour l’EPG</strong>.
- Patientez une à deux minutes, puis ouvrez la Live TV.',
			'## Le guide est décalé d’une heure ?',
			'C’est un problème de fuseau horaire. Dans Paramètres > Time Format / EPG Timeshift, ajustez le décalage (+1 ou -1). Pensez-y aussi aux changements d’heure de mars et d’octobre.',
			'## L’EPG est vide pour certaines chaînes',
			'Certaines chaînes n’ont pas de programme disponible. Si l’EPG est vide pour toutes les chaînes, forcez la mise à jour, videz le cache de l’application ou reconnectez-vous.',
			'## Dans TiviMate',
			'TiviMate met à jour l’EPG automatiquement. Vous pouvez régler la fréquence de mise à jour dans Paramètres > EPG.',
			'## Dans VLC',
			'VLC n’offre pas de vraie grille de programmes. Utilisez plutôt IPTV Smarters sur PC. Voir <a href="{post:iptv-pc-vlc-guide}">IPTV sur PC</a>.',
			'D’autres soucis avec l’application ? Consultez <a href="{post:iptv-smarters-pro-ne-fonctionne-pas}">nos 12 solutions de dépannage</a>.',
		),
	),

	'abonnement-iptv-12-mois-ou-1-mois' => array(
		'cat'     => 'bien-choisir-iptv',
		'title'   => 'Abonnement IPTV 1, 3, 6, 12 ou 24 mois : quelle durée choisir ?',
		'excerpt' => 'Prix mensuel, flexibilité, risque : comparatif des durées d’abonnement IPTV et notre stratégie pour payer moins cher sans prendre de risque.',
		'content' => array(
			'La plupart des fournisseurs proposent des abonnements de 1 à 24 mois. Plus la durée est longue, plus le prix mensuel baisse. Mais la formule la moins chère n’est pas toujours la meilleure pour commencer.',
			'## Le principe : un paiement unique',
			'Vous payez une seule fois pour toute la durée, sans prélèvement automatique. À l’échéance, vous choisissez de renouveler ou non.',
			'## Comparatif des durées',
			'- <strong>1 mois</strong> : idéal pour découvrir le service. Prix mensuel le plus élevé.
- <strong>3 mois</strong> : bon compromis pour valider la qualité sur la durée.
- <strong>6 mois</strong> : économie significative, engagement modéré.
- <strong>12 mois</strong> : le meilleur rapport qualité/prix pour un usage régulier.
- <strong>24 mois</strong> : le prix mensuel le plus bas, pour les utilisateurs convaincus.',
			'## Notre stratégie recommandée',
			'- Demandez un <a href="{post:test-iptv-comment-evaluer}">test</a>.
- Prenez une formule courte (1 ou 3 mois).
- Si le service vous satisfait, passez à 12 mois au renouvellement.',
			'## Méfiez-vous des offres « trop belles »',
			'Un abonnement de 24 mois vendu à un prix dérisoire par un vendeur non identifiable est un risque : si le service disparaît, l’argent est perdu. Vérifiez toujours les mentions légales et la politique de remboursement.',
			'## Calculer le vrai prix',
			'Divisez le prix total par le nombre de mois pour comparer. Sur notre page <a href="{tarifs}">tarifs</a>, le prix mensuel équivalent est affiché sous chaque formule.',
		),
	),

	'box-mag-formuler-iptv' => array(
		'cat'     => 'guides-iptv',
		'title'   => 'Box MAG et Formuler : configurer l’IPTV facilement',
		'excerpt' => 'Configurer un portail IPTV sur box MAG (322, 424, 524) et Formuler Z (application MyTVOnline) : adresse MAC, URL du portail, réglages et dépannage.',
		'content' => array(
			'Les box MAG (Infomir) et Formuler sont des boîtiers conçus spécialement pour l’IPTV. Leur configuration repose sur l’adresse MAC de l’appareil.',
			'## Étape préalable : l’adresse MAC',
			'L’adresse MAC est inscrite sur l’étiquette sous la box. Sur MAG, elle commence par 00:1A:79. Communiquez-la lors de votre commande : l’abonnement est lié à cette adresse.',
			'## Configurer une box MAG',
			'- Allumez la box, ouvrez <strong>Settings > System settings > Servers > Portals</strong>.
- Dans « Portal 1 name », saisissez un nom (ex. IPTV).
- Dans « Portal 1 URL », collez l’URL du portail reçue par e-mail.
- Validez avec OK, puis redémarrez la box.',
			'## Configurer une Formuler Z (MyTVOnline)',
			'- Ouvrez l’application MyTVOnline.
- Ajoutez un portail : nom au choix et URL du portail fournie.
- Validez : l’adresse MAC de la Formuler est utilisée automatiquement.
- Formuler accepte aussi les connexions Xtream Codes dans les versions récentes.',
			'## Réglages conseillés',
			'- Connectez la box en Ethernet.
- Réglez la résolution de sortie sur celle de votre téléviseur (1080p ou 2160p).
- Mettez à jour le firmware depuis les paramètres.',
			'## Dépannage',
			'- « STB blocked » ou « Authentication failed » : adresse MAC non activée ou mal communiquée.
- Chargement infini : vérifiez l’URL du portail (sans espace, avec le bon port).',
			'Guide synthétique : <a href="{guide:mag}">IPTV sur box MAG</a>. Voir aussi <a href="{post:meilleur-appareil-pour-iptv}">quel appareil choisir</a>.',
		),
	),

	'controle-parental-iptv' => array(
		'cat'     => 'applications-iptv',
		'title'   => 'Contrôle parental IPTV : protéger les enfants dans IPTV Smarters Pro',
		'excerpt' => 'Activer le code parental, masquer des catégories, créer des profils enfants : comment sécuriser votre IPTV dans IPTV Smarters Pro et TiviMate.',
		'content' => array(
			'Un abonnement IPTV donne accès à un vaste catalogue, dont certains contenus ne conviennent pas aux enfants. Heureusement, les principales applications intègrent un contrôle parental efficace.',
			'## Dans IPTV Smarters Pro',
			'- Ouvrez <strong>Paramètres > Contrôle parental</strong>.
- Définissez un code PIN à 4 chiffres (différent de celui de votre téléphone).
- Dans l’onglet des catégories, cochez celles à verrouiller (Live TV, Films, Séries).
- Validez : l’accès à ces catégories demandera désormais le code.',
			'## Dans TiviMate',
			'Paramètres > Contrôle parental : créez un code, puis verrouillez des groupes de chaînes ou masquez-les complètement de la liste.',
			'## Créer un profil dédié aux enfants',
			'Dans IPTV Smarters Pro, vous pouvez ajouter un second profil avec les mêmes identifiants et n’y garder que les catégories jeunesse. Les enfants utilisent ce profil, les adultes le leur.',
			'## Bonnes pratiques',
			'- Ne communiquez pas le code PIN et changez-le régulièrement.
- Masquez plutôt que verrouiller : un contenu invisible n’attire pas la curiosité.
- Utilisez aussi le contrôle parental de l’appareil (Fire TV, Android TV, Apple TV) pour bloquer l’installation d’autres applications.',
			'## Et sur plusieurs écrans ?',
			'Le réglage est enregistré par appareil : configurez-le sur chaque TV et tablette utilisée par les enfants. Voir <a href="{post:abonnement-iptv-multi-ecrans}">l’IPTV multi-écrans</a>.',
			'Besoin d’aide pour configurer ? Notre <a href="{contact}">support</a> vous guide pas à pas.',
		),
	),

);
