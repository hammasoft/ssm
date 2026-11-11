<?php
/**
 * FAQ ajoutée en fin de chaque article (cible les « Autres questions posées » de Google).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

return array(
	'iptv-c-est-quoi' => array(
		array( 'Faut-il un décodeur pour regarder l’IPTV ?', 'Non. Une application installée sur une Smart TV, un Fire TV Stick, une box Android, un smartphone ou un ordinateur suffit. Le décodeur de votre opérateur n’est pas nécessaire : seule la connexion Internet compte.' ),
		array( 'Quelle est la différence entre IPTV et streaming ?', 'Le streaming désigne la lecture d’une vidéo pendant son téléchargement. L’IPTV est une forme de streaming organisée comme une télévision : chaînes en direct, guide des programmes, catégories et replay, le tout lu dans une application dédiée.' ),
		array( 'L’IPTV fonctionne-t-elle avec la 4G ou la 5G ?', 'Oui, à condition d’avoir un débit suffisant et un forfait data adapté : une heure en Full HD consomme environ 2 à 3 Go. À la maison, une connexion fibre ou ADSL reste la solution la plus économique et la plus stable.' ),
	),
	'meilleur-abonnement-iptv-comment-choisir' => array(
		array( 'Quel est le meilleur abonnement IPTV ?', 'Il n’existe pas de réponse universelle : le meilleur abonnement est celui qui reste stable avec votre connexion, fonctionne sur votre appareil et offre un support réactif. D’où l’importance de tester avant de vous engager sur une longue durée.' ),
		array( 'Un abonnement IPTV cher est-il forcément meilleur ?', 'Pas nécessairement. Le prix reflète surtout la durée et le nombre de connexions. Comparez plutôt la stabilité aux heures de pointe, la qualité du support et la transparence du vendeur.' ),
		array( 'Peut-on changer de fournisseur IPTV facilement ?', 'Oui. Il suffit de saisir les nouveaux identifiants dans votre application. Aucun matériel n’est à rendre et, sans engagement, rien ne vous retient à l’échéance.' ),
	),
	'code-iptv-xtream-codes-m3u' => array(
		array( 'Peut-on convertir un lien M3U en Xtream Codes ?', 'Souvent oui : un lien M3U contient généralement l’adresse du serveur, le nom d’utilisateur et le mot de passe. Il suffit de les reporter dans les champs Xtream Codes de l’application. En cas de doute, demandez les deux formats à votre fournisseur.' ),
		array( 'Mon code IPTV fonctionne-t-il sur plusieurs appareils ?', 'Vous pouvez l’enregistrer sur plusieurs appareils, mais le nombre d’écrans qui regardent en même temps dépend de votre formule (généralement une connexion simultanée).' ),
		array( 'Que faire si mon code IPTV est refusé ?', 'Vérifiez la saisie (majuscules, espaces, http:// et port), la date d’expiration et qu’aucun autre écran n’utilise l’abonnement. Si tout est correct, contactez le support avec une capture d’écran de l’erreur.' ),
	),
	'iptv-smarters-pro-ne-fonctionne-pas' => array(
		array( 'Pourquoi IPTV Smarters Pro affiche un écran noir ?', 'Le plus souvent, le lecteur vidéo intégré ne prend pas en charge le format du flux. Changez de lecteur dans Paramètres > Player Selection, puis essayez de basculer le format entre TS et HLS.' ),
		array( 'IPTV Smarters Pro a-t-il été supprimé du Play Store ?', 'La disponibilité varie selon les pays et les périodes. Si l’application n’apparaît pas, utilisez la version officielle proposée sur le site de l’éditeur et évitez les copies publiées par des tiers.' ),
		array( 'Comment mettre à jour IPTV Smarters Pro ?', 'Depuis le store de votre appareil si l’application y est installée, sinon en téléchargeant la dernière version officielle puis en l’installant par-dessus l’ancienne. Vos profils sont généralement conservés.' ),
	),
	'tivimate-vs-iptv-smarters-pro' => array(
		array( 'TiviMate fonctionne-t-il sur iPhone ?', 'Non, TiviMate est réservé aux appareils Android TV, Google TV et Fire TV. Sur iPhone, iPad et Apple TV, utilisez Smarters Player Lite ou GSE Smart IPTV.' ),
		array( 'Peut-on utiliser TiviMate et IPTV Smarters avec le même abonnement ?', 'Oui, les mêmes identifiants fonctionnent dans les deux applications. Attention simplement à ne pas lancer de lecture sur les deux en même temps si votre formule n’autorise qu’une connexion.' ),
		array( 'TiviMate Premium vaut-il le coup ?', 'Si vous regardez beaucoup la TV en direct et voulez l’enregistrement, les rappels et plusieurs playlists, la version premium apporte un vrai confort. Pour un usage occasionnel, la version gratuite ou IPTV Smarters Pro suffit.' ),
	),
	'meilleures-applications-iptv' => array(
		array( 'Quelle est la meilleure application IPTV gratuite ?', 'IPTV Smarters Pro est la plus polyvalente parmi les applications gratuites. VLC est aussi gratuit et très pratique pour tester un lien M3U sur ordinateur.' ),
		array( 'Une application IPTV donne-t-elle accès à des chaînes ?', 'Non. Une application IPTV est uniquement un lecteur : elle affiche les chaînes de l’abonnement dont vous saisissez les identifiants. Sans abonnement, elle reste vide.' ),
		array( 'Les applications IPTV sont-elles sûres ?', 'Oui si vous les installez depuis un store officiel ou le site de l’éditeur. Évitez les APK modifiés distribués sur des forums : ils peuvent contenir des logiciels malveillants.' ),
	),
	'installer-iptv-samsung-smart-tv' => array(
		array( 'Pourquoi je ne trouve pas IPTV Smarters sur ma TV Samsung ?', 'Le catalogue du Smart Hub varie selon le pays et l’année du téléviseur. Cherchez aussi « Smarters Player » ou utilisez une alternative comme IBO Player, ou un Fire TV Stick branché en HDMI.' ),
		array( 'Peut-on installer un APK sur une TV Samsung ?', 'Non, les téléviseurs Samsung fonctionnent sous Tizen, incompatible avec les fichiers APK Android. Passez par les applications du Smart Hub ou par un boîtier Android externe.' ),
		array( 'Quelle TV Samsung est compatible IPTV ?', 'La plupart des modèles Tizen depuis 2017 gèrent les lecteurs IPTV actuels. Pour la 4K, un modèle récent connecté en Ethernet offre la meilleure expérience.' ),
	),
	'installer-iptv-lg-smart-tv-webos' => array(
		array( 'Quelle application IPTV sur LG ?', 'IBO Player et Smart IPTV sont les plus utilisées sur webOS. IPTV Smarters est disponible sur certains modèles et certaines régions du LG Content Store.' ),
		array( 'Où trouver l’adresse MAC de ma TV LG ?', 'Elle s’affiche à l’ouverture de l’application IPTV (IBO Player, Smart IPTV). Vous la trouvez aussi dans Paramètres > Général > À propos de ce téléviseur > Informations réseau.' ),
		array( 'L’IPTV fonctionne-t-elle sur une ancienne TV LG ?', 'Les modèles sous webOS 3.0 et plus récents sont généralement compatibles. Sur les modèles plus anciens, un Fire TV Stick ou une box Android est la solution la plus simple.' ),
	),
	'installer-iptv-android-tv-google-tv' => array(
		array( 'Chromecast avec Google TV est-il bon pour l’IPTV ?', 'Oui, il accède au Play Store et fait tourner IPTV Smarters Pro et TiviMate sans difficulté. Ajoutez un adaptateur Ethernet USB-C pour une stabilité maximale en 4K.' ),
		array( 'Comment autoriser les sources inconnues sur Android TV ?', 'Paramètres > Applications > Sécurité et restrictions > Sources inconnues, puis autorisez uniquement l’application qui installe le fichier (par exemple Downloader).' ),
		array( 'Quelle box Android choisir pour l’IPTV ?', 'Privilégiez un modèle certifié Android TV / Google TV avec au moins 2 Go de RAM, le Wi-Fi 5 GHz et idéalement un port Ethernet (Nvidia Shield, Xiaomi Mi Box, Chromecast avec Google TV).' ),
	),
	'iptv-apple-tv-iphone-ipad' => array(
		array( 'IPTV Smarters Pro existe-t-il sur iPhone ?', 'Sur l’App Store, l’application se présente sous le nom « Smarters Player Lite ». Elle accepte les connexions Xtream Codes et M3U.' ),
		array( 'Peut-on regarder l’IPTV sur Apple TV sans iPhone ?', 'Oui, installez directement un lecteur depuis l’App Store de l’Apple TV (GSE Smart IPTV, iPlayTV) et saisissez vos identifiants à l’écran.' ),
		array( 'AirPlay réduit-il la qualité d’image ?', 'AirPlay peut ajouter un léger délai et dépend de la qualité du Wi-Fi. Pour la meilleure image, préférez une application installée directement sur l’Apple TV.' ),
	),
	'iptv-pc-vlc-guide' => array(
		array( 'VLC est-il gratuit pour l’IPTV ?', 'Oui, VLC est un logiciel libre et entièrement gratuit, sans publicité. Il suffit d’y ouvrir votre lien M3U.' ),
		array( 'Peut-on enregistrer une émission avec VLC ?', 'VLC dispose d’une fonction d’enregistrement (bouton rouge dans Vue > Contrôles avancés) pour un usage privé. Respectez les droits des programmes enregistrés.' ),
		array( 'Pourquoi VLC saccade avec l’IPTV ?', 'Le cache réseau par défaut est faible. Augmentez la mise en cache réseau à 3000 ms dans les préférences avancées et privilégiez une connexion filaire.' ),
	),
	'iptv-qui-coupe-buffering-solutions' => array(
		array( 'Un VPN réduit-il les coupures IPTV ?', 'Parfois, si votre fournisseur d’accès ralentit certains flux. Mais un VPN ajoute aussi de la latence : testez avec et sans, et gardez la configuration la plus stable.' ),
		array( 'Pourquoi l’IPTV coupe seulement le soir ?', 'Entre 19 h et 23 h, les réseaux domestiques et ceux des fournisseurs d’accès sont les plus chargés. Le passage en Ethernet et la réduction des usages simultanés à la maison améliorent nettement la situation.' ),
		array( 'Le Wi-Fi suffit-il pour la 4K ?', 'Un Wi-Fi 5 GHz ou Wi-Fi 6 proche de la box peut suffire. Dès qu’il y a des murs épais ou de la distance, un câble Ethernet ou un adaptateur CPL est fortement recommandé.' ),
	),
	'abonnement-iptv-multi-ecrans' => array(
		array( 'Peut-on partager un abonnement IPTV avec un autre foyer ?', 'Les abonnements sont personnels et réservés à un foyer. Le partage à l’extérieur est généralement interdit par les conditions générales et peut entraîner la suspension du compte.' ),
		array( 'Combien d’écrans pour une famille de 4 ?', 'Deux connexions simultanées couvrent la plupart des familles (salon + chambre). Passez à trois ou quatre si chacun regarde régulièrement son propre programme.' ),
		array( 'Peut-on ajouter un écran en cours d’abonnement ?', 'C’est souvent possible moyennant un complément de prix. Contactez le support pour connaître les conditions de passage à une formule multi-écrans.' ),
	),
	'test-iptv-comment-evaluer' => array(
		array( 'Le test IPTV est-il gratuit ?', 'De nombreux fournisseurs proposent un test gratuit de quelques heures. Il est généralement limité à un appareil et peut nécessiter un contact préalable avec le support.' ),
		array( 'Combien de temps dure un test IPTV ?', 'Le plus souvent entre 12 et 24 heures. C’est suffisant si vous testez en soirée, aux heures de forte affluence.' ),
		array( 'Le test reflète-t-il le service réel ?', 'Il doit utiliser les mêmes serveurs que l’abonnement payant. Méfiez-vous si le fournisseur refuse tout test ou si la qualité baisse brutalement après l’achat.' ),
	),
	'iptv-legal-france' => array(
		array( 'Risque-t-on une amende en utilisant l’IPTV ?', 'Utiliser un service IPTV légal ne présente aucun risque. Accéder sciemment à des contenus diffusés sans autorisation expose en revanche à des poursuites pour contrefaçon ou recel.' ),
		array( 'IPTV Smarters Pro est-il légal ?', 'Oui, c’est un lecteur vidéo neutre disponible sur les stores officiels. Sa légalité ne dépend pas de l’application mais des contenus que vous y lisez.' ),
		array( 'Que fait l’Arcom contre l’IPTV illégale ?', 'L’Arcom peut demander le blocage des sites et services qui diffusent des contenus sans autorisation, mène des campagnes de sensibilisation et référence les offres légales.' ),
	),
	'epg-guide-tv-iptv' => array(
		array( 'Que signifie EPG ?', 'EPG signifie Electronic Program Guide, c’est-à-dire guide électronique des programmes. Il affiche l’émission en cours et les suivantes pour chaque chaîne.' ),
		array( 'Qu’est-ce qu’une URL EPG XMLTV ?', 'C’est un fichier au format XML contenant les grilles de programmes. Avec une playlist M3U, il faut parfois l’ajouter manuellement dans l’application pour afficher le guide.' ),
		array( 'À quelle fréquence l’EPG se met-il à jour ?', 'En général toutes les 24 heures. Vous pouvez forcer la mise à jour depuis les paramètres de l’application si le guide semble incomplet.' ),
	),
	'abonnement-iptv-12-mois-ou-1-mois' => array(
		array( 'L’abonnement IPTV se renouvelle-t-il automatiquement ?', 'Chez nous, non : vous payez une fois pour la durée choisie. À l’échéance, vous décidez librement de renouveler.' ),
		array( 'Peut-on passer d’un abonnement 1 mois à 12 mois ?', 'Oui, au moment du renouvellement, choisissez simplement la formule 12 mois. Vos identifiants et vos réglages restent identiques.' ),
		array( 'Un abonnement 24 mois est-il risqué ?', 'Le risque dépend surtout du sérieux du fournisseur. Ne choisissez une longue durée qu’après avoir testé le service et vérifié les conditions de remboursement.' ),
	),
	'box-mag-formuler-iptv' => array(
		array( 'Quelle box MAG choisir en 2026 ?', 'Les modèles récents comme la MAG 524 (4K, HEVC) sont les plus adaptés. Évitez les modèles anciens qui ne gèrent pas la 4K ni le codec H.265.' ),
		array( 'Peut-on utiliser une box MAG avec IPTV Smarters ?', 'Non, les box MAG utilisent leur propre portail. Pour IPTV Smarters ou TiviMate, il faut un boîtier sous Android TV.' ),
		array( 'Formuler ou MAG : lequel choisir ?', 'Formuler (application MyTVOnline) offre plus de souplesse, avec la prise en charge d’Xtream Codes. MAG reste très simple et fiable pour un usage purement IPTV.' ),
	),
	'controle-parental-iptv' => array(
		array( 'J’ai oublié mon code parental IPTV Smarters, que faire ?', 'Le code est stocké sur l’appareil : la solution la plus simple est de vider les données de l’application puis de reconnecter votre compte et de définir un nouveau code.' ),
		array( 'Peut-on masquer complètement une catégorie ?', 'Oui, IPTV Smarters Pro et TiviMate permettent de masquer des catégories entières. Elles disparaissent alors de la liste sans demander de code.' ),
		array( 'Le contrôle parental est-il synchronisé entre appareils ?', 'Non, il se configure appareil par appareil. Pensez à l’activer sur chaque écran utilisé par les enfants.' ),
	),
);
