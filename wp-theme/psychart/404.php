<?php
/** 404 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="psy-page psy-404">
	<div class="psy-container">
		<p class="psy-label"><?php esc_html_e( 'Error 404', 'psychart' ); ?></p>
		<h1 class="psy-display psy-404__title"><?php esc_html_e( 'Page not found', 'psychart' ); ?></h1>
		<p class="psy-pagehead__intro"><?php esc_html_e( 'The page you are looking for has moved, or never existed.', 'psychart' ); ?></p>
		<p class="psy-404__btns">
			<a class="psy-btn psy-btn--solid" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Shop All', 'psychart' ); ?> <?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<a class="psy-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'psychart' ); ?></a>
			<button type="button" class="psy-btn" data-psy-open="search"><?php esc_html_e( 'Search', 'psychart' ); ?></button>
		</p>
	</div>
</div>
<?php get_footer(); ?>
