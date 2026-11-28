<?php
/**
 * Bag drawer data layer. Uses the real WooCommerce cart; the drawer is re-rendered
 * through admin-ajax so it stays correct on cached pages.
 */
defined( 'ABSPATH' ) || exit;

function psychart_cart_ready() {
	if ( ! function_exists( 'WC' ) ) {
		return false;
	}
	if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}
	return null !== WC()->cart;
}

function psychart_cart_count() {
	return psychart_cart_ready() ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/** Inner HTML of the bag drawer body + footer (everything below the drawer header). */
function psychart_bag_inner_html() {
	ob_start();
	$cart = psychart_cart_ready() ? WC()->cart : null;

	if ( ! $cart || $cart->is_empty() ) {
		?>
		<div class="psy-bag__empty">
			<svg class="psy-icon psy-icon--lg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z"/><path d="M4 7l8 4 8-4M12 11v10"/></svg>
			<p><?php esc_html_e( 'Your bag is empty', 'psychart' ); ?></p>
			<button type="button" class="psy-link-underline" data-psy-close><?php esc_html_e( 'Continue Shopping', 'psychart' ); ?></button>
		</div>
		<?php
		return ob_get_clean();
	}

	echo '<ul class="psy-bag__items">';
	foreach ( $cart->get_cart() as $key => $item ) {
		$product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
		if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
			continue;
		}
		$permalink = $product->is_visible() ? $product->get_permalink( $item ) : '';
		$thumb     = $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) );
		$name      = $product->get_name();
		?>
		<li class="psy-bag__item" data-key="<?php echo esc_attr( $key ); ?>">
			<div class="psy-bag__thumb"><?php echo $permalink ? '<a href="' . esc_url( $permalink ) . '">' . $thumb . '</a>' : $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="psy-bag__info">
				<p class="psy-bag__name"><?php echo $permalink ? '<a href="' . esc_url( $permalink ) . '">' . esc_html( $name ) . '</a>' : esc_html( $name ); ?></p>
				<?php echo wc_get_formatted_cart_item_data( $item ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="psy-qty" role="group" aria-label="<?php esc_attr_e( 'Quantity', 'psychart' ); ?>">
					<button type="button" data-psy-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'psychart' ); ?>"><?php echo psychart_icon( 'minus' ); // phpcs:ignore ?></button>
					<span><?php echo (int) $item['quantity']; ?></span>
					<button type="button" data-psy-qty="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'psychart' ); ?>"><?php echo psychart_icon( 'plus' ); // phpcs:ignore ?></button>
				</div>
			</div>
			<div class="psy-bag__side">
				<span class="psy-bag__price"><?php echo $cart->get_product_subtotal( $product, $item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<button type="button" class="psy-bag__remove" data-psy-remove><?php esc_html_e( 'Remove', 'psychart' ); ?></button>
			</div>
		</li>
		<?php
	}
	echo '</ul>';
	?>
	<div class="psy-bag__foot">
		<div class="psy-bag__subtotal"><span><?php esc_html_e( 'Subtotal', 'psychart' ); ?></span><span><?php echo $cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div>
		<p class="psy-bag__note"><?php esc_html_e( 'Shipping and taxes calculated at checkout.', 'psychart' ); ?></p>
		<a class="psy-btn psy-btn--solid psy-btn--block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'psychart' ); ?></a>
		<a class="psy-link-underline" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View bag', 'psychart' ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

function psychart_cart_send() {
	wp_send_json_success( array(
		'count' => psychart_cart_count(),
		'html'  => psychart_bag_inner_html(),
	) );
}

add_action( 'wp_ajax_psychart_cart', 'psychart_cart_send' );
add_action( 'wp_ajax_nopriv_psychart_cart', 'psychart_cart_send' );

function psychart_cart_update() {
	if ( ! psychart_cart_ready() ) {
		wp_send_json_error();
	}
	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

	if ( $key && WC()->cart->get_cart_item( $key ) ) {
		if ( 0 === $qty ) {
			WC()->cart->remove_cart_item( $key );
		} else {
			WC()->cart->set_quantity( $key, $qty, true );
		}
		WC()->cart->calculate_totals();
	}
	psychart_cart_send();
}
add_action( 'wp_ajax_psychart_cart_update', 'psychart_cart_update' );
add_action( 'wp_ajax_nopriv_psychart_cart_update', 'psychart_cart_update' );

/** Keep the bag count in step with the header after normal (non-AJAX) cart changes too. */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['.psy-bag-count'] = '<span class="psy-bag-count" data-count="' . esc_attr( psychart_cart_count() ) . '">' . esc_html( psychart_cart_count() ) . '</span>';
	return $fragments;
} );
