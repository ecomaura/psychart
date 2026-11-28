<?php
/**
 * Phase 4: product page data layer.
 *
 * The product page markup, CSS, advisor and sizing engine come from the static repo page.
 * This file only supplies its data from WooCommerce:
 *   - colour / size  -> product attributes named "Colour" (or "Color") and "Size", used for variations
 *   - colour swatch  -> a colour value stored on each Colour attribute term (Products > Attributes > Configure terms)
 *   - size chart     -> per-product field in the "PSYCHART product page" box on the product edit screen
 *   - add to bag     -> AJAX endpoint below, then the bag drawer (Phase 1) refreshes and opens.
 */
defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Attribute helpers
 * ===================================================================== */

/** Which of the product's attributes is the "size" or "colour" one. Returns array( 'size' => key, 'colour' => key ). */
function psychart_pdp_attr_keys( WC_Product $product ) {
	$found = array();
	foreach ( $product->get_attributes() as $key => $attr ) {
		if ( ! $attr instanceof WC_Product_Attribute || ! $attr->get_variation() ) {
			continue;
		}
		$base = preg_replace( '/^pa_/', '', sanitize_title( $attr->get_name() ) );
		if ( 'size' === $base && ! isset( $found['size'] ) ) {
			$found['size'] = $key;
		} elseif ( in_array( $base, array( 'colour', 'color' ), true ) && ! isset( $found['colour'] ) ) {
			$found['colour'] = $key;
		}
	}
	return $found;
}

