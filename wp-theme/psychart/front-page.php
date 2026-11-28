<?php
/** Home page. Content is edited in Appearance > Customize > Home page. */
defined( 'ABSPATH' ) || exit;
get_header();

$shop  = psychart_shop_url();
$about = home_url( '/about/' );
?>

<div class="psy-home-top">
	<div class="psy-hero">
		<?php echo psychart_home_img( 'hero_image', 'hero.jpg', esc_attr( get_bloginfo( 'name' ) ), 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>

	<div class="psy-home-top__side">
		<section class="psy-tagline" aria-label="<?php esc_attr_e( 'Tagline', 'psychart' ); ?>">
			<div class="psy-tagline__thumb"><?php echo psychart_home_img( 'intro_thumb', 'jacket.jpg', '', 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="psy-tagline__card">
				<p class="psy-label"><?php echo esc_html( psychart_mod( 'intro_label' ) ); ?></p>
				<h2 class="psy-display psy-tagline__title"><?php echo psychart_mod_lines( 'intro_title' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="psy-label psy-tagline__note"><?php echo esc_html( psychart_mod( 'intro_note' ) ); ?></p>
			</div>
		</section>

		<section class="psy-alter">
			<p class="psy-label"><?php echo esc_html( psychart_mod( 'ae_eyebrow' ) ); ?></p>
			<h1 class="psy-display psy-alter__title"><?php echo esc_html( psychart_mod( 'ae_title' ) ); ?></h1>
			<p class="psy-alter__sub"><?php echo esc_html( psychart_mod( 'ae_sub' ) ); ?></p>
			<p class="psy-alter__body"><?php echo esc_html( psychart_mod( 'ae_body' ) ); ?></p>
			<div class="psy-alter__btns">
				<a class="psy-btn psy-btn--solid" href="<?php echo esc_url( psychart_home_link( 'ae_btn1_url', $shop ) ); ?>"><span><?php echo esc_html( psychart_mod( 'ae_btn1_label' ) ); ?></span><?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="psy-btn psy-btn--ghost" href="<?php echo esc_url( psychart_home_link( 'ae_btn2_url', $about ) ); ?>"><span><?php echo esc_html( psychart_mod( 'ae_btn2_label' ) ); ?></span><?php echo psychart_icon( 'arrow-ne' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
			<ol class="psy-values">
				<li><span class="psy-values__n">01</span><span class="psy-label"><?php echo esc_html( psychart_mod( 'val_1' ) ); ?></span></li>
				<li><span class="psy-values__n">02</span><span class="psy-label"><?php echo esc_html( psychart_mod( 'val_2' ) ); ?></span></li>
				<li><span class="psy-values__n">03</span><span class="psy-label"><?php echo esc_html( psychart_mod( 'val_3' ) ); ?></span></li>
				<li><span class="psy-values__n">04</span><span class="psy-label"><?php echo esc_html( psychart_mod( 'val_4' ) ); ?></span></li>
			</ol>
		</section>
	</div>
</div>

<section class="psy-uniform">
	<div class="psy-container">
		<header class="psy-uniform__head">
			<p class="psy-label"><?php echo esc_html( psychart_mod( 'uni_eyebrow' ) ); ?></p>
			<h2 class="psy-display psy-uniform__title"><?php echo esc_html( psychart_mod( 'uni_title' ) ); ?></h2>
			<p class="psy-uniform__copy"><?php echo esc_html( psychart_mod( 'uni_copy' ) ); ?></p>
		</header>
		<div class="psy-stories">
			<?php psychart_story_card( 1, 'jacket.jpg' ); ?>
			<?php psychart_story_card( 2, 'hero.jpg' ); ?>
			<?php psychart_story_card( 3, 'denim.jpg' ); ?>
		</div>
	</div>
</section>

<section class="psy-manifesto">
	<div class="psy-container psy-manifesto__grid">
		<header>
			<p class="psy-label"><?php echo esc_html( psychart_mod( 'man_eyebrow' ) ); ?></p>
			<h2 class="psy-display psy-manifesto__title"><?php echo psychart_mod_lines( 'man_title' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</header>
		<ul class="psy-manifesto__list">
			<li><h3 class="psy-display"><?php echo esc_html( psychart_mod( 'man_1_title' ) ); ?></h3><p><?php echo esc_html( psychart_mod( 'man_1_desc' ) ); ?></p></li>
			<li><h3 class="psy-display"><?php echo esc_html( psychart_mod( 'man_2_title' ) ); ?></h3><p><?php echo esc_html( psychart_mod( 'man_2_desc' ) ); ?></p></li>
			<li><h3 class="psy-display"><?php echo esc_html( psychart_mod( 'man_3_title' ) ); ?></h3><p><?php echo esc_html( psychart_mod( 'man_3_desc' ) ); ?></p></li>
			<li><h3 class="psy-display"><?php echo esc_html( psychart_mod( 'man_4_title' ) ); ?></h3><p><?php echo esc_html( psychart_mod( 'man_4_desc' ) ); ?></p></li>
		</ul>
	</div>
</section>

<?php get_footer(); ?>
