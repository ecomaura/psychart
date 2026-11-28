<?php
/** Shipping & Returns (slug: shipping-returns). The copy below is a starting point: replace it in the page editor. */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
?>
<div class="psy-page psy-policy">
	<div class="psy-container">
		<?php psychart_page_head( __( 'Help', 'psychart' ), get_the_title() ); ?>

		<?php if ( psychart_has_editor_content() ) : ?>
			<div class="psy-prose psy-richtext"><?php the_content(); ?></div>
		<?php else : ?>
			<div class="psy-policy__grid">
				<section>
					<h2 class="psy-display psy-about__h"><?php esc_html_e( 'Shipping', 'psychart' ); ?></h2>
					<p><?php esc_html_e( 'Orders are packed within a few business days. Delivery options and costs are shown at checkout, and you will get a tracking email once your parcel is on its way.', 'psychart' ); ?></p>
				</section>
				<section>
					<h2 class="psy-display psy-about__h"><?php esc_html_e( '30-day returns', 'psychart' ); ?></h2>
					<p><?php esc_html_e( 'You have 30 days from delivery to return an item. It must be unworn, unwashed and have its tags attached.', 'psychart' ); ?></p>
				</section>
				<section>
					<h2 class="psy-display psy-about__h"><?php esc_html_e( 'How to return', 'psychart' ); ?></h2>
					<ol class="psy-steps">
						<li><?php esc_html_e( 'Contact us with your order number and the item you want to return.', 'psychart' ); ?></li>
						<li><?php esc_html_e( 'Pack it in its original packaging and send it to the address we reply with.', 'psychart' ); ?></li>
						<li><?php esc_html_e( 'Once it arrives and is checked, we refund the original payment method.', 'psychart' ); ?></li>
					</ol>
				</section>
				<section>
					<h2 class="psy-display psy-about__h"><?php esc_html_e( 'Wrong size?', 'psychart' ); ?></h2>
					<p><?php esc_html_e( 'Use “Find my size” on the product page before you order. If it still does not fit, we will help you exchange it.', 'psychart' ); ?></p>
				</section>
			</div>
		<?php endif; ?>

		<p class="psy-about__cta"><a class="psy-link-underline" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Questions? Contact us', 'psychart' ); ?></a></p>
	</div>
</div>
<?php get_footer(); ?>
