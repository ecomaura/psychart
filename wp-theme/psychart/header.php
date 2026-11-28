<?php defined( 'ABSPATH' ) || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="psy-skip" href="#main"><?php esc_html_e( 'Skip to content', 'psychart' ); ?></a>

<header class="psy-header" id="psy-header">
	<div class="psy-header__inner">
		<button type="button" class="psy-iconbtn psy-header__menu" data-psy-open="menu" aria-label="<?php esc_attr_e( 'Open menu', 'psychart' ); ?>" aria-controls="psy-menu" aria-expanded="false">
			<?php echo psychart_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</button>

		<a class="psy-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php
			if ( has_custom_logo() ) {
				echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'psy-wordmark__img' ) );
			} else {
				echo psychart_brandmark(); // phpcs:ignore WordPress.Security.EscapeOutput
				echo '<span class="psy-wordmark__text">PSYCHART.CO</span>';
			}
			?>
		</a>

		<div class="psy-header__actions">
			<button type="button" class="psy-iconbtn psy-theme-toggle" data-psy-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e( 'Dark theme', 'psychart' ); ?>" title="<?php esc_attr_e( 'Switch theme', 'psychart' ); ?>">
				<?php echo psychart_icon( 'sun' ) . psychart_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<button type="button" class="psy-iconbtn" data-psy-open="search" aria-label="<?php esc_attr_e( 'Search', 'psychart' ); ?>" aria-controls="psy-search" aria-expanded="false">
				<?php echo psychart_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<button type="button" class="psy-iconbtn psy-header__bag" data-psy-open="bag" aria-label="<?php esc_attr_e( 'Open bag', 'psychart' ); ?>" aria-controls="psy-bag" aria-expanded="false">
				<?php echo psychart_icon( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="psy-bag-count" data-count="<?php echo esc_attr( psychart_cart_count() ); ?>"><?php echo esc_html( psychart_cart_count() ); ?></span>
			</button>
		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/drawer-menu' ); ?>
<?php get_template_part( 'template-parts/overlay-search' ); ?>
<?php get_template_part( 'template-parts/drawer-bag' ); ?>
<div class="psy-scrim" data-psy-close hidden></div>

<main id="main" class="psy-main" tabindex="-1">
