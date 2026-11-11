<?php
/**
 * En-tête : bandeau urgence + logo + navigation + numéro cliquable.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b2545">
<meta name="format-detection" content="telephone=yes">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu">Aller au contenu</a>

<div class="topbar">
	<div class="container topbar__inner">
		<p class="topbar__live"><span class="pulse" aria-hidden="true"></span> Plombiers disponibles maintenant · 24h/24 · 7j/7</p>
		<p class="topbar__zone">Nord (59) &amp; Pas-de-Calais (62) · Intervention en <?php echo esc_html( bd_business( 'delay' ) ); ?></p>
	</div>
</div>

<header class="site-header">
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( (int) get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand__logo', 'alt' => bd_business( 'name' ) ) ); ?>
			<?php else : ?>
				<svg class="brand__mark" viewBox="0 0 40 48" width="34" height="40" aria-hidden="true"><path fill="#ff6b1a" d="M20 0l6 8h-4v6h8v34H10V14h8V8h-4z"/><path fill="#fff" d="M17 22h6v8h-6zM15 36h10v12H15z"/></svg>
				<span class="brand__text"><strong>Beffroi</strong> Dépannage<small>Plomberie · Chauffage</small></span>
			<?php endif; ?>
		</a>

		<button class="nav-toggle" aria-expanded="false" aria-controls="nav-principale"><span class="sr-only">Menu</span><span aria-hidden="true">☰</span></button>

		<nav id="nav-principale" class="site-nav" aria-label="Navigation principale">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => 'bd_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<?php echo bd_call_button( 'header', bd_business( 'phone_display' ), 'btn btn--call btn--header' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</header>

<main id="contenu" class="site-main">
