<?php
/** Shop / archive / search: sorting, filters and URL helpers. */
defined( 'ABSPATH' ) || exit;

/* ---------- Sorting: the four options from the design ---------- */
add_filter( 'woocommerce_catalog_orderby', function () {
	$opts = array(
		'date'       => __( 'Newest', 'psychart' ),
		'price'      => __( 'Price: Low to High', 'psychart' ),
		'price-desc' => __( 'Price: High to Low', 'psychart' ),
		'title'      => __( 'Name: A to Z', 'psychart' ),
	);
	if ( is_search() ) {
		$opts = array( 'relevance' => __( 'Relevance', 'psychart' ) ) + $opts;
	}
	return $opts;
} );
add_filter( 'woocommerce_default_catalog_orderby', function ( $default ) {
	return is_search() ? 'relevance' : 'date';
} );
add_filter( 'loop_shop_per_page', function () {
	return 12;
} );

/* ---------- "In stock only" ---------- */
add_action( 'woocommerce_product_query', function ( $q ) {
	if ( ! empty( $_GET['instock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta   = (array) $q->get( 'meta_query' );
		$meta[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		$q->set( 'meta_query', $meta );
	}
} );

/* ---------- Which attributes appear in the Filters drawer ---------- */
function psychart_filter_attributes() {
	$names = apply_filters( 'psychart_filter_attributes', array( 'size', 'colour', 'color' ) );
	$out   = array();
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return $out;
	}
	foreach ( wc_get_attribute_taxonomies() as $a ) {
		if ( in_array( $a->attribute_name, $names, true ) && taxonomy_exists( 'pa_' . $a->attribute_name ) ) {
			$out[ $a->attribute_name ] = $a->attribute_label ? $a->attribute_label : ucfirst( $a->attribute_name );
		}
	}
	return $out;
}

/* ---------- Query-string helpers ---------- */
function psychart_filter_keys() {
	$keys = array( 'orderby', 's', 'post_type', 'min_price', 'max_price', 'instock' );
	foreach ( array_keys( psychart_filter_attributes() ) as $n ) {
		$keys[] = 'filter_' . $n;
		$keys[] = 'query_type_' . $n;
	}
	return $keys;
}

function psychart_current_args() {
	$out = array();
	foreach ( psychart_filter_keys() as $k ) {
		if ( isset( $_GET[ $k ] ) && is_scalar( $_GET[ $k ] ) && '' !== $_GET[ $k ] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$out[ $k ] = sanitize_text_field( wp_unslash( $_GET[ $k ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}
	return $out;
}

function psychart_archive_base() {
	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	if ( is_search() ) {
		return home_url( '/' );
	}
	return psychart_shop_url();
}

/** URL of the current archive with $changes applied (null/'' removes a key). Pagination resets. */
function psychart_url( $changes = array(), $base = null ) {
	$args = array_merge( psychart_current_args(), $changes );
	$args = array_filter( $args, function ( $v ) {
		return null !== $v && '' !== $v;
	} );
	return add_query_arg( $args, $base ? $base : psychart_archive_base() );
}

/** Number of active filters (shown on the Filters button). */
function psychart_active_filter_count() {
	$c = psychart_current_args();
	$n = 0;
	foreach ( array_keys( psychart_filter_attributes() ) as $name ) {
		if ( ! empty( $c[ 'filter_' . $name ] ) ) {
			$n += count( array_filter( explode( ',', $c[ 'filter_' . $name ] ) ) );
		}
	}
	if ( isset( $c['min_price'] ) || isset( $c['max_price'] ) ) {
		$n++;
	}
	if ( isset( $c['instock'] ) ) {
		$n++;
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$n++;
	}
	return $n;
}

/** URL that clears every filter but keeps the search term and sort order. */
function psychart_clear_url() {
	$keep = array_intersect_key( psychart_current_args(), array_flip( array( 's', 'post_type', 'orderby' ) ) );
	return add_query_arg( $keep, is_search() ? home_url( '/' ) : psychart_shop_url() );
}

/** URL toggling one attribute term on/off (multi-select, OR within an attribute). */
function psychart_attr_toggle_url( $attr, $slug ) {
	$c        = psychart_current_args();
	$selected = ! empty( $c[ 'filter_' . $attr ] ) ? array_filter( explode( ',', $c[ 'filter_' . $attr ] ) ) : array();
	if ( in_array( $slug, $selected, true ) ) {
		$selected = array_diff( $selected, array( $slug ) );
	} else {
		$selected[] = $slug;
	}
	return psychart_url( array(
		'filter_' . $attr     => implode( ',', $selected ),
		'query_type_' . $attr => count( $selected ) > 1 ? 'or' : null,
	) );
}

/** Top-level + child product categories for the drawer (Uncategorized hidden). */
function psychart_category_list() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'name' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$default = (int) get_option( 'default_product_cat' );
	return array_values( array_filter( $terms, function ( $t ) use ( $default ) {
		return (int) $t->term_id !== $default && 'uncategorized' !== $t->slug;
	} ) );
}

/** Heading + sub line for the archive. */
function psychart_archive_heading() {
	if ( is_search() ) {
		return array( __( 'Search', 'psychart' ), sprintf( /* translators: %s: search term */ __( 'Results for “%s”', 'psychart' ), get_search_query() ) );
	}
	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$desc = wp_strip_all_tags( term_description() );
		return array( single_term_title( '', false ), $desc ? $desc : __( 'Browse our complete collection', 'psychart' ) );
	}
	return array( __( 'Shop All', 'psychart' ), __( 'Browse our complete collection', 'psychart' ) );
}
