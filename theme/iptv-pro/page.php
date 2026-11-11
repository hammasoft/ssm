<?php
/**
 * Page standard + rendu automatique des guides d'installation (meta _iptvp_guide).
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$guide_slug = iptvp_current_guide_slug();
	$guide      = $guide_slug ? iptvp_guides()[ $guide_slug ] : null;
	?>
	<header class="page-hero">
		<div class="container container--narrow">
			<?php iptvp_breadcrumbs(); ?>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<?php if ( $guide ) : ?>
				<p class="page-hero__meta">
					<?php echo iptvp_icon( 'guide', 16 ); // phpcs:ignore ?>
					<?php
					/* translators: 1: app, 2: date */
					printf( esc_html__( 'Application : %1$s · Mis à jour le %2$s', 'iptv-pro' ), esc_html( $guide['app'] ), esc_html( get_the_modified_date() ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<div class="section section--tight">
		<article class="container container--narrow entry-content">
			<?php the_content(); ?>

			<?php if ( $guide ) : ?>
				<h2><?php esc_html_e( 'Installation étape par étape', 'iptv-pro' ); ?></h2>
				<ol class="howto">
					<?php foreach ( $guide['steps'] as $i => $step ) : ?>
						<li id="etape-<?php echo (int) $i + 1; ?>"><?php echo esc_html( $step ); ?></li>
					<?php endforeach; ?>
				</ol>
				<div class="notice">
					<?php echo iptvp_icon( 'support', 22 ); // phpcs:ignore ?>
					<p><?php esc_html_e( 'Un souci pendant l’installation ? Envoyez-nous une capture d’écran sur WhatsApp : nous vous guidons en direct.', 'iptv-pro' ); ?>
					<a href="<?php echo esc_url( iptvp_whatsapp_url( sprintf( 'Aide installation : %s', $guide['title'] ) ) ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Obtenir de l’aide', 'iptv-pro' ); ?> →</a></p>
				</div>

				<h2><?php esc_html_e( 'Autres guides d’installation', 'iptv-pro' ); ?></h2>
				<ul class="link-grid">
					<?php foreach ( iptvp_guides() as $slug => $other ) : ?>
						<?php if ( $slug !== $guide_slug ) : ?>
							<li><a href="<?php echo esc_url( home_url( '/installation-iptv/' . $slug . '/' ) ); ?>"><?php echo esc_html( $other['title'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php
			wp_link_pages();
			?>
		</article>
	</div>
	<?php
	if ( $guide ) {
		iptvp_section( 'cta' );
	}
endwhile;

get_footer();
