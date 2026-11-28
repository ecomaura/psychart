<?php defined( 'ABSPATH' ) || exit; ?>
</main>

<?php
if ( apply_filters( 'psychart_show_newsletter', true ) ) {
	get_template_part( 'template-parts/newsletter' );
}
get_template_part( 'template-parts/site-footer' );
wp_footer();
?>
</body>
</html>
