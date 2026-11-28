<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'psychart', PSYCHART_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// WooCommerce. Gallery lightbox/zoom/slider are off: the product page (Phase 4) ships its own.
	add_theme_support( 'woocommerce' );

	register_nav_menus( array(
		'primary'        => __( 'Menu drawer', 'psychart' ),
		'footer_shop'    => __( 'Footer: Shop', 'psychart' ),
		'footer_help'    => __( 'Footer: Help', 'psychart' ),
		'footer_company' => __( 'Footer: Company', 'psychart' ),
	) );
} );

/**
 * Default links used until menus are assigned in Appearance > Menus.
 * Each item: label => url, with 'search' as a special value that opens the search overlay.
 */
function psychart_default_links( $location ) {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$acct = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );

	$defaults = array(
		'primary'        => array(
			__( 'Shop All', 'psychart' ) => $shop,
			__( 'Sign In', 'psychart' )  => $acct,
			__( 'Search', 'psychart' )   => 'search',
		),
		'footer_shop'    => array(
			__( 'All Products', 'psychart' ) => $shop,
			__( 'New Arrivals', 'psychart' ) => add_query_arg( 'orderby', 'date', $shop ),
			__( 'Collections', 'psychart' )  => $shop,
		),
		'footer_help'    => array(
			__( 'FAQ', 'psychart' )                 => home_url( '/faq/' ),
			__( 'Shipping & Returns', 'psychart' )  => home_url( '/shipping-returns/' ),
			__( 'Contact Us', 'psychart' )          => home_url( '/contact/' ),
		),
		'footer_company' => array(
			__( 'About', 'psychart' ) => home_url( '/about/' ),
		),
	);
	return isset( $defaults[ $location ] ) ? $defaults[ $location ] : array();
}

/**
 * Print a link list for a menu location: the assigned WP menu if any, otherwise the defaults.
 */
function psychart_render_links( $location, $class = '' ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => $class,
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
		return;
	}
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( psychart_default_links( $location ) as $label => $url ) {
		if ( 'search' === $url ) {
			echo '<li><button type="button" class="psy-link-btn" data-psy-open="search">' . esc_html( $label ) . '</button></li>';
		} else {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}
	echo '</ul>';
}

/** Inline SVG icons (stroke style, 1.5px) used across the shell. */
function psychart_icon( $name ) {
	$p = array(
		'menu'   => '<path d="M3 7h18M3 12h18M3 17h18"/>',
		'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',
		'bag'    => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
		'close'  => '<path d="M6 6l12 12M18 6 6 18"/>',
		'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'plus'   => '<path d="M12 5v14M5 12h14"/>',
		'minus'  => '<path d="M5 12h14"/>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="psy-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}
