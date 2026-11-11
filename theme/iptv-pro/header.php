<?php
/**
 * En-tête du site.
 *
 * @package IPTV_Pro
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="<?php echo esc_url( IPTVP_URI . '/assets/img/favicon.svg' ); ?>" type="image/svg+xml">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'iptv-pro' ); ?></a>

<div class="topbar">
	<div class="container topbar__inner">
		<p><?php echo iptvp_icon( 'bolt', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Activation immédiate · Sans engagement · Support 7j/7', 'iptv-pro' ); ?></p>
		<a href="<?php echo esc_url( iptvp_trial_url() ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Demander un test', 'iptv-pro' ); ?> →</a>
	</div>
</div>

<header class="site-header" id="top">
	<div class="container site-header__inner">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo esc_url( IPTVP_URI . '/assets/img/logo.svg' ); ?>" width="36" height="36" alt="">
					<span><?php echo esc_html( iptvp_opt( 'brand' ) ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<button class="nav-toggle" aria-controls="primary-nav" aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'Ouvrir le menu', 'iptv-pro' ); ?></span>
			<?php echo iptvp_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</button>

		<nav class="nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'iptv-pro' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav__list',
				'depth'          => 2,
				'fallback_cb'    => 'iptvp_menu_fallback',
			) );
			?>
			<a class="btn btn--primary btn--sm nav__cta" href="<?php echo esc_url( home_url( '/#tarifs' ) ); ?>"><?php esc_html_e( 'Commander', 'iptv-pro' ); ?></a>
		</nav>
	</div>
</header>

<main id="contenu" class="site-main">
