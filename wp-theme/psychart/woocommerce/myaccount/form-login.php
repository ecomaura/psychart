<?php
/**
 * Sign in / Register. Overrides woocommerce/myaccount/form-login.php
 *
 * @version 9.9.0
 */
defined( 'ABSPATH' ) || exit;

$can_register = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$posted_user  = isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$posted_email = isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="psy-auth <?php echo $can_register ? 'psy-auth--two' : ''; ?>">

	<section class="psy-auth__panel">
		<h2 class="psy-display psy-auth__title"><?php esc_html_e( 'Sign in', 'psychart' ); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="psy-field">
				<label class="psy-label" for="username"><?php esc_html_e( 'Username or email', 'psychart' ); ?></label>
				<input type="text" class="psy-input" name="username" id="username" autocomplete="username" value="<?php echo $posted_user; // phpcs:ignore WordPress.Security.EscapeOutput ?>" required aria-required="true">
			</p>
			<p class="psy-field">
				<label class="psy-label" for="password"><?php esc_html_e( 'Password', 'psychart' ); ?></label>
				<input class="psy-input" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true">
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="psy-check">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <span><?php esc_html_e( 'Remember me', 'psychart' ); ?></span></label>
			</p>
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<button type="submit" class="psy-btn psy-btn--solid psy-btn--block" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Sign in', 'psychart' ); ?> <?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<p class="psy-auth__lost"><a class="psy-link-underline" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'psychart' ); ?></a></p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</section>

	<?php if ( $can_register ) : ?>
		<section class="psy-auth__panel psy-auth__panel--alt">
			<h2 class="psy-display psy-auth__title"><?php esc_html_e( 'Create account', 'psychart' ); ?></h2>

			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<p class="psy-field">
						<label class="psy-label" for="reg_username"><?php esc_html_e( 'Username', 'psychart' ); ?></label>
						<input type="text" class="psy-input" name="username" id="reg_username" autocomplete="username" value="<?php echo $posted_user; // phpcs:ignore WordPress.Security.EscapeOutput ?>" required aria-required="true">
					</p>
				<?php endif; ?>

				<p class="psy-field">
					<label class="psy-label" for="reg_email"><?php esc_html_e( 'Email', 'psychart' ); ?></label>
					<input type="email" class="psy-input" name="email" id="reg_email" autocomplete="email" value="<?php echo $posted_email; // phpcs:ignore WordPress.Security.EscapeOutput ?>" required aria-required="true">
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="psy-field">
						<label class="psy-label" for="reg_password"><?php esc_html_e( 'Password', 'psychart' ); ?></label>
						<input type="password" class="psy-input" name="password" id="reg_password" autocomplete="new-password" required aria-required="true">
					</p>
				<?php else : ?>
					<p class="psy-auth__note"><?php esc_html_e( 'A link to set your password will be sent to your email.', 'psychart' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="psy-btn psy-btn--block" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Create account', 'psychart' ); ?></button>

				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		</section>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
