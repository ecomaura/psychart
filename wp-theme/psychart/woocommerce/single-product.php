<?php
/**
 * Single product: the PSYCHART product page (ported from the static repo page).
 * Markup, ids and classes are the same as index.html so css/advisor/engine work as written.
 * Data comes from WooCommerce through window.PRODUCT (inc/product.php).
 */
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}

	$data     = psychart_pdp_data( $product );
	$crumb    = psychart_pdp_category_trail( $product );
	$desc     = $product->get_short_description();
	$desc     = $desc ? $desc : wp_trim_words( $product->get_description(), 40 );
	$desc     = trim( wp_strip_all_tags( $desc ) );
	$default  = isset( $data['colours'][ $data['defaultColour'] ] ) ? $data['colours'][ $data['defaultColour'] ] : array( 'name' => '' );
	$img_args = array(
		'class'         => 'gallery__img',
		'sizes'         => '(min-width: 820px) 50vw, 100vw',
		'loading'       => 'eager',
		'fetchpriority' => 'high',
	);
	$gallery  = array_filter( $product->get_gallery_image_ids() );

	$details = array(
		array( __( 'Materials & care', 'psychart' ), (string) get_post_meta( $product->get_id(), '_psychart_materials', true ), true ),
		array( __( 'Fit', 'psychart' ), (string) get_post_meta( $product->get_id(), '_psychart_fit', true ), false ),
		array( __( 'Delivery & returns', 'psychart' ), (string) get_post_meta( $product->get_id(), '_psychart_delivery', true ) ?: psychart_pdp_default_delivery(), false ),
	);
	?>
	<div class="psy-pdp">
		<?php do_action( 'woocommerce_before_single_product' ); ?>

		<div class="page" id="product-<?php the_ID(); ?>">
			<section class="gallery" aria-label="<?php esc_attr_e( 'Product image', 'psychart' ); ?>">
				<?php
				if ( $product->get_image_id() ) {
					echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_single', false, $img_args ); // phpcs:ignore WordPress.Security.EscapeOutput
				} else {
					printf( '<img class="gallery__img" src="%s" alt="">', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ) );
				}
				if ( apply_filters( 'psychart_pdp_extra_images', true ) ) {
					foreach ( $gallery as $gid ) {
						echo wp_get_attachment_image( $gid, 'woocommerce_single', false, array( 'class' => 'gallery__img', 'sizes' => $img_args['sizes'], 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
				?>
			</section>

			<section class="info">
				<?php if ( $crumb ) : ?><p class="crumb" id="crumb"><?php echo esc_html( $crumb ); ?></p><?php endif; ?>
				<h1 class="title" id="title"><?php the_title(); ?></h1>
				<p class="price" id="price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php if ( $desc ) : ?><p class="desc" id="desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>

				<?php if ( $data['hasColour'] ) : ?>
				<div class="field">
					<p class="label"><?php esc_html_e( 'Colour', 'psychart' ); ?> <span class="label__value" id="colour-name"><?php echo esc_html( $default['name'] ); ?></span></p>
					<div class="swatches" id="swatches" role="radiogroup" aria-label="<?php esc_attr_e( 'Colour', 'psychart' ); ?>"></div>
				</div>
				<?php endif; ?>

				<?php if ( $data['hasSize'] ) : ?>
				<div class="field">
					<p class="label"><?php esc_html_e( 'Size', 'psychart' ); ?> <span class="label__value" id="size-name"><?php esc_html_e( 'Select', 'psychart' ); ?></span></p>
					<div class="sizes" id="sizes" role="radiogroup" aria-label="<?php esc_attr_e( 'Size', 'psychart' ); ?>"></div>
					<?php if ( ! empty( $data['sizeChart'] ) ) : ?>
					<button type="button" class="find-size" id="find-size">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 17 17 3l4 4L7 21H3z"/><path d="m13 7 2 2M10 10l2 2M7 13l2 2"/></svg>
						<span><?php esc_html_e( 'Find my size', 'psychart' ); ?></span>
					</button>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<button type="button" class="add<?php echo $data['inStock'] ? '' : ' is-soldout'; ?>" id="add"><?php echo $data['inStock'] ? esc_html__( 'Add to bag', 'psychart' ) : esc_html__( 'Sold out', 'psychart' ); ?></button>
				<p class="status" id="status" role="status" aria-live="polite"></p>

				<div class="details" id="details">
					<?php foreach ( $details as $d ) : if ( '' === trim( $d[1] ) ) { continue; } ?>
						<details<?php echo $d[2] ? ' open' : ''; ?>><summary><?php echo esc_html( $d[0] ); ?></summary><p><?php echo nl2br( esc_html( $d[1] ) ); ?></p></details>
					<?php endforeach; ?>
				</div>
			</section>
		</div>

		<?php do_action( 'woocommerce_after_single_product' ); ?>
	</div>
	<?php
	// Product schema (JSON-LD), normally printed by the summary hooks this template does not use.
	if ( function_exists( 'WC' ) && WC()->structured_data && method_exists( WC()->structured_data, 'generate_product_data' ) ) {
		WC()->structured_data->generate_product_data();
	}
endwhile;

get_footer();
