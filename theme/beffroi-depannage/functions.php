<?php
/**
 * Beffroi Dépannage — bootstrap du thème.
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

define( 'BD_VERSION', '1.0.0' );
define( 'BD_DIR', get_template_directory() );
define( 'BD_URI', get_template_directory_uri() );

foreach ( array( 'business', 'services', 'helpers', 'setup', 'customizer', 'schema', 'seo', 'seeder' ) as $bd_module ) {
	require_once BD_DIR . '/inc/' . $bd_module . '.php';
}
unset( $bd_module );
