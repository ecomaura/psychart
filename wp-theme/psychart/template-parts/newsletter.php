<?php defined( 'ABSPATH' ) || exit; ?>
<section class="psy-news" aria-labelledby="psy-news-title">
	<div class="psy-container">
		<div class="psy-news__card">
			<p class="psy-label"><?php esc_html_e( 'Join the list', 'psychart' ); ?></p>
			<h2 class="psy-display psy-news__title" id="psy-news-title"><?php esc_html_e( 'Get Access', 'psychart' ); ?></h2>
			<p class="psy-news__copy"><?php esc_html_e( 'Be first to see new drops, limited restocks, and members-only releases from PsychArt.CO.', 'psychart' ); ?></p>
			<form class="psy-news__form" data-psy-news novalidate>
				<label class="psy-label" for="psy-news-email"><?php esc_html_e( 'Email address', 'psychart' ); ?></label>
				<input id="psy-news-email" class="psy-input" type="email" name="email" placeholder="<?php esc_attr_e( 'your@email.com', 'psychart' ); ?>" autocomplete="email" required>
				<input type="text" name="website" class="psy-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
				<button type="submit" class="psy-btn psy-btn--solid psy-btn--block"><span><?php esc_html_e( 'Join now', 'psychart' ); ?></span><?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<p class="psy-news__msg" data-psy-news-msg role="status"></p>
			</form>
		</div>
	</div>
</section>
