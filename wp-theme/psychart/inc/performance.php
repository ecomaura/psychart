<?php
/**
 * Phase 6: front-end weight. Removes core/emoji assets this theme does not use.
 */
defined( 'ABSPATH' ) || exit;

// Emoji script/styles and the oEmbed discovery/script: not used by the theme.
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
} );

/**
 * Block-editor front-end CSS is only needed where block content is output.
 * Home, shop, product and 404 templates are plain PHP, so skip it there.
 * Pages and posts keep it (content written with blocks needs it).
 */
add_action( 'wp_enqueue_scripts', function () {
	$plain = is_front_page() || is_404() || ( function_exists( 'is_shop' ) && ( is_shop() || is_product() || is_product_taxonomy() ) );
	if ( ! $plain || ( is_front_page() && is_page() && psychart_has_editor_content() ) ) {
		return;
	}
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'wc-blocks-style' );
}, 100 );
