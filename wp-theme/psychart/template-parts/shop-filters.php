<?php
/** Filters drawer for the shop. Every option is a plain link/form, so it works without JS. */
defined( 'ABSPATH' ) || exit;

$args    = wp_parse_args( $args ?? array(), array( 'total' => 0, 'active' => 0 ) );
$current = psychart_current_args();
$cats    = psychart_category_list();
$attrs   = psychart_filter_attributes();
$shop    = psychart_shop_url();
?>
<aside class="psy-drawer psy-drawer--left" id="psy-filters" role="dialog" aria-modal="true" aria-labelledby="psy-filters-title" aria-hidden="true" hidden>
	<div class="psy-drawer__head">
		<h2 class="psy-drawer__title" id="psy-filters-title"><?php esc_html_e( 'Filters', 'psychart' ); ?></h2>
		<button type="button" class="psy-iconbtn" data-psy-close aria-label="<?php esc_attr_e( 'Close filters', 'psychart' ); ?>"><?php echo psychart_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>

	<div class="psy-drawer__body psy-filters">

		<section class="psy-filter">
			<h3 class="psy-label psy-label--strong"><?php esc_html_e( 'Category', 'psychart' ); ?></h3>
			<ul class="psy-filter__list">
				<li><a href="<?php echo esc_url( add_query_arg( array_intersect_key( $current, array_flip( array( 'orderby' ) ) ), $shop ) ); ?>" class="<?php echo ( function_exists( 'is_product_category' ) && is_product_category() ) ? '' : 'is-active'; ?>"><?php esc_html_e( 'All Products', 'psychart' ); ?></a></li>
				<?php foreach ( $cats as $t ) :
					$is = function_exists( 'is_product_category' ) && is_product_category() && get_queried_object_id() === $t->term_id;
					?>
					<li><a href="<?php echo esc_url( add_query_arg( array_intersect_key( $current, array_flip( array( 'orderby' ) ) ), get_term_link( $t ) ) ); ?>" class="<?php echo $is ? 'is-active' : ''; ?>" <?php echo $is ? 'aria-current="true"' : ''; ?>>
						<?php echo $t->parent ? '— ' : ''; echo esc_html( $t->name ); ?> <span class="psy-filter__count"><?php echo (int) $t->count; ?></span>
					</a></li>
				<?php endforeach; ?>
			</ul>
		</section>

		<?php foreach ( $attrs as $name => $label ) :
			$terms = get_terms( array( 'taxonomy' => 'pa_' . $name, 'hide_empty' => true ) );
			if ( is_wp_error( $terms ) || ! $terms ) { continue; }
			$selected = ! empty( $current[ 'filter_' . $name ] ) ? explode( ',', $current[ 'filter_' . $name ] ) : array();
			?>
			<section class="psy-filter">
				<h3 class="psy-label psy-label--strong"><?php echo esc_html( $label ); ?></h3>
				<ul class="psy-chips">
					<?php foreach ( $terms as $t ) : $on = in_array( $t->slug, $selected, true ); ?>
						<li><a class="psy-chip <?php echo $on ? 'is-active' : ''; ?>" href="<?php echo esc_url( psychart_attr_toggle_url( $name, $t->slug ) ); ?>" aria-pressed="<?php echo $on ? 'true' : 'false'; ?>"><?php echo esc_html( $t->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>

		<section class="psy-filter">
			<h3 class="psy-label psy-label--strong"><?php esc_html_e( 'Price', 'psychart' ); ?></h3>
			<form class="psy-price" method="get" action="<?php echo esc_url( psychart_archive_base() ); ?>">
				<?php foreach ( $current as $k => $v ) : if ( in_array( $k, array( 'min_price', 'max_price' ), true ) ) { continue; } ?>
					<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>">
				<?php endforeach; ?>
				<label class="screen-reader-text" for="psy-min"><?php esc_html_e( 'Minimum price', 'psychart' ); ?></label>
				<input class="psy-input" id="psy-min" type="number" min="0" step="1" inputmode="numeric" name="min_price" placeholder="<?php esc_attr_e( 'Min', 'psychart' ); ?>" value="<?php echo esc_attr( $current['min_price'] ?? '' ); ?>">
				<span aria-hidden="true">&ndash;</span>
				<label class="screen-reader-text" for="psy-max"><?php esc_html_e( 'Maximum price', 'psychart' ); ?></label>
				<input class="psy-input" id="psy-max" type="number" min="0" step="1" inputmode="numeric" name="max_price" placeholder="<?php esc_attr_e( 'Max', 'psychart' ); ?>" value="<?php echo esc_attr( $current['max_price'] ?? '' ); ?>">
				<button type="submit" class="psy-btn psy-btn--ghost"><?php esc_html_e( 'Apply', 'psychart' ); ?></button>
			</form>
		</section>

		<section class="psy-filter">
			<h3 class="psy-label psy-label--strong"><?php esc_html_e( 'Availability', 'psychart' ); ?></h3>
			<?php $in = isset( $current['instock'] ); ?>
			<ul class="psy-chips">
				<li><a class="psy-chip <?php echo $in ? 'is-active' : ''; ?>" href="<?php echo esc_url( psychart_url( array( 'instock' => $in ? null : '1' ) ) ); ?>" aria-pressed="<?php echo $in ? 'true' : 'false'; ?>"><?php esc_html_e( 'In stock only', 'psychart' ); ?></a></li>
			</ul>
		</section>
	</div>

	<div class="psy-filters__foot">
		<?php if ( $args['active'] ) : ?>
			<a class="psy-link-underline" href="<?php echo esc_url( psychart_clear_url() ); ?>"><?php esc_html_e( 'Clear all', 'psychart' ); ?></a>
		<?php endif; ?>
		<button type="button" class="psy-btn psy-btn--solid" data-psy-close><?php echo esc_html( sprintf( _n( 'View %d product', 'View %d products', (int) $args['total'], 'psychart' ), (int) $args['total'] ) ); ?></button>
	</div>
</aside>
