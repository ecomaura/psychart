<?php
/** Live product search for the search overlay. */
defined( 'ABSPATH' ) || exit;

function psychart_live_search() {
	$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( mb_strlen( $term ) < 2 || ! function_exists( 'wc_get_product' ) ) {
		wp_send_json_success( array( 'items' => array(), 'more' => '' ) );
	}

	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		's'              => $term,
		'posts_per_page' => 6,
		'no_found_rows'  => false,
		'tax_query'      => array( // Respect catalogue visibility (hidden products stay hidden).
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'exclude-from-search' ),
				'operator' => 'NOT IN',
			),
		),
	) );

	$items = array();
	foreach ( $q->posts as $post ) {
		$p = wc_get_product( $post );
		if ( ! $p ) {
			continue;
		}
		$items[] = array(
			'title' => $p->get_name(),
			'url'   => $p->get_permalink(),
			'price' => wp_strip_all_tags( html_entity_decode( $p->get_price_html() ) ),
			'img'   => wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src( 'woocommerce_thumbnail' ),
		);
	}

	wp_send_json_success( array(
		'items' => $items,
		'total' => (int) $q->found_posts,
		'more'  => add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ),
	) );
}
add_action( 'wp_ajax_psychart_search', 'psychart_live_search' );
add_action( 'wp_ajax_nopriv_psychart_search', 'psychart_live_search' );
