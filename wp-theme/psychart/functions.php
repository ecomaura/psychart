<?php
/**
 * PSYCHART.CO theme bootstrap.
 */
defined( 'ABSPATH' ) || exit;

define( 'PSYCHART_VERSION', '0.3.0' );
define( 'PSYCHART_DIR', get_template_directory() );
define( 'PSYCHART_URI', get_template_directory_uri() );

require_once PSYCHART_DIR . '/inc/setup.php';
require_once PSYCHART_DIR . '/inc/assets.php';
require_once PSYCHART_DIR . '/inc/cart.php';
require_once PSYCHART_DIR . '/inc/search.php';
require_once PSYCHART_DIR . '/inc/newsletter.php';
require_once PSYCHART_DIR . '/inc/home.php';
require_once PSYCHART_DIR . '/inc/customizer.php';
require_once PSYCHART_DIR . '/inc/shop.php';
