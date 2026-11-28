<?php
/** Fallback template. Home, shop, product and content templates arrive in later phases. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="psy-container psy-content">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'psy-entry' ); ?>>
				<h1 class="psy-h1"><?php the_title(); ?></h1>
				<div class="psy-prose"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<h1 class="psy-h1"><?php esc_html_e( 'Nothing here', 'psychart' ); ?></h1>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
