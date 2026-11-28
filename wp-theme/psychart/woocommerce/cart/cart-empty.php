<?php
/**
 * Empty cart. Overrides woocommerce/cart/cart-empty.php
 *
 * @version 7.0.1
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
?>
<div class="psy-empty">
	<?php echo psychart_icon( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<p class="psy-label psy-label--strong"><?php esc_html_e( 'Your bag is empty', 'psychart' ); ?></p>
	<p><?php esc_html_e( 'Nothing here yet. Start with the latest pieces.', 'psychart' ); ?></p>
	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<a class="psy-btn psy-btn--solid" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Continue shopping', 'psychart' ); ?> <?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	<?php endif; ?>
</div>
<?php psychart_product_picks( 3, __( 'Latest pieces', 'psychart' ) ); ?>
