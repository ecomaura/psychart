<?php
/**
 * Cart. Overrides woocommerce/cart/cart.php
 *
 * @version 7.9.0
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<div class="psy-cart">
<form class="woocommerce-cart-form psy-cart__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<ul class="psy-cartlist">
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			$permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			$name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
			$thumb     = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
			?>
			<li class="psy-cartitem woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<div class="psy-cartitem__thumb">
					<?php echo $permalink ? '<a href="' . esc_url( $permalink ) . '">' . $thumb . '</a>' : $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="psy-cartitem__info">
					<h2 class="psy-display psy-cartitem__name">
						<?php echo $permalink ? '<a href="' . esc_url( $permalink ) . '">' . wp_kses_post( $name ) . '</a>' : wp_kses_post( $name ); ?>
					</h2>
					<?php
					do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
					echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput
					if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
						echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
					}
					?>
					<p class="psy-cartitem__price"><?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>

					<div class="psy-cartitem__qty">
						<?php
						if ( $_product->is_sold_individually() ) {
							$qty = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$qty = woocommerce_quantity_input(
								array(
									'input_name'   => "cart[{$cart_item_key}][qty]",
									'input_value'  => $cart_item['quantity'],
									'max_value'    => $_product->get_max_purchase_quantity(),
									'min_value'    => '0',
									'product_name' => $_product->get_name(),
								),
								$_product,
								false
							);
						}
						echo apply_filters( 'woocommerce_cart_item_quantity', $qty, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</div>
				</div>

				<div class="psy-cartitem__side">
					<span class="psy-cartitem__subtotal"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a href="%s" class="remove psy-cartitem__remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							/* translators: %s: product name */
							esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $_product->get_name() ) ) ),
							esc_attr( $product_id ),
							esc_attr( $_product->get_sku() ),
							esc_html__( 'Remove', 'psychart' )
						),
						$cart_item_key
					);
					?>
				</div>
			</li>
			<?php
		}
		?>

		<?php do_action( 'woocommerce_cart_contents' ); ?>
		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</ul>

	<div class="psy-cart__actions">
		<?php if ( wc_coupons_enabled() ) : ?>
			<div class="psy-coupon">
				<label class="psy-label" for="coupon_code"><?php esc_html_e( 'Discount code', 'psychart' ); ?></label>
				<div class="psy-coupon__row">
					<input type="text" name="coupon_code" class="psy-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Enter code', 'psychart' ); ?>">
					<button type="submit" class="psy-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'psychart' ); ?></button>
				</div>
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</div>
		<?php endif; ?>

		<button type="submit" class="psy-btn psy-btn--ghost" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" disabled aria-disabled="true"><?php esc_html_e( 'Update bag', 'psychart' ); ?></button>

		<?php do_action( 'woocommerce_cart_actions' ); ?>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals psy-cart__totals">
	<?php do_action( 'woocommerce_cart_collaterals' ); ?>
</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
