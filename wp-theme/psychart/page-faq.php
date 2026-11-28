<?php
/** FAQ page (slug: faq). Write in the page editor to replace the defaults, or use the psychart_faq_items filter. */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
?>
<div class="psy-page psy-faq">
	<div class="psy-container">
		<?php psychart_page_head( __( 'Help', 'psychart' ), get_the_title(), __( 'Quick answers. Still stuck? Get in touch.', 'psychart' ) ); ?>

		<?php if ( psychart_has_editor_content() ) : ?>
			<div class="psy-prose psy-richtext"><?php the_content(); ?></div>
		<?php else : ?>
			<div class="psy-faq__list">
				<?php foreach ( psychart_faq_items() as $item ) : ?>
					<details class="psy-faq__item">
						<summary><span><?php echo esc_html( $item[0] ); ?></span><?php echo psychart_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
						<div class="psy-faq__a"><?php echo wp_kses_post( wpautop( $item[1] ) ); ?></div>
					</details>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p class="psy-about__cta"><a class="psy-link-underline" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact us', 'psychart' ); ?></a></p>
	</div>
</div>
<?php get_footer(); ?>
