<?php
/** Home page content model: defaults, getters and small render helpers. */
defined( 'ABSPATH' ) || exit;

/**
 * All editable home fields: key => [ type, label, default ] grouped by Customizer section.
 * Types: text, textarea, url, media, product.
 */
function psychart_home_fields() {
	$f = array(
		'psy_home_hero'  => array(
			'title'  => __( 'Hero', 'psychart' ),
			'fields' => array(
				'hero_image' => array( 'media', __( 'Hero image (portrait, full width)', 'psychart' ), '' ),
				'intro_thumb' => array( 'media', __( 'Tagline card thumbnail', 'psychart' ), '' ),
				'intro_label' => array( 'text', __( 'Tagline card label', 'psychart' ), __( 'Tagline', 'psychart' ) ),
				'intro_title' => array( 'textarea', __( 'Tagline card title (new line = line break)', 'psychart' ), "Your\nAlter Ego" ),
				'intro_note'  => array( 'text', __( 'Tagline card note', 'psychart' ), __( 'Minimal. Elegant. Unmistakable.', 'psychart' ) ),
			),
		),
		'psy_home_alter' => array(
			'title'  => __( 'Alter ego intro', 'psychart' ),
			'fields' => array(
				'ae_eyebrow'    => array( 'text', __( 'Small label', 'psychart' ), 'PsychArt.CO / SS 26' ),
				'ae_title'      => array( 'text', __( 'Headline', 'psychart' ), __( 'Your Alter Ego', 'psychart' ) ),
				'ae_sub'        => array( 'text', __( 'Sub headline', 'psychart' ), __( 'Premium streetwear with a minimal editorial edge.', 'psychart' ) ),
				'ae_body'       => array( 'textarea', __( 'Paragraph', 'psychart' ), __( 'PsychArt.CO strips the noise away and lets silhouette, texture, and attitude do the work. Built for people who dress like identity is part of the art.', 'psychart' ) ),
				'ae_btn1_label' => array( 'text', __( 'Primary button label', 'psychart' ), __( 'Shop the drop', 'psychart' ) ),
				'ae_btn1_url'   => array( 'url', __( 'Primary button link (empty = Shop)', 'psychart' ), '' ),
				'ae_btn2_label' => array( 'text', __( 'Secondary button label', 'psychart' ), __( 'Our world', 'psychart' ) ),
				'ae_btn2_url'   => array( 'url', __( 'Secondary button link (empty = About)', 'psychart' ), '' ),
				'val_1'         => array( 'text', __( 'Value 1', 'psychart' ), __( 'Clean silhouettes', 'psychart' ) ),
				'val_2'         => array( 'text', __( 'Value 2', 'psychart' ), __( 'Premium construction', 'psychart' ) ),
				'val_3'         => array( 'text', __( 'Value 3', 'psychart' ), __( 'Editorial attitude', 'psychart' ) ),
				'val_4'         => array( 'text', __( 'Value 4', 'psychart' ), __( 'Alter ego energy', 'psychart' ) ),
			),
		),
		'psy_home_uniform' => array(
			'title'  => __( 'The New Uniform', 'psychart' ),
			'fields' => array(
				'uni_eyebrow' => array( 'text', __( 'Small label', 'psychart' ), __( 'Signature edit', 'psychart' ) ),
				'uni_title'   => array( 'text', __( 'Headline', 'psychart' ), __( 'The New Uniform', 'psychart' ) ),
				'uni_copy'    => array( 'textarea', __( 'Intro text', 'psychart' ), __( 'Built around your latest denim visual, this homepage edit now feels lighter, sharper, and more fashion-led.', 'psychart' ) ),
			),
		),
		'psy_home_manifesto' => array(
			'title'  => __( 'Manifesto', 'psychart' ),
			'fields' => array(
				'man_eyebrow' => array( 'text', __( 'Small label', 'psychart' ), __( 'Brand manifesto', 'psychart' ) ),
				'man_title'   => array( 'textarea', __( 'Headline (new line = line break)', 'psychart' ), "White space.\nDark intent." ),
			),
		),
	);

	$story = array(
		1 => array( __( '01 / Outerwear', 'psychart' ), "Dual-tone\nDenim Jacket", __( 'Sharp panel contrast, utility pockets, and a clean front-zip silhouette.', 'psychart' ) ),
		2 => array( __( '02 / Full look', 'psychart' ), "Editorial\nSet", __( 'A full look built around contrast, clean lines, and a minimal editorial mood.', 'psychart' ) ),
		3 => array( __( '03 / Denim', 'psychart' ), "Washed\nFlare Denim", __( 'A faded charcoal wash with a long clean flare for a sharper, more elevated streetwear silhouette.', 'psychart' ) ),
	);
	foreach ( $story as $i => $d ) {
		$f['psy_home_uniform']['fields'][ "story_{$i}_product" ] = array( 'product', sprintf( __( 'Card %d: linked product', 'psychart' ), $i ), '' );
		$f['psy_home_uniform']['fields'][ "story_{$i}_image" ]   = array( 'media', sprintf( __( 'Card %d: image (empty = product image)', 'psychart' ), $i ), '' );
		$f['psy_home_uniform']['fields'][ "story_{$i}_label" ]   = array( 'text', sprintf( __( 'Card %d: label', 'psychart' ), $i ), $d[0] );
		$f['psy_home_uniform']['fields'][ "story_{$i}_title" ]   = array( 'textarea', sprintf( __( 'Card %d: title', 'psychart' ), $i ), $d[1] );
		$f['psy_home_uniform']['fields'][ "story_{$i}_desc" ]    = array( 'textarea', sprintf( __( 'Card %d: description', 'psychart' ), $i ), $d[2] );
	}

	$man = array(
		1 => array( __( 'Premium', 'psychart' ), __( 'Refined materials and cleaner construction keep the pieces elevated rather than loud.', 'psychart' ) ),
		2 => array( __( 'Minimal', 'psychart' ), __( 'Every screen is stripped back so the silhouette and brand tone stay front and center.', 'psychart' ) ),
		3 => array( __( 'Elegant', 'psychart' ), __( 'Sharp typography, generous white space, and editorial framing create a polished storefront feel.', 'psychart' ) ),
		4 => array( __( 'Streetwear', 'psychart' ), __( 'The attitude still lands through product, contrast, and the alter ego story behind each drop.', 'psychart' ) ),
	);
	foreach ( $man as $i => $d ) {
		$f['psy_home_manifesto']['fields'][ "man_{$i}_title" ] = array( 'text', sprintf( __( 'Point %d: title', 'psychart' ), $i ), $d[0] );
		$f['psy_home_manifesto']['fields'][ "man_{$i}_desc" ]  = array( 'textarea', sprintf( __( 'Point %d: text', 'psychart' ), $i ), $d[1] );
	}
	return $f;
}

