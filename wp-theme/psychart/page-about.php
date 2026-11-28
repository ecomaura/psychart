<?php
/** About page (slug: about). Text and stats are editable in Customize > About page. */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
?>
<div class="psy-page psy-about">
	<div class="psy-container">
		<?php psychart_page_head( __( 'The label', 'psychart' ), get_the_title(), psychart_about( 'intro' ) ); ?>

		<div class="psy-about__grid">
			<section class="psy-about__block">
				<span class="psy-values__n">01</span>
				<h2 class="psy-display psy-about__h"><?php echo esc_html( psychart_about( 'phil_title' ) ); ?></h2>
				<p><?php echo esc_html( psychart_about( 'phil_text' ) ); ?></p>
			</section>
			<section class="psy-about__block">
				<span class="psy-values__n">02</span>
				<h2 class="psy-display psy-about__h"><?php echo esc_html( psychart_about( 'sust_title' ) ); ?></h2>
				<p><?php echo esc_html( psychart_about( 'sust_text' ) ); ?></p>
			</section>
		</div>

		<ul class="psy-stats">
			<?php foreach ( array( 1, 2, 3 ) as $i ) : ?>
				<li>
					<span class="psy-display psy-stats__v"><?php echo esc_html( psychart_about( "stat{$i}_value" ) ); ?></span>
					<span class="psy-label"><?php echo esc_html( psychart_about( "stat{$i}_label" ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( psychart_has_editor_content() ) : ?>
			<div class="psy-prose psy-richtext psy-about__extra"><?php the_content(); ?></div>
		<?php endif; ?>

		<p class="psy-about__cta"><a class="psy-btn psy-btn--solid" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Shop All', 'psychart' ); ?> <?php echo psychart_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
	</div>
</div>
<?php get_footer(); ?>
