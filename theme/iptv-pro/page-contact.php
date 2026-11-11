<?php
/**
 * Page Contact (slug : contact). Ajoutez un formulaire (Contact Form 7, WPForms…) via l'éditeur si besoin.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<header class="page-hero">
		<div class="container container--narrow">
			<?php iptvp_breadcrumbs(); ?>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
		</div>
	</header>
	<section class="section section--tight">
		<div class="container container--narrow">
			<div class="entry-content"><?php the_content(); ?></div>
			<div class="contact__grid">
				<a class="contact-card" href="<?php echo esc_url( iptvp_whatsapp_url( __( 'Bonjour, ', 'iptv-pro' ) ) ); ?>" target="_blank" rel="noopener nofollow">
					<?php echo iptvp_icon( 'whatsapp', 32 ); // phpcs:ignore ?>
					<strong>WhatsApp</strong>
					<span><?php esc_html_e( 'Réponse rapide 7j/7', 'iptv-pro' ); ?></span>
				</a>
				<?php if ( iptvp_opt( 'email' ) ) : ?>
					<a class="contact-card" href="mailto:<?php echo esc_attr( antispambot( iptvp_opt( 'email' ) ) ); ?>">
						<?php echo iptvp_icon( 'mail', 32 ); // phpcs:ignore ?>
						<strong><?php esc_html_e( 'E-mail', 'iptv-pro' ); ?></strong>
						<span><?php echo esc_html( antispambot( iptvp_opt( 'email' ) ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( iptvp_opt( 'telegram' ) ) : ?>
					<a class="contact-card" href="<?php echo esc_url( iptvp_opt( 'telegram' ) ); ?>" target="_blank" rel="noopener">
						<?php echo iptvp_icon( 'support', 32 ); // phpcs:ignore ?>
						<strong>Telegram</strong>
						<span><?php esc_html_e( 'Canal & support', 'iptv-pro' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

iptvp_section( 'faq', array( 'limit' => 4 ) );

get_footer();
