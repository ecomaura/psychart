<?php defined( 'ABSPATH' ) || exit; ?>
<div class="psy-search" id="psy-search" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search', 'psychart' ); ?>" aria-hidden="true" hidden>
	<div class="psy-search__bar">
		<form role="search" method="get" class="psy-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="hidden" name="post_type" value="product">
			<label class="screen-reader-text" for="psy-search-input"><?php esc_html_e( 'Search products', 'psychart' ); ?></label>
			<input id="psy-search-input" class="psy-search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products...', 'psychart' ); ?>" autocomplete="off" enterkeyhint="search">
		</form>
		<button type="button" class="psy-iconbtn" data-psy-close aria-label="<?php esc_attr_e( 'Close search', 'psychart' ); ?>"><?php echo psychart_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
	<div class="psy-search__body">
		<div class="psy-search__status" aria-live="polite">
			<svg class="psy-icon psy-icon--lg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/></svg>
			<p data-psy-search-msg><?php esc_html_e( 'Start typing to search products', 'psychart' ); ?></p>
		</div>
		<ul class="psy-search__results" data-psy-search-results></ul>
		<a class="psy-link-underline psy-search__more" data-psy-search-more href="#" hidden><?php esc_html_e( 'View all results', 'psychart' ); ?></a>
	</div>
</div>
