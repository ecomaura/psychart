<?php
/**
 * Shop, product category/tag archives and product search results.
 */
defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$total   = (int) $wp_query->found_posts;
$heading = psychart_archive_heading();
$active  = psychart_active_filter_count();
$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', 'date' ); // phpcs:ignore WordPress.Security.NonceVerification
$options = apply_filters( 'woocommerce_catalog_orderby', array() );
?>
<div class="psy-shop">
	<div class="psy-container">

		<header class="psy-shop__head">
			<h1 class="psy-display psy-shop__title"><?php echo esc_html( $heading[0] ); ?></h1>
			<p class="psy-shop__sub"><?php echo esc_html( $heading[1] ); ?></p>
		</header>

		<?php
		if ( function_exists( 'woocommerce_output_all_notices' ) ) {
			woocommerce_output_all_notices();
		}
		?>

		<div class="psy-toolbar">
			<button type="button" class="psy-filterbtn" data-psy-open="filters" aria-controls="psy-filters" aria-expanded="false">
				<?php echo psychart_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Filters', 'psychart' ); ?></span>
				<?php if ( $active ) : ?><span class="psy-filterbtn__n"><?php echo (int) $active; ?></span><?php endif; ?>
			</button>

			<form class="psy-sort" method="get" action="<?php echo esc_url( psychart_archive_base() ); ?>">
				<?php foreach ( psychart_current_args() as $k => $v ) : if ( 'orderby' === $k ) { continue; } ?>
					<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>">
				<?php endforeach; ?>
				<label class="psy-label" for="psy-orderby"><?php esc_html_e( 'Sort', 'psychart' ); ?></label>
				<span class="psy-select">
					<select id="psy-orderby" name="orderby" data-psy-sort>
						<?php foreach ( $options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $orderby, $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>
				<noscript><button type="submit" class="psy-btn psy-btn--ghost"><?php esc_html_e( 'Apply', 'psychart' ); ?></button></noscript>
			</form>
		</div>

		<?php if ( have_posts() ) : ?>
			<p class="psy-label psy-shop__count" aria-live="polite"><?php echo esc_html( sprintf( _n( '%d product', '%d products', $total, 'psychart' ), $total ) ); ?></p>

			<ul class="psy-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					wc_get_template_part( 'content', 'product' );
				}
				?>
			</ul>

			<?php
			$links = paginate_links( array(
				'type'      => 'array',
				'prev_text' => __( 'Prev', 'psychart' ),
				'next_text' => __( 'Next', 'psychart' ),
				'mid_size'  => 1,
			) );
			if ( $links ) :
				?>
				<nav class="psy-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'psychart' ); ?>">
					<ul>
						<?php foreach ( $links as $l ) : ?><li><?php echo $l; // phpcs:ignore WordPress.Security.EscapeOutput ?></li><?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

		<?php else : ?>
			<div class="psy-empty">
				<?php echo psychart_icon( 'box' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( is_search() ) : ?>
					<p><?php esc_html_e( 'No products found for that search', 'psychart' ); ?></p>
					<a class="psy-link-underline" href="<?php echo esc_url( psychart_shop_url() ); ?>"><?php esc_html_e( 'Browse all products', 'psychart' ); ?></a>
				<?php elseif ( $active ) : ?>
					<p><?php esc_html_e( 'No products match these filters', 'psychart' ); ?></p>
					<a class="psy-link-underline" href="<?php echo esc_url( psychart_clear_url() ); ?>"><?php esc_html_e( 'Clear all filters', 'psychart' ); ?></a>
				<?php else : ?>
					<p><?php esc_html_e( 'No products available yet', 'psychart' ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php get_template_part( 'template-parts/shop-filters', null, array( 'total' => $total, 'active' => $active ) ); ?>

<?php get_footer(); ?>
