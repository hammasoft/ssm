<?php
/**
 * Page de réglages : Offre marketing.
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	register_setting( 'iob', 'iob_settings', array( 'sanitize_callback' => 'iob_sanitize' ) );
} );

add_action( 'admin_menu', function () {
	add_menu_page( __( 'Offre marketing', 'iptv-offer-booster' ), __( 'Offre marketing', 'iptv-offer-booster' ), 'manage_options', 'iob', 'iob_admin_page', 'dashicons-megaphone', 26 );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_iob' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'iob-admin', IOB_URL . 'assets/admin.css', array(), IOB_VERSION );
	wp_enqueue_script( 'iob-admin', IOB_URL . 'assets/admin.js', array( 'jquery' ), IOB_VERSION, true );
	wp_localize_script( 'iob-admin', 'IOB_PRESETS', iob_presets() );
} );

function iob_admin_page() {
	$s      = iob_settings();
	$stats  = iob_stats();
	$ctr    = $stats['views'] ? round( $stats['clicks'] / $stats['views'] * 100, 1 ) : 0;
	$name   = 'iob_settings';
	$status = ! $s['enabled'] ? array( 'off', __( 'Désactivée', 'iptv-offer-booster' ) ) : ( iob_is_expired( $s ) ? array( 'expired', __( 'Expirée', 'iptv-offer-booster' ) ) : array( 'on', __( 'En ligne', 'iptv-offer-booster' ) ) );
	?>
	<div class="wrap iob-admin">
		<h1><?php esc_html_e( 'Offre marketing', 'iptv-offer-booster' ); ?> <span class="iob-status iob-status--<?php echo esc_attr( $status[0] ); ?>"><?php echo esc_html( $status[1] ); ?></span></h1>

		<div class="iob-stats">
			<div><strong><?php echo esc_html( number_format_i18n( $stats['views'] ) ); ?></strong><span><?php esc_html_e( 'Vues', 'iptv-offer-booster' ); ?></span></div>
			<div><strong><?php echo esc_html( number_format_i18n( $stats['clicks'] ) ); ?></strong><span><?php esc_html_e( 'Clics', 'iptv-offer-booster' ); ?></span></div>
			<div><strong><?php echo esc_html( number_format_i18n( $ctr, 1 ) ); ?> %</strong><span><?php esc_html_e( 'Taux de clic', 'iptv-offer-booster' ); ?></span></div>
			<p class="description">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Depuis le %s (remis à zéro quand le titre ou le code promo change). Visiteurs uniques par heure, administrateurs exclus.', 'iptv-offer-booster' ), esc_html( wp_date( get_option( 'date_format' ), (int) $stats['since'] ) ) );
				?>
			</p>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'iob' ); ?>

			<div class="iob-section">
				<h2><span>1</span> <?php esc_html_e( 'Activer l’offre', 'iptv-offer-booster' ); ?></h2>
				<label class="iob-switch"><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $s['enabled'] ); ?>> <span><?php esc_html_e( 'Afficher l’offre sur le site', 'iptv-offer-booster' ); ?></span></label>
			</div>

			<div class="iob-section">
				<h2><span>2</span> <?php esc_html_e( 'Partir d’une offre prête à l’emploi (facultatif)', 'iptv-offer-booster' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Un clic préremplit le contenu, le modèle et les couleurs. Vous pouvez ensuite tout modifier.', 'iptv-offer-booster' ); ?></p>
				<div class="iob-presets">
					<?php foreach ( iob_presets() as $key => $preset ) : ?>
						<button type="button" class="button iob-preset" data-preset="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $preset['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="iob-section">
				<h2><span>3</span> <?php esc_html_e( 'Choisir le modèle d’affichage', 'iptv-offer-booster' ); ?></h2>
				<div class="iob-templates">
					<?php foreach ( iob_templates() as $key => list( $label, $desc ) ) : ?>
						<label class="iob-tpl">
							<input type="radio" name="<?php echo esc_attr( $name ); ?>[template]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['template'], $key ); ?>>
							<span class="iob-tpl__thumb iob-tpl__thumb--<?php echo esc_attr( $key ); ?>" aria-hidden="true"><i></i><b></b></span>
							<strong><?php echo esc_html( $label ); ?></strong>
							<small><?php echo esc_html( $desc ); ?></small>
							<a href="<?php echo esc_url( add_query_arg( array( 'iob_preview' => 1, 'iob_template' => $key ), home_url( '/' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Aperçu ↗', 'iptv-offer-booster' ); ?></a>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'L’aperçu utilise les réglages enregistrés : enregistrez d’abord vos modifications.', 'iptv-offer-booster' ); ?></p>
			</div>

			<div class="iob-section">
				<h2><span>4</span> <?php esc_html_e( 'Contenu de l’offre', 'iptv-offer-booster' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					iob_field( 'badge', __( 'Badge', 'iptv-offer-booster' ), $s, 'text', __( 'Ex. : Offre spéciale, Nouveau client, Vente flash', 'iptv-offer-booster' ) );
					iob_field( 'title', __( 'Titre accrocheur', 'iptv-offer-booster' ), $s, 'text', __( 'Un bénéfice clair et chiffré : « -15 % sur votre premier abonnement »', 'iptv-offer-booster' ) );
					iob_field( 'text', __( 'Texte', 'iptv-offer-booster' ), $s, 'textarea' );
					iob_field( 'old_price', __( 'Prix barré', 'iptv-offer-booster' ), $s, 'text', __( 'Doit être un prix réellement pratiqué auparavant (prix de référence des 30 derniers jours).', 'iptv-offer-booster' ) );
					iob_field( 'new_price', __( 'Prix promotionnel', 'iptv-offer-booster' ), $s );
					iob_field( 'price_note', __( 'Précision prix', 'iptv-offer-booster' ), $s, 'text', __( 'Ex. : pour 12 mois, soit 4,99 €/mois', 'iptv-offer-booster' ) );
					iob_field( 'coupon', __( 'Code promo', 'iptv-offer-booster' ), $s, 'text', __( 'Bouton « Copier » automatique. Laissez vide si aucun code.', 'iptv-offer-booster' ) );
					iob_field( 'button', __( 'Texte du bouton', 'iptv-offer-booster' ), $s );
					iob_field( 'url', __( 'Lien du bouton', 'iptv-offer-booster' ), $s, 'url', __( 'Ex. : /#tarifs, page de paiement ou lien WhatsApp (https://wa.me/…)', 'iptv-offer-booster' ) );
					?>
					<tr><th scope="row"><?php esc_html_e( 'Ouvrir dans un nouvel onglet', 'iptv-offer-booster' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[new_tab]" value="1" <?php checked( $s['new_tab'] ); ?>> <?php esc_html_e( 'Oui (conseillé pour WhatsApp)', 'iptv-offer-booster' ); ?></label></td></tr>
					<tr><th scope="row"><label for="iob-image"><?php esc_html_e( 'Image (facultative)', 'iptv-offer-booster' ); ?></label></th><td>
						<input type="url" id="iob-image" class="regular-text" name="<?php echo esc_attr( $name ); ?>[image]" value="<?php echo esc_attr( $s['image'] ); ?>">
						<button type="button" class="button iob-media"><?php esc_html_e( 'Choisir', 'iptv-offer-booster' ); ?></button>
						<p class="description"><?php esc_html_e( 'Affichée dans la popup et la bannière. Format conseillé : 800×400, WebP.', 'iptv-offer-booster' ); ?></p>
					</td></tr>
					<?php iob_field( 'legal', __( 'Conditions (petites lignes)', 'iptv-offer-booster' ), $s, 'textarea', __( 'Ex. : Offre valable une fois par client, non cumulable.', 'iptv-offer-booster' ) ); ?>
				</table>
			</div>

			<div class="iob-section">
				<h2><span>5</span> <?php esc_html_e( 'Durée de l’offre', 'iptv-offer-booster' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><label for="iob-end"><?php esc_html_e( 'Date de fin', 'iptv-offer-booster' ); ?></label></th><td>
						<input type="datetime-local" id="iob-end" name="<?php echo esc_attr( $name ); ?>[end_date]" value="<?php echo esc_attr( $s['end_date'] ); ?>">
						<p class="description"><?php printf( esc_html__( 'Fuseau horaire du site : %s. L’offre disparaît automatiquement à cette date. Laisser vide = offre permanente.', 'iptv-offer-booster' ), esc_html( wp_timezone_string() ) ); ?></p>
					</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Compte à rebours', 'iptv-offer-booster' ); ?></th><td>
						<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[countdown]" value="1" <?php checked( $s['countdown'] ); ?>> <?php esc_html_e( 'Afficher le temps restant jusqu’à la date de fin', 'iptv-offer-booster' ); ?></label>
						<p class="description"><?php esc_html_e( 'Uniquement avec une vraie date de fin : un compte à rebours qui repart à zéro est une pratique commerciale trompeuse interdite.', 'iptv-offer-booster' ); ?></p>
					</td></tr>
				</table>
			</div>

			<div class="iob-section">
				<h2><span>6</span> <?php esc_html_e( 'Où et quand afficher', 'iptv-offer-booster' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><?php esc_html_e( 'Pages', 'iptv-offer-booster' ); ?></th><td>
						<?php iob_select( 'pages', $s, array( 'home' => __( 'Page d’accueil uniquement', 'iptv-offer-booster' ), 'all' => __( 'Toutes les pages', 'iptv-offer-booster' ), 'all_but_home' => __( 'Toutes sauf l’accueil', 'iptv-offer-booster' ) ) ); ?>
						<p class="description"><?php esc_html_e( 'Astuce : insérez aussi l’offre dans n’importe quel article avec le shortcode [iptv_offre].', 'iptv-offer-booster' ); ?></p>
					</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Appareils', 'iptv-offer-booster' ); ?></th><td>
						<?php iob_select( 'devices', $s, array( 'all' => __( 'Ordinateur et mobile', 'iptv-offer-booster' ), 'desktop' => __( 'Ordinateur uniquement', 'iptv-offer-booster' ), 'mobile' => __( 'Mobile uniquement', 'iptv-offer-booster' ) ) ); ?>
					</td></tr>
					<tr><th scope="row"><label for="iob-delay"><?php esc_html_e( 'Délai d’apparition', 'iptv-offer-booster' ); ?></label></th><td>
						<input type="number" id="iob-delay" min="0" max="120" name="<?php echo esc_attr( $name ); ?>[delay]" value="<?php echo (int) $s['delay']; ?>" class="small-text"> <?php esc_html_e( 'secondes', 'iptv-offer-booster' ); ?>
						<?php esc_html_e( 'ou dès', 'iptv-offer-booster' ); ?>
						<input type="number" min="0" max="100" name="<?php echo esc_attr( $name ); ?>[scroll]" value="<?php echo (int) $s['scroll']; ?>" class="small-text"> <?php esc_html_e( '% de défilement (popup et slide-in)', 'iptv-offer-booster' ); ?>
						<p class="description"><?php esc_html_e( 'Google pénalise les popups qui masquent le contenu dès l’arrivée sur mobile : gardez au moins 5 secondes ou 30 % de défilement.', 'iptv-offer-booster' ); ?></p>
					</td></tr>
					<tr><th scope="row"><label for="iob-freq"><?php esc_html_e( 'Après fermeture, ne plus afficher pendant', 'iptv-offer-booster' ); ?></label></th><td>
						<input type="number" id="iob-freq" min="0" max="365" name="<?php echo esc_attr( $name ); ?>[frequency]" value="<?php echo (int) $s['frequency']; ?>" class="small-text"> <?php esc_html_e( 'jours (0 = à chaque visite)', 'iptv-offer-booster' ); ?>
					</td></tr>
				</table>
			</div>

			<div class="iob-section">
				<h2><span>7</span> <?php esc_html_e( 'Couleurs', 'iptv-offer-booster' ); ?></h2>
				<div class="iob-colors">
					<?php foreach ( iob_styles() as $key => list( $label, $c1, $c2, $fg ) ) : ?>
						<label class="iob-color">
							<input type="radio" name="<?php echo esc_attr( $name ); ?>[style]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['style'], $key ); ?>>
							<span style="background:linear-gradient(135deg,<?php echo esc_attr( $c1 ); ?>,<?php echo esc_attr( $c2 ); ?>);color:<?php echo esc_attr( $fg ); ?>">Aa</span>
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<?php submit_button( __( 'Enregistrer l’offre', 'iptv-offer-booster' ) ); ?>
		</form>
	</div>
	<?php
}

/** Champ texte / url / textarea. */
function iob_field( $key, $label, array $s, $type = 'text', $help = '' ) {
	$id   = 'iob-' . $key;
	$name = 'iob_settings[' . $key . ']';
	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="3" class="large-text">' . esc_textarea( $s[ $key ] ) . '</textarea>';
	} else {
		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $s[ $key ] ) . '" class="regular-text">';
	}
	if ( $help ) {
		echo '<p class="description">' . esc_html( $help ) . '</p>';
	}
	echo '</td></tr>';
}

/** Liste déroulante. */
function iob_select( $key, array $s, array $choices ) {
	echo '<select name="iob_settings[' . esc_attr( $key ) . ']">';
	foreach ( $choices as $v => $l ) {
		echo '<option value="' . esc_attr( $v ) . '" ' . selected( $s[ $key ], $v, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select>';
}
