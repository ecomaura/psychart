<?php
/**
 * Generic page. Also wraps the WooCommerce Cart, Checkout and My Account pages (shortcode content).
 */
defined( 'ABSPATH' ) || exit;
get_header();

$is_commerce = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
$label       = '';
if ( $is_commerce ) {
	if ( is_cart() ) {
		$label = __( 'Your bag', 'psychart' );
	} elseif ( is_checkout() ) {
		$label = is_wc_endpoint_url( 'order-received' ) ? __( 'Thank you', 'psychart' ) : __( 'Secure checkout', 'psychart' );
	} else {
		$label = is_user_logged_in() ? __( 'Account', 'psychart' ) : __( 'Welcome', 'psychart' );
	}
}
?>
<div class="psy-page <?php echo $is_commerce ? 'psy-page--commerce' : ''; ?>">
	<div class="psy-container">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php psychart_page_head( $label, get_the_title() ); ?>
			<div class="<?php echo $is_commerce ? 'psy-commerce-body' : 'psy-prose psy-richtext'; ?>">
				<?php the_content(); ?>
			</div>
		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