/** Options of one attribute as list of array( 'name' =>, 'slug' =>, 'hex' => (colours only) ). */
function psychart_pdp_attr_options( WC_Product $product, $key, $is_colour = false ) {
	$attr = $product->get_attributes()[ $key ];
	$out  = array();
	if ( $attr->is_taxonomy() ) {
		$terms = wc_get_product_terms( $product->get_id(), $attr->get_name(), array( 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		foreach ( $terms as $t ) {
			$o = array( 'name' => $t->name, 'slug' => $t->slug );
			if ( $is_colour ) {
				$o['hex'] = psychart_colour_hex( $t->name, get_term_meta( $t->term_id, 'psychart_hex', true ) );
			}
			$out[] = $o;
		}
	} else {
		foreach ( $attr->get_options() as $opt ) {
			$opt = (string) $opt;
			$o   = array( 'name' => $opt, 'slug' => $opt );
			if ( $is_colour ) {
				$o['hex'] = psychart_colour_hex( $opt, '' );
			}
			$out[] = $o;
		}
	}
	return $out;
}

/** Swatch colour: saved term colour, else a hex/known colour name, else neutral grey. */
function psychart_colour_hex( $name, $saved ) {
	if ( is_string( $saved ) && preg_match( '/^#[0-9a-f]{3,8}$/i', $saved ) ) {
		return $saved;
	}
	$n = strtolower( trim( $name ) );
	if ( preg_match( '/^#[0-9a-f]{3,8}$/i', $n ) ) {
		return $n;
	}
	$map = apply_filters( 'psychart_colour_names', array(
		'black' => '#111111', 'washed black' => '#222222', 'white' => '#ffffff', 'off white' => '#f4f1ea', 'bone' => '#e6dfd2',
		'grey' => '#a3a3a3', 'gray' => '#a3a3a3', 'stone grey' => '#a3a3a3', 'charcoal' => '#3a3a3a', 'forest' => '#1f4d33',
		'green' => '#2f6b43', 'olive' => '#5b5d33', 'navy' => '#1c2a44', 'blue' => '#2c4f8a', 'indigo' => '#2a3560',
		'red' => '#a32a2a', 'brown' => '#6b4a32', 'tan' => '#b99a74', 'sand' => '#d6c7a8', 'beige' => '#d9ccb4', 'cream' => '#efe8d6',
	) );
	return isset( $map[ $n ] ) ? $map[ $n ] : '#cccccc';
}

/* =====================================================================
 * Colour field on Colour attribute terms
 * ===================================================================== */
add_action( 'init', function () {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return;
	}
	foreach ( wc_get_attribute_taxonomies() as $a ) {
		if ( ! in_array( $a->attribute_name, array( 'colour', 'color' ), true ) ) {
			continue;
		}
		$tax = 'pa_' . $a->attribute_name;
		add_action( $tax . '_add_form_fields', function () {
			?>
			<div class="form-field">
				<label for="psychart_hex"><?php esc_html_e( 'Swatch colour', 'psychart' ); ?></label>
				<input type="color" id="psychart_hex" name="psychart_hex" value="#222222">
				<p><?php esc_html_e( 'Shown as the round swatch on the product page.', 'psychart' ); ?></p>
			</div>
			<?php
		} );
		add_action( $tax . '_edit_form_fields', function ( $term ) {
			$hex = get_term_meta( $term->term_id, 'psychart_hex', true );
			$hex = $hex ? $hex : psychart_colour_hex( $term->name, '' );
			?>
			<tr class="form-field">
				<th scope="row"><label for="psychart_hex"><?php esc_html_e( 'Swatch colour', 'psychart' ); ?></label></th>
				<td><input type="color" id="psychart_hex" name="psychart_hex" value="<?php echo esc_attr( $hex ); ?>">
				<p class="description"><?php esc_html_e( 'Shown as the round swatch on the product page.', 'psychart' ); ?></p></td>
			</tr>
			<?php
		} );
		$save = function ( $term_id ) {
			if ( isset( $_POST['psychart_hex'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core term screens verify the nonce
				$hex = sanitize_hex_color( wp_unslash( $_POST['psychart_hex'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( $hex ) {
					update_term_meta( $term_id, 'psychart_hex', $hex );
				}
			}
		};
		add_action( 'created_' . $tax, $save );
		add_action( 'edited_' . $tax, $save );
	}
}, 20 );

/* =====================================================================
 * Per-product fields: size chart + detail accordions
 * ===================================================================== */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'psychart_pdp', __( 'PSYCHART product page', 'psychart' ), 'psychart_pdp_metabox', 'product', 'normal', 'default' );
} );

function psychart_pdp_metabox( $post ) {
	wp_nonce_field( 'psychart_pdp_save', 'psychart_pdp_nonce' );
	$unit  = get_post_meta( $post->ID, '_psychart_chart_unit', true );
	$unit  = 'cm' === $unit ? 'cm' : 'in';
	$rows  = get_post_meta( $post->ID, '_psychart_chart_rows', true );
	$mat   = get_post_meta( $post->ID, '_psychart_materials', true );
	$fit   = get_post_meta( $post->ID, '_psychart_fit', true );
	$deliv = get_post_meta( $post->ID, '_psychart_delivery', true );
	?>
	<p><strong><?php esc_html_e( 'Size chart (garment measurements)', 'psychart' ); ?></strong></p>
	<p>
		<label><input type="radio" name="psychart_chart_unit" value="in" <?php checked( $unit, 'in' ); ?>> <?php esc_html_e( 'Inches', 'psychart' ); ?></label>
		&nbsp;
		<label><input type="radio" name="psychart_chart_unit" value="cm" <?php checked( $unit, 'cm' ); ?>> <?php esc_html_e( 'Centimetres', 'psychart' ); ?></label>
	</p>
	<p><textarea name="psychart_chart_rows" rows="7" class="widefat code" placeholder="XS, 41.7, 39.4, 39.4, 26.0&#10;S, 44.1, 41.7, 41.7, 26.8"><?php echo esc_textarea( $rows ); ?></textarea></p>
	<p class="description"><?php esc_html_e( 'One size per line: Size, Chest, Waist, Hips, Length. Size names must match the Size attribute values. Leave empty to hide "Find my size".', 'psychart' ); ?></p>
	<hr>
	<p><label for="psychart_materials"><strong><?php esc_html_e( 'Materials & care', 'psychart' ); ?></strong></label></p>
	<p><textarea id="psychart_materials" name="psychart_materials" rows="3" class="widefat"><?php echo esc_textarea( $mat ); ?></textarea></p>
	<p><label for="psychart_fit"><strong><?php esc_html_e( 'Fit', 'psychart' ); ?></strong></label></p>
	<p><textarea id="psychart_fit" name="psychart_fit" rows="3" class="widefat"><?php echo esc_textarea( $fit ); ?></textarea></p>
	<p><label for="psychart_delivery"><strong><?php esc_html_e( 'Delivery & returns', 'psychart' ); ?></strong></label></p>
	<p><textarea id="psychart_delivery" name="psychart_delivery" rows="3" class="widefat" placeholder="<?php echo esc_attr( psychart_pdp_default_delivery() ); ?>"><?php echo esc_textarea( $deliv ); ?></textarea></p>
	<p class="description"><?php esc_html_e( 'Empty sections are hidden. Delivery & returns falls back to the store-wide text under Appearance > Customize > Product page.', 'psychart' ); ?></p>
	<?php
}

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['psychart_pdp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['psychart_pdp_nonce'] ) ), 'psychart_pdp_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$unit = ( isset( $_POST['psychart_chart_unit'] ) && 'cm' === $_POST['psychart_chart_unit'] ) ? 'cm' : 'in';
	update_post_meta( $post_id, '_psychart_chart_unit', $unit );
	update_post_meta( $post_id, '_psychart_chart_rows', isset( $_POST['psychart_chart_rows'] ) ? sanitize_textarea_field( wp_unslash( $_POST['psychart_chart_rows'] ) ) : '' );
	foreach ( array( 'materials', 'fit', 'delivery' ) as $f ) {
		update_post_meta( $post_id, '_psychart_' . $f, isset( $_POST[ 'psychart_' . $f ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'psychart_' . $f ] ) ) : '' );
	}
} );

function psychart_pdp_default_delivery() {
	return (string) get_theme_mod( 'psychart_pdp_delivery', __( 'Free delivery on orders over $100. Returns accepted within 30 days, unworn and with tags.', 'psychart' ) );
}

add_action( 'customize_register', function ( WP_Customize_Manager $c ) {
	$c->add_section( 'psychart_pdp', array( 'title' => __( 'Product page', 'psychart' ), 'priority' => 31 ) );
	$c->add_setting( 'psychart_pdp_delivery', array(
		'default'           => __( 'Free delivery on orders over $100. Returns accepted within 30 days, unworn and with tags.', 'psychart' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$c->add_control( 'psychart_pdp_delivery', array(
		'label'       => __( 'Delivery & returns (all products)', 'psychart' ),
		'description' => __( 'Used when a product has no Delivery & returns text of its own.', 'psychart' ),
		'section'     => 'psychart_pdp',
		'type'        => 'textarea',
	) );
} );

/** Parse the size chart field into the structure the sizing engine expects (always inches). */
function psychart_pdp_chart( $post_id, array $size_labels ) {
	$raw  = (string) get_post_meta( $post_id, '_psychart_chart_rows', true );
	$unit = 'cm' === get_post_meta( $post_id, '_psychart_chart_unit', true ) ? 'cm' : 'in';
	$by   = array();
	foreach ( $size_labels as $l ) {
		$by[ strtolower( $l ) ] = $l;
	}
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$c = array_map( 'trim', explode( ',', $line ) );
		if ( count( $c ) < 5 || ! is_numeric( $c[1] ) || ! is_numeric( $c[2] ) || ! is_numeric( $c[3] ) || ! is_numeric( $c[4] ) ) {
			continue; // header or malformed line
		}
		$k = strtolower( $c[0] );
		if ( ! isset( $by[ $k ] ) ) {
			continue; // not a size this product actually has
		}
		$f      = 'cm' === $unit ? 1 / 2.54 : 1;
		$rows[] = array(
			'size'   => $by[ $k ],
			'chest'  => round( (float) $c[1] * $f, 1 ),
			'waist'  => round( (float) $c[2] * $f, 1 ),
			'hips'   => round( (float) $c[3] * $f, 1 ),
			'length' => round( (float) $c[4] * $f, 1 ),
		);
	}
	// Keep the order of the product's own sizes.
	$order = array_flip( $size_labels );
	usort( $rows, function ( $a, $b ) use ( $order ) {
		return $order[ $a['size'] ] <=> $order[ $b['size'] ];
	} );
	return $rows ? array( 'unit' => 'in', 'rows' => $rows ) : null;
}

/* =====================================================================
 * window.PRODUCT
 * ===================================================================== */
function psychart_pdp_image( $attachment_id ) {
	$src = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' ) : false;
	if ( ! $src ) {
		return null;
	}
	return array(
		'src'    => $src[0],
		'srcset' => (string) wp_get_attachment_image_srcset( $attachment_id, 'woocommerce_single' ),
		'w'      => $src[1],
		'h'      => $src[2],
		'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
}

function psychart_pdp_category_trail( WC_Product $product ) {
	$ids = $product->get_category_ids();
	$best = null;
	$best_depth = -1;
	foreach ( $ids as $id ) {
		$t = get_term( $id, 'product_cat' );
		if ( ! $t || is_wp_error( $t ) || 'uncategorized' === $t->slug ) {
			continue;
		}
		$depth = count( get_ancestors( $id, 'product_cat' ) );
		if ( $depth > $best_depth ) {
			$best = $t;
			$best_depth = $depth;
		}
	}
	if ( ! $best ) {
		return '';
	}
	$names = array();
	foreach ( array_reverse( get_ancestors( $best->term_id, 'product_cat' ) ) as $aid ) {
		$a = get_term( $aid, 'product_cat' );
		if ( $a && ! is_wp_error( $a ) ) {
			$names[] = $a->name;
		}
	}
	$names[] = $best->name;
	return implode( ' / ', $names );
}

function psychart_pdp_data( WC_Product $product ) {
	$keys    = $product->is_type( 'variable' ) ? psychart_pdp_attr_keys( $product ) : array();
	$colours = isset( $keys['colour'] ) ? psychart_pdp_attr_options( $product, $keys['colour'], true ) : array();
	$sizes   = isset( $keys['size'] ) ? psychart_pdp_attr_options( $product, $keys['size'] ) : array();

	$variations = array();
	if ( $keys ) {
		$limit = (int) apply_filters( 'psychart_pdp_max_variations', 100 );
		foreach ( array_slice( $product->get_children(), 0, $limit ) as $vid ) {
			$v = wc_get_product( $vid );
			if ( ! $v || ! $v->is_type( 'variation' ) || 'publish' !== $v->get_status() ) {
				continue;
			}
			$attrs = $v->get_attributes(); // key => slug ('' means any)
			$a     = array();
			foreach ( array( 'colour', 'size' ) as $role ) {
				if ( isset( $keys[ $role ] ) ) {
					$a[ $role ] = isset( $attrs[ $keys[ $role ] ] ) ? (string) $attrs[ $keys[ $role ] ] : '';
				}
			}
			$variations[] = array(
				'id'        => $vid,
				'attrs'     => $a,
				'inStock'   => $v->is_in_stock() && $v->is_purchasable(),
				'priceHtml' => $v->get_price_html(),
				'image'     => psychart_pdp_image( $v->get_image_id() ),
			);
		}
	}

	$size_labels = wp_list_pluck( $sizes, 'name' );
	$chart       = $sizes ? psychart_pdp_chart( $product->get_id(), $size_labels ) : null;

	// Default colour: the product's default selection if set, else the first colour with stock.
	$default = 0;
	if ( $colours ) {
		$defaults = $product->get_default_attributes();
		$want     = isset( $keys['colour'], $defaults[ $keys['colour'] ] ) ? $defaults[ $keys['colour'] ] : '';
		$found    = false;
		foreach ( $colours as $i => $c ) {
			if ( $want && 0 === strcasecmp( $want, $c['slug'] ) ) {
				$default = $i;
				$found   = true;
				break;
			}
		}
		if ( ! $found ) {
			foreach ( $colours as $i => $c ) {
				foreach ( $variations as $v ) {
					if ( $v['inStock'] && ( '' === $v['attrs']['colour'] || 0 === strcasecmp( $v['attrs']['colour'], $c['slug'] ) ) ) {
						$default = $i;
						break 2;
					}
				}
			}
		}
	}

	$price = (float) wc_get_price_to_display( $product );
	$currency = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );

	return array(
		'id'          => $product->get_id(),
		'title'       => $product->get_name(),
		'price'       => $price,
		'currency'    => $currency,
		'priceHtml'   => $product->get_price_html(),
		'type'        => $product->is_type( 'variable' ) ? 'variable' : 'simple',
		'inStock'     => $product->is_in_stock(),
		'hasColour'   => (bool) $colours,
		'hasSize'     => (bool) $sizes,
		// The advisor reads P.colours[i].hex, so there is always at least one entry.
		'colours'     => $colours ? $colours : array( array( 'name' => '', 'slug' => '', 'hex' => '#222222' ) ),
		'sizes'       => array_map( function ( $s ) {
			return array( 'label' => $s['name'], 'slug' => $s['slug'], 'available' => true );
		}, $sizes ),
		'sizeChart'   => $chart,
		'variations'  => $variations,
		'defaultColour' => $default,
		'defaultImage'  => psychart_pdp_image( $product->get_image_id() ),
	);
}

/* =====================================================================
 * Assets (product page only)
 * ===================================================================== */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return;
	}
	$base = PSYCHART_URI . '/assets/pdp/';
	$ver  = PSYCHART_VERSION;

	wp_enqueue_style( 'psychart-pdp', $base . 'css/styles.css', array( 'psychart-main' ), $ver );
	wp_enqueue_style( 'psychart-pdp-advisor', $base . 'css/advisor.css', array( 'psychart-pdp' ), $ver );
	wp_enqueue_style( 'psychart-pdp-theme', $base . 'css/pdp-theme.css', array( 'psychart-pdp-advisor' ), $ver );

	// Same order as the static page: data, page behaviour, estimate, engine, advisor.
	$defer = array( 'strategy' => 'defer', 'in_footer' => true );
	wp_register_script( 'psychart-pdp', $base . 'js/pdp.js', array( 'psychart-shell' ), $ver, $defer );
	wp_register_script( 'psychart-pdp-estimate', $base . 'js/estimate.js', array( 'psychart-pdp' ), $ver, $defer );
	wp_register_script( 'psychart-pdp-engine', $base . 'js/engine.js', array( 'psychart-pdp-estimate' ), $ver, $defer );
	wp_register_script( 'psychart-pdp-advisor', $base . 'js/advisor.js', array( 'psychart-pdp-engine' ), $ver, $defer );

	$data = psychart_pdp_data( $product );
	wp_add_inline_script(
		'psychart-pdp',
		'window.PRODUCT=' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ) . ';',
		'before'
	);
	wp_enqueue_script( 'psychart-pdp' );
	// The advisor is only useful with a size chart.
	if ( ! empty( $data['sizeChart'] ) ) {
		wp_enqueue_script( 'psychart-pdp-advisor' );
	}
}, 20 );

