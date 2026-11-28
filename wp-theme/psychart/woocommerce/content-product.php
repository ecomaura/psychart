<?php
/** Product card used in every product grid. */
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$gallery = $product->get_gallery_image_ids();
$alt_img = $gallery ? wp_get_attachment_image( $gallery[0], 'woocommerce_thumbnail', false, array( 'class' => 'psy-card__img psy-card__img--alt', 'alt' => '', 'loading' => 'lazy' ) ) : '';
$main    = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'psy-card__img', 'loading' => 'lazy' ) );
$sold    = ! $product->is_in_stock();
$sale    = $product->is_on_sale();
?>
<li <?php wc_product_class( 'psy-card', $product ); ?>>
	<a class="psy-card__media" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>" tabindex="-1">
		<?php echo $main . $alt_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $sold ) : ?>
			<span class="psy-badge"><?php esc_html_e( 'Sold out', 'psychart' ); ?></span>
		<?php elseif ( $sale ) : ?>
			<span class="psy-badge psy-badge--dark"><?php esc_html_e( 'Sale', 'psychart' ); ?></span>
		<?php endif; ?>
	</a>
	<div class="psy-card__body">
		<h2 class="psy-display psy-card__title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<p class="psy-card__price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</div>
</li>
