<?php
/** Contact page (slug: contact). Messages go to Customize > About page > Contact form recipient (default: admin email). */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();

$sent = isset( $_GET['sent'] ); // phpcs:ignore WordPress.Security.NonceVerification
$err  = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$errs = array(
	'invalid' => __( 'Please fill in your name, a valid email and a message.', 'psychart' ),
	'wait'    => __( 'Please wait a minute before sending another message.', 'psychart' ),
	'expired' => __( 'That form expired. Please try again.', 'psychart' ),
	'mail'    => __( 'We could not send your message. Please try again later.', 'psychart' ),
);
?>
<div class="psy-page psy-contact">
	<div class="psy-container">
		<?php psychart_page_head( __( 'Help', 'psychart' ), get_the_title(), __( 'Questions about an order, sizing or anything else. We read everything.', 'psychart' ) ); ?>

		<div class="psy-contact__grid">
			<div class="psy-contact__side">
				<?php if ( psychart_has_editor_content() ) : ?>
					<div class="psy-prose psy-richtext"><?php the_content(); ?></div>
				<?php else : ?>
					<p class="psy-label"><?php esc_html_e( 'Before you write', 'psychart' ); ?></p>
					<ul class="psy-menu-list psy-contact__links">
						<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQ', 'psychart' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/shipping-returns/' ) ); ?>"><?php esc_html_e( 'Shipping & Returns', 'psychart' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="psy-contact__form" id="contact-form">
				<?php if ( $sent ) : ?>
					<p class="psy-notice psy-notice--ok" role="status"><?php esc_html_e( 'Thank you. Your message is on its way.', 'psychart' ); ?></p>
				<?php elseif ( $err && isset( $errs[ $err ] ) ) : ?>
					<p class="psy-notice psy-notice--err" role="alert"><?php echo esc_html( $errs[ $err ] ); ?></p>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="psychart_contact">
					<?php wp_nonce_field( 'psychart_contact' ); ?>
					<div class="psy-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

					<p class="psy-field">
						<label class="psy-label" for="c-name"><?php esc_html_e( 'Name', 'psychart' ); ?></label>
						<input class="psy-input" type="text" id="c-name" name="name" required autocomplete="name">
					</p>
					<p class="psy-field">
						<label class="psy-label" for="c-email"><?php esc_html_e( 'Email', 'psychart' ); ?></label>
						<input class="psy-input" type="email" id="c-email" name="email" required autocomplete="email">
					</p>
					<p class="psy-field">
						<label class="psy-label" for="c-msg"><?php esc_html_e( 'Message', 'psychart' ); ?></label>
						<textarea class="psy-input psy-textarea" id="c-msg" name="message" rows="6" required></textarea>
					</p>
					<button type="submit" class="psy-btn psy-btn--solid psy-btn--block"><?php esc_html_e( 'Send message', 'psychart' ); ?> <?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
