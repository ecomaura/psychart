<?php defined( 'ABSPATH' ) || exit; ?>
<aside class="psy-drawer psy-drawer--right" id="psy-bag" role="dialog" aria-modal="true" aria-labelledby="psy-bag-title" aria-hidden="true" hidden>
	<div class="psy-drawer__head">
		<h2 class="psy-drawer__title" id="psy-bag-title"><?php esc_html_e( 'Bag', 'psychart' ); ?> (<span class="psy-bag-count" data-count="<?php echo esc_attr( psychart_cart_count() ); ?>"><?php echo esc_html( psychart_cart_count() ); ?></span>)</h2>
		<button type="button" class="psy-iconbtn" data-psy-close aria-label="<?php esc_attr_e( 'Close bag', 'psychart' ); ?>"><?php echo psychart_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
	<div class="psy-drawer__body psy-bag-inner" aria-live="polite">
		<?php echo psychart_bag_inner_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</aside>
