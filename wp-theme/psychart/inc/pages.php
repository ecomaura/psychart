<?php
/**
 * Phase 5: content pages (About, FAQ, Shipping & Returns, Contact), 404, cart/checkout/account support.
 */
defined( 'ABSPATH' ) || exit;

/** Page header used by every content template. */
function psychart_page_head( $label, $title, $intro = '' ) {
	?>
	<header class="psy-pagehead">
		<?php if ( $label ) : ?><p class="psy-label"><?php echo esc_html( $label ); ?></p><?php endif; ?>
		<h1 class="psy-display psy-pagehead__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?><p class="psy-pagehead__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
	</header>
	<?php
}

/** True when the current page has editor content the owner wrote. */
function psychart_has_editor_content() {
	return '' !== trim( wp_strip_all_tags( (string) get_post_field( 'post_content', get_queried_object_id() ) ) );
}

/* ---------------------------------------------------------------- About */

function psychart_about_defaults() {
	return array(
		'intro'        => __( 'White space. Dark intent. PSYCHART.CO is a minimal streetwear label for people who dress like identity is part of the art.', 'psychart' ),
		'phil_title'   => __( 'Philosophy', 'psychart' ),
		'phil_text'    => __( "We design fewer pieces and make them better. Every cut is drawn to sit quietly on the body and say more than a logo ever could. Clothes are a way to edit yourself, not announce yourself.", 'psychart' ),
		'sust_title'   => __( 'Sustainability', 'psychart' ),
		'sust_text'    => __( "Small runs, considered fabrics and no seasonal dumping. We make what we can stand behind and keep it in circulation for as long as possible.", 'psychart' ),
		'stat1_value'  => '100%',
		'stat1_label'  => __( 'Original designs', 'psychart' ),
		'stat2_value'  => '50+',
		'stat2_label'  => __( 'Pieces released', 'psychart' ),
		'stat3_value'  => __( '30-day', 'psychart' ),
		'stat3_label'  => __( 'Returns', 'psychart' ),
	);
}

function psychart_about( $key ) {
	$d = psychart_about_defaults();
	return get_theme_mod( 'psychart_about_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'psychart_about', array(
		'title'    => __( 'About page', 'psychart' ),
		'priority' => 31,
	) );
	$labels = array(
		'intro'       => array( __( 'Intro', 'psychart' ), 'textarea' ),
		'phil_title'  => array( __( 'Philosophy title', 'psychart' ), 'text' ),
		'phil_text'   => array( __( 'Philosophy text', 'psychart' ), 'textarea' ),
		'sust_title'  => array( __( 'Sustainability title', 'psychart' ), 'text' ),
		'sust_text'   => array( __( 'Sustainability text', 'psychart' ), 'textarea' ),
		'stat1_value' => array( __( 'Stat 1 value', 'psychart' ), 'text' ),
		'stat1_label' => array( __( 'Stat 1 label', 'psychart' ), 'text' ),
		'stat2_value' => array( __( 'Stat 2 value', 'psychart' ), 'text' ),
		'stat2_label' => array( __( 'Stat 2 label', 'psychart' ), 'text' ),
		'stat3_value' => array( __( 'Stat 3 value', 'psychart' ), 'text' ),
		'stat3_label' => array( __( 'Stat 3 label', 'psychart' ), 'text' ),
	);
	$defaults = psychart_about_defaults();
	foreach ( $labels as $key => $def ) {
		$id = 'psychart_about_' . $key;
		$wp_customize->add_setting( $id, array(
			'default'           => $defaults[ $key ],
			'sanitize_callback' => 'textarea' === $def[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array( 'label' => $def[0], 'section' => 'psychart_about', 'type' => $def[1] ) );
	}

	$wp_customize->add_setting( 'psychart_contact_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'psychart_contact_email', array(
		'label'       => __( 'Contact form recipient', 'psychart' ),
		'description' => __( 'Where Contact page messages are sent. Empty = the site admin email.', 'psychart' ),
		'section'     => 'psychart_about',
		'type'        => 'email',
	) );
} );