add_action( 'wp_head', function () {
	if ( function_exists( 'is_product' ) && is_product() ) {
		foreach ( array( 'archivo-latin-wght-normal.woff2', 'inter-latin-wght-normal.woff2' ) as $f ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( PSYCHART_URI . '/assets/pdp/fonts/' . $f ) );
		}
	}
}, 2 );

/* =====================================================================
 * Add to bag (AJAX). The variation is resolved on the server from the chosen
 * colour and size, so a tampered request cannot add a different product.
 * ===================================================================== */
function psychart_ajax_add_to_cart() {
	if ( ! psychart_cart_ready() ) {
		wp_send_json_error( array( 'message' => __( 'The bag is not available right now.', 'psychart' ) ) );
	}
	$pid     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification -- same as WooCommerce core add-to-cart, which is not nonce-protected so it works on cached pages
	$product = $pid ? wc_get_product( $pid ) : false;
	if ( ! $product || 'publish' !== $product->get_status() ) {
		wp_send_json_error( array( 'message' => __( 'This product is not available.', 'psychart' ) ) );
	}

	$variation_id = 0;
	$variation    = array();

	if ( $product->is_type( 'variable' ) ) {
		$keys   = psychart_pdp_attr_keys( $product );
		$chosen = array(
			'colour' => isset( $_POST['colour'] ) ? wc_clean( wp_unslash( $_POST['colour'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
			'size'   => isset( $_POST['size'] ) ? wc_clean( wp_unslash( $_POST['size'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		);
		foreach ( $product->get_attributes() as $key => $attr ) {
			if ( ! $attr->get_variation() ) {
				continue;
			}
			$role  = array_search( $key, $keys, true );
			$value = $role ? $chosen[ $role ] : '';
			if ( ! $role ) { // any other variation attribute: only OK if there is a single option
				$opts  = psychart_pdp_attr_options( $product, $key );
				$value = 1 === count( $opts ) ? $opts[0]['slug'] : '';
			}
			if ( '' === $value ) {
				wp_send_json_error( array( 'message' => __( 'Please choose your options first.', 'psychart' ) ) );
			}
			$variation[ 'attribute_' . sanitize_title( $attr->get_name() ) ] = $value;
		}
		$variation_id = (int) WC_Data_Store::load( 'product' )->find_matching_product_variation( $product, $variation );
		if ( ! $variation_id ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, that combination is not available.', 'psychart' ) ) );
		}
	}

	wc_clear_notices();
	$key = WC()->cart->add_to_cart( $pid, 1, $variation_id, $variation );
	if ( ! $key ) {
		$msgs = array();
		foreach ( wc_get_notices( 'error' ) as $n ) {
			$msgs[] = wp_strip_all_tags( is_array( $n ) ? $n['notice'] : $n );
		}
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $msgs ? implode( ' ', $msgs ) : __( 'Could not add this to your bag.', 'psychart' ) ) );
	}
	wc_clear_notices();
	wp_send_json_success( array( 'count' => psychart_cart_count() ) );
}
add_action( 'wp_ajax_psychart_add_to_cart', 'psychart_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_psychart_add_to_cart', 'psychart_ajax_add_to_cart' );
