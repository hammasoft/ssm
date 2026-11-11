<?php
/**
 * Bloc éditorial SEO (contenu riche en sémantique) — affiché en bas de l'accueil.
 * Le contenu saisi dans l'éditeur de la page d'accueil remplace ce texte par défaut.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
$custom = is_front_page() && is_page() ? trim( (string) get_post_field( 'post_content', get_queried_object_id() ) ) : '';
?>
<section class="section seo-text">
	<div class="container container--narrow entry-content">
		<?php if ( $custom ) : ?>
			<?php echo apply_filters( 'the_content', $custom ); // phpcs:ignore ?>
		<?php else : ?>
			<h2><?php esc_html_e( 'Abonnement IPTV : la télévision nouvelle génération', 'iptv-pro' ); ?></h2>
			<p><?php esc_html_e( 'Un abonnement IPTV remplace avantageusement le câble, le satellite et les décodeurs traditionnels. Grâce à votre connexion Internet, vous accédez à la télévision en direct, au replay et à un vaste catalogue de films et séries à la demande, sur l’écran de votre choix, à la maison comme en déplacement.', 'iptv-pro' ); ?></p>
			<h3><?php esc_html_e( 'Pourquoi choisir IPTV Smarters Pro ?', 'iptv-pro' ); ?></h3>
			<p><?php esc_html_e( 'IPTV Smarters Pro est un lecteur gratuit et intuitif qui organise vos chaînes par catégories, affiche le guide des programmes (EPG), gère les favoris, le contrôle parental et la reprise de lecture. Notre service est optimisé pour cette application, mais reste compatible avec tous les lecteurs du marché : IBO Player, TiviMate, GSE Smart IPTV, VLC, Smart IPTV ou les box MAG.', 'iptv-pro' ); ?></p>
			<h3><?php esc_html_e( 'Une qualité d’image 4K stable', 'iptv-pro' ); ?></h3>
			<p><?php esc_html_e( 'Nos serveurs sont dimensionnés pour absorber les pics d’audience lors des grands événements. La technologie de diffusion adaptative ajuste automatiquement la qualité à votre débit afin d’éviter les coupures, du SD jusqu’à la 4K Ultra HD.', 'iptv-pro' ); ?></p>
			<h3><?php esc_html_e( 'Sans engagement, sans matériel', 'iptv-pro' ); ?></h3>
			<p><?php esc_html_e( 'Vous payez une seule fois pour la durée de votre choix (1, 3, 6, 12 ou 24 mois). Aucun matériel à acheter, aucune installation technique : l’application et vos identifiants suffisent. Consultez nos guides d’installation ou contactez notre support pour être accompagné pas à pas.', 'iptv-pro' ); ?></p>
		<?php endif; ?>
	</div>
</section>