/* ----------------------------------------------------------------- FAQ */

function psychart_faq_items() {
	$items = array(
		array( __( 'How does sizing work?', 'psychart' ), __( 'Use “Find my size” on any product page. It takes your measurements, body shape and preferred fit and recommends a size. Every product also has its own size chart.', 'psychart' ) ),
		array( __( 'When will my order ship?', 'psychart' ), __( 'Orders are packed within a few business days. You will get an email with tracking as soon as your parcel leaves us.', 'psychart' ) ),
		array( __( 'Can I return or exchange something?', 'psychart' ), __( 'Yes. You have 30 days from delivery to return unworn items with their tags attached. See Shipping & Returns for the steps.', 'psychart' ) ),
		array( __( 'How do I care for my pieces?', 'psychart' ), __( 'Care instructions are listed on each product page under Materials & care. In general: wash cold, inside out, and hang dry.', 'psychart' ) ),
		array( __( 'I have a problem with my order.', 'psychart' ), __( 'Write to us through the Contact page with your order number and we will sort it out.', 'psychart' ) ),
	);
	return apply_filters( 'psychart_faq_items', $items );
}

/* ------------------------------------------------------------- Contact */

add_action( 'admin_post_nopriv_psychart_contact', 'psychart_contact_submit' );
add_action( 'admin_post_psychart_contact', 'psychart_contact_submit' );

function psychart_contact_redirect( $args ) {
	$back = wp_get_referer();
	$back = $back ? $back : home_url( '/contact/' );
	$back = remove_query_arg( array( 'sent', 'err' ), $back );
	wp_safe_redirect( add_query_arg( $args, $back ) . '#contact-form' );
	exit;
}

function psychart_contact_submit() {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'psychart_contact' ) ) {
		psychart_contact_redirect( array( 'err' => 'expired' ) );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot: pretend success.
		psychart_contact_redirect( array( 'sent' => 1 ) );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) || strlen( $message ) < 5 ) {
		psychart_contact_redirect( array( 'err' => 'invalid' ) );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'psy_contact_' . md5( $ip );
	if ( get_transient( $key ) ) {
		psychart_contact_redirect( array( 'err' => 'wait' ) );
	}
	set_transient( $key, 1, MINUTE_IN_SECONDS );

	$to = sanitize_email( get_theme_mod( 'psychart_contact_email', '' ) );
	$to = $to ? $to : get_option( 'admin_email' );

	/* translators: %s: sender name */
	$subject = sprintf( __( '[%1$s] Message from %2$s', 'psychart' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $name );
	$body    = $message . "\n\n— " . $name . ' <' . $email . '>';
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$ok = wp_mail( $to, $subject, $body, $headers );
	do_action( 'psychart_contact_sent', $name, $email, $message, $ok );

	psychart_contact_redirect( $ok ? array( 'sent' => 1 ) : array( 'err' => 'mail' ) );
}

/* -------------------------------------------------- Create default pages */

/** On first activation, create the pages the footer links already point to. */
add_action( 'after_switch_theme', function () {
	if ( get_option( 'psychart_pages_created' ) ) {
		return;
	}
	$pages = array(
		'about'            => __( 'About', 'psychart' ),
		'faq'              => __( 'FAQ', 'psychart' ),
		'shipping-returns' => __( 'Shipping & Returns', 'psychart' ),
		'contact'          => __( 'Contact', 'psychart' ),
	);
	foreach ( $pages as $slug => $title ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => '',
		) );
	}
	update_option( 'psychart_pages_created', 1 );
} );

/* ------------------------------------------------------ WooCommerce bits */

// Cross-sells use the product loop; the theme keeps the cart focused instead.
add_action( 'init', function () {
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
} );

// Body class so CSS can target wide commerce pages.
add_filter( 'body_class', function ( $classes ) {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		$classes[] = 'psy-commerce';
	}
	return $classes;
} );
