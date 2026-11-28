<?php defined( 'ABSPATH' ) || exit; ?>
<footer class="psy-footer">
	<div class="psy-container">
		<div class="psy-footer__brand">
			<a class="psy-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo psychart_brandmark(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<a class="psy-wordmark psy-wordmark--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">PSYCHART.CO</a>
			<p><?php echo esc_html( get_theme_mod( 'psychart_footer_tagline', __( 'Minimal streetwear for those who dress like identity is part of the art.', 'psychart' ) ) ); ?></p>
		</div>

		<div class="psy-footer__cols">
			<nav class="psy-footer__col" aria-labelledby="psy-f-shop">
				<h2 class="psy-label psy-label--strong" id="psy-f-shop"><?php esc_html_e( 'Shop', 'psychart' ); ?></h2>
				<?php psychart_render_links( 'footer_shop', 'psy-footer__list' ); ?>
			</nav>
			<nav class="psy-footer__col" aria-labelledby="psy-f-help">
				<h2 class="psy-label psy-label--strong" id="psy-f-help"><?php esc_html_e( 'Help', 'psychart' ); ?></h2>
				<?php psychart_render_links( 'footer_help', 'psy-footer__list' ); ?>
			</nav>
			<nav class="psy-footer__col" aria-labelledby="psy-f-company">
				<h2 class="psy-label psy-label--strong" id="psy-f-company"><?php esc_html_e( 'Company', 'psychart' ); ?></h2>
				<?php psychart_render_links( 'footer_company', 'psy-footer__list' ); ?>
			</nav>
		</div>

		<p class="psy-footer__legal">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> PSYCHART.CO &middot; <?php esc_html_e( 'Your alter ego', 'psychart' ); ?></p>
	</div>
</footer>
