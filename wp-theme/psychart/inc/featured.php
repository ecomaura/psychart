<?php
/**
 * Product links for the places that should always lead to products:
 * home cards (default products), menu/footer category links, "latest pieces" on empty cart and 404.
 */
defined( 'ABSPATH' ) || exit;

/** Demo SKU for each home "The New Uniform" card (used until a product is picked in the Customizer). */
function psychart_story_default_sku( $i ) {
	$map = array( 1 => 'PSY-DTJ', 2 => 'PSY-EDS', 3 => 'PSY-WFD' );
	return isset( $map[ $i ] ) ? $map[ $i ] : '';
}

/** Product ID for a home card: the Customizer pick, else the demo product with the matching SKU, else 0. */
function psychart_story_product_id( $i ) {
	$pid = absint( psychart_mod( "story_{$i}_product" ) );
	if ( $pid ) {
		return $pid;
	}
	$sku = psychart_story_default_sku( $i );
	return ( $sku && function_exists( 'wc_get_product_id_by_sku' ) ) ? absint( wc_get_product_id_by_sku( $sku ) ) : 0;
}

/** Product category links (label => url) for menus. Skips empty categories and Uncategorized. */
function psychart_category_links( $limit = 6 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => $limit,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'name',
	) );
	$links = array();
	if ( is_wp_error( $terms ) ) {
		return $links;
	}
	foreach ( $terms as $t ) {
		$url = get_term_link( $t );
		if ( ! is_wp_error( $url ) ) {
			$links[ $t->name ] = $url;
		}
	}
	return $links;
}

/** A row of the newest products (image + name, each linking to the product). Prints nothing without products. */
function psychart_product_picks( $limit = 3, $heading = '' ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return;
	}
	$products = wc_get_products( array(
		'limit'      => $limit,
		'status'     => 'publish',
		'visibility' => 'catalog',
		'orderby'    => 'date',
		'order'      => 'DESC',
	) );
	if ( ! $products ) {
		return;
	}
	echo '<div class="psy-picks">';
	if ( $heading ) {
		echo '<p class="psy-label psy-label--strong">' . esc_html( $heading ) . '</p>';
	}
	echo '<ul class="psy-picks__list">';
	foreach ( $products as $p ) {
		echo '<li><a href="' . esc_url( $p->get_permalink() ) . '">';
		echo '<span class="psy-picks__img">' . $p->get_image( 'woocommerce_thumbnail' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="psy-picks__name">' . esc_html( $p->get_name() ) . '</span>';
		echo '</a></li>';
	}
	echo '</ul></div>';
}