function psychart_mod_default( $key ) {
	foreach ( psychart_home_fields() as $section ) {
		if ( isset( $section['fields'][ $key ] ) ) {
			return $section['fields'][ $key ][2];
		}
	}
	return '';
}

function psychart_mod( $key ) {
	return get_theme_mod( 'psychart_' . $key, psychart_mod_default( $key ) );
}

/** Escaped text with newlines turned into <br>. */
function psychart_mod_lines( $key ) {
	return nl2br( esc_html( psychart_mod( $key ) ) );
}

/** <img> from a media setting, else the bundled demo image. */
function psychart_home_img( $key, $demo, $alt = '', $size = 'large', $args = array() ) {
	$id = absint( psychart_mod( $key ) );
	if ( $id ) {
		return wp_get_attachment_image( $id, $size, false, array_merge( array( 'alt' => $alt ), $args ) );
	}
	$attrs = '';
	foreach ( array_merge( array( 'alt' => $alt, 'loading' => 'lazy', 'decoding' => 'async' ), $args ) as $k => $v ) {
		$attrs .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	return '<img src="' . esc_url( PSYCHART_URI . '/assets/img/demo/' . $demo ) . '"' . $attrs . '>';
}

function psychart_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

function psychart_home_link( $key, $fallback ) {
	$u = psychart_mod( $key );
	return $u ? $u : $fallback;
}

/** One "New Uniform" story card. Links to the chosen WooCommerce product, else to the shop. */
function psychart_story_card( $i, $demo ) {
	$pid     = psychart_story_product_id( $i );
	$product = ( $pid && function_exists( 'wc_get_product' ) ) ? wc_get_product( $pid ) : null;
	$url     = $product ? $product->get_permalink() : psychart_shop_url();
	$title   = psychart_mod( "story_{$i}_title" );

	if ( absint( psychart_mod( "story_{$i}_image" ) ) ) {
		$img = psychart_home_img( "story_{$i}_image", $demo, wp_strip_all_tags( $title ), 'large' );
	} elseif ( $product && $product->get_image_id() ) {
		$img = wp_get_attachment_image( $product->get_image_id(), 'large', false, array( 'alt' => $product->get_name() ) );
	} else {
		$img = psychart_home_img( "story_{$i}_image", $demo, wp_strip_all_tags( $title ), 'large' );
	}
	?>
	<article class="psy-story">
		<a class="psy-story__media" href="<?php echo esc_url( $url ); ?>"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="psy-story__body">
			<p class="psy-label"><?php echo esc_html( psychart_mod( "story_{$i}_label" ) ); ?></p>
			<h3 class="psy-display psy-story__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo psychart_mod_lines( "story_{$i}_title" ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></h3>
			<p class="psy-story__desc"><?php echo esc_html( psychart_mod( "story_{$i}_desc" ) ); ?></p>
		</div>
	</article>
	<?php
}
