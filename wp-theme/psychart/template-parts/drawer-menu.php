<?php defined( 'ABSPATH' ) || exit; ?>
<aside class="psy-drawer psy-drawer--left" id="psy-menu" role="dialog" aria-modal="true" aria-labelledby="psy-menu-title" aria-hidden="true" hidden>
	<div class="psy-drawer__head">
		<h2 class="psy-drawer__title" id="psy-menu-title"><?php esc_html_e( 'Menu', 'psychart' ); ?></h2>
		<button type="button" class="psy-iconbtn" data-psy-close aria-label="<?php esc_attr_e( 'Close menu', 'psychart' ); ?>"><?php echo psychart_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
	<nav class="psy-drawer__body" aria-label="<?php esc_attr_e( 'Menu', 'psychart' ); ?>">
		<?php psychart_render_links( 'primary', 'psy-menu-list' ); ?>
	</nav>
</aside>
