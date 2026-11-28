<?php
/**
 * "Get Access" signups. Stored as a private post type (Subscribers in wp-admin),
 * so there is no third-party dependency. Hook psychart_subscribed to forward to Mailchimp/Klaviyo.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'psy_subscriber', array(
		'labels'              => array( 'name' => __( 'Subscribers', 'psychart' ), 'singular_name' => __( 'Subscriber', 'psychart' ) ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'        => true,
	) );
} );

function psychart_subscribe() {
	check_ajax_referer( 'psychart', 'nonce' );

	// Honeypot.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success();
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Enter a valid email address.', 'psychart' ) ) );
	}

	$exists = get_posts( array(
		'post_type'      => 'psy_subscriber',
		'post_status'    => 'any',
		'title'          => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( ! $exists ) {
		wp_insert_post( array(
			'post_type'   => 'psy_subscriber',
			'post_status' => 'publish',
			'post_title'  => $email,
		) );
		do_action( 'psychart_subscribed', $email );
	}

	wp_send_json_success( array( 'message' => __( 'You are on the list.', 'psychart' ) ) );
}
add_action( 'wp_ajax_psychart_subscribe', 'psychart_subscribe' );
add_action( 'wp_ajax_nopriv_psychart_subscribe', 'psychart_subscribe' );
