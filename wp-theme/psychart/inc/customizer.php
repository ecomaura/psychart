<?php
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'psychart_home', array(
		'title'    => __( 'Home page', 'psychart' ),
		'priority' => 30,
	) );

	// Product choices for the story cards (first 200 published products).
	$products = array( 0 => __( '— None (links to Shop) —', 'psychart' ) );
	if ( function_exists( 'wc_get_products' ) ) {
		foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 200, 'orderby' => 'title', 'order' => 'ASC', 'return' => 'objects' ) ) as $p ) {
			$products[ $p->get_id() ] = $p->get_name();
		}
	}

	foreach ( psychart_home_fields() as $section_id => $section ) {
		$wp_customize->add_section( $section_id, array(
			'title' => $section['title'],
			'panel' => 'psychart_home',
		) );

		foreach ( $section['fields'] as $key => $def ) {
			list( $type, $label, $default ) = $def;
			$id = 'psychart_' . $key;

			$sanitize = array(
				'text'     => 'sanitize_text_field',
				'textarea' => 'sanitize_textarea_field',
				'url'      => 'esc_url_raw',
				'media'    => 'absint',
				'product'  => 'absint',
			);

			$wp_customize->add_setting( $id, array(
				'default'           => $default,
				'sanitize_callback' => $sanitize[ $type ],
			) );

			if ( 'media' === $type ) {
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, array(
					'label'     => $label,
					'section'   => $section_id,
					'mime_type' => 'image',
				) ) );
			} elseif ( 'product' === $type ) {
				$wp_customize->add_control( $id, array(
					'label'   => $label,
					'section' => $section_id,
					'type'    => 'select',
					'choices' => $products,
				) );
			} else {
				$wp_customize->add_control( $id, array(
					'label'   => $label,
					'section' => $section_id,
					'type'    => $type,
				) );
			}
		}
	}

	$wp_customize->add_setting( 'psychart_footer_tagline', array(
		'default'           => __( 'Minimal streetwear for those who dress like identity is part of the art.', 'psychart' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'psychart_footer_tagline', array(
		'label'   => __( 'Footer tagline', 'psychart' ),
		'section' => 'title_tagline',
		'type'    => 'text',
	) );
} );
