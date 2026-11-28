<?php
defined( 'ABSPATH' ) || exit;

/** Preload the two display-critical fonts. */
add_action( 'wp_head', function () {
	foreach ( array( 'bebas-neue-latin-400-normal.woff2', 'dm-sans-latin-wght-normal.woff2' ) as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( PSYCHART_URI . '/assets/fonts/' . $f ) );
	}
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'psychart-main', PSYCHART_URI . '/assets/css/main.css', array(), PSYCHART_VERSION );
	wp_enqueue_script( 'psychart-shell', PSYCHART_URI . '/assets/js/shell.js', array(), PSYCHART_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	wp_localize_script( 'psychart-shell', 'PSYCHART', array(
		'ajax'     => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'psychart' ),
		'shopUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'i18n'     => array(
			'noResults' => __( 'No products found', 'psychart' ),
			'typeMore'  => __( 'Start typing to search products', 'psychart' ),
			'error'     => __( 'Something went wrong. Please try again.', 'psychart' ),
			'joined'    => __( 'You are on the list.', 'psychart' ),
		),
	) );
} );

/** Drop the WooCommerce default layout CSS that fights this theme; keep the rest. */
add_filter( 'woocommerce_enqueue_styles', function ( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );
	return $styles;
} );

// The theme provides its own bag drawer, so the cart-fragments script is not needed.
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_script( 'wc-cart-fragments' );
}, 100 );
