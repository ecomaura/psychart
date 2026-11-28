<?php
/**
 * One-click demo product import.
 *
 * Adds Appearance > Import Demo Products. Runs WooCommerce's own CSV importer
 * on demo/demo-products.csv, so it creates the same 3 variable products
 * (with variations, size charts and demo images) as Products > Import does.
 * Products whose SKU already exists are skipped, so it is safe to click twice.
 */
defined( 'ABSPATH' ) || exit;

/**
 * CSV heading => WooCommerce importer field. Keep in sync with demo/demo-products.csv.
 */
function psychart_demo_import_mapping() {
	return array(
		'ID'                        => 'id',
		'Type'                      => 'type',
		'SKU'                       => 'sku',
		'Name'                      => 'name',
		'Published'                 => 'published',
		'Is featured?'              => 'featured',
		'Visibility in catalog'     => 'catalog_visibility',
		'Short description'         => 'short_description',
		'Description'               => 'description',
		'Tax status'                => 'tax_status',
		'In stock?'                 => 'stock_status',
		'Stock'                     => 'stock_quantity',
		'Regular price'             => 'regular_price',
		'Categories'                => 'category_ids',
		'Images'                    => 'images',
		'Parent'                    => 'parent_id',
		'Attribute 1 name'          => 'attributes:name1',
		'Attribute 1 value(s)'      => 'attributes:value1',
		'Attribute 1 visible'       => 'attributes:visible1',
		'Attribute 1 global'        => 'attributes:taxonomy1',
		'Attribute 2 name'          => 'attributes:name2',
		'Attribute 2 value(s)'      => 'attributes:value2',
		'Attribute 2 visible'       => 'attributes:visible2',
		'Attribute 2 global'        => 'attributes:taxonomy2',
		'Meta: _psychart_chart_unit' => 'meta:_psychart_chart_unit',
		'Meta: _psychart_chart_rows' => 'meta:_psychart_chart_rows',
		'Meta: _psychart_materials'  => 'meta:_psychart_materials',
		'Meta: _psychart_fit'        => 'meta:_psychart_fit',
	);
}

/**
 * Run the import. Returns array( imported, variations, skipped, failed, errors[] ) or WP_Error.
 */
function psychart_run_demo_import() {
	$file = PSYCHART_DIR . '/demo/demo-products.csv';
	if ( ! is_readable( $file ) ) {
		return new WP_Error( 'psychart_demo_missing', __( 'demo/demo-products.csv was not found in the theme.', 'psychart' ) );
	}

	if ( ! defined( 'WC_ABSPATH' ) || ! file_exists( WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php' ) ) {
		return new WP_Error( 'psychart_demo_wc', __( 'WooCommerce is not available.', 'psychart' ) );
	}

	include_once WC_ABSPATH . 'includes/import/abstract-wc-product-importer.php';
	include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( 'admin' );
	}
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

	$importer = new WC_Product_CSV_Importer(
		$file,
		array(
			'mapping'          => psychart_demo_import_mapping(),
			'parse'            => true,
			'update_existing'  => false,
			'prevent_timeouts' => false,
		)
	);
	$data = $importer->import();

	$errors = array();
	foreach ( array( 'failed', 'skipped' ) as $bucket ) {
		foreach ( (array) $data[ $bucket ] as $err ) {
			if ( is_wp_error( $err ) ) {
				$errors[] = $err->get_error_message();
			}
		}
	}

	return array(
		'imported'   => count( $data['imported'] ),
		'variations' => count( $data['imported_variations'] ),
		'updated'    => count( $data['updated'] ),
		'skipped'    => count( $data['skipped'] ),
		'failed'     => count( $data['failed'] ),
		'errors'     => array_values( array_unique( $errors ) ),
	);
}

add_action( 'admin_menu', function () {
	add_theme_page(
		__( 'Import Demo Products', 'psychart' ),
		__( 'Import Demo Products', 'psychart' ),
		'manage_woocommerce',
		'psychart-demo-import',
		'psychart_demo_import_page'
	);
} );

function psychart_demo_import_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to do this.', 'psychart' ) );
	}

	echo '<div class="wrap"><h1>' . esc_html__( 'Import Demo Products', 'psychart' ) . '</h1>';

	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Activate WooCommerce first.', 'psychart' ) . '</p></div></div>';
		return;
	}

	if ( isset( $_POST['psychart_demo_import'] ) ) {
		check_admin_referer( 'psychart_demo_import' );
		$r = psychart_run_demo_import();

		if ( is_wp_error( $r ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $r->get_error_message() ) );
		} else {
			$class = $r['failed'] ? 'notice-warning' : 'notice-success';
			$msg   = sprintf(
				/* translators: 1: products, 2: variations, 3: skipped, 4: failed */
				__( 'Created %1$d products and %2$d variations. Skipped %3$d (already exist). Failed %4$d.', 'psychart' ),
				$r['imported'],
				$r['variations'],
				$r['skipped'],
				$r['failed']
			);
			printf( '<div class="notice %s"><p>%s</p>', esc_attr( $class ), esc_html( $msg ) );
			if ( $r['errors'] ) {
				echo '<ul style="list-style:disc;margin-left:20px">';
				foreach ( $r['errors'] as $e ) {
					echo '<li>' . esc_html( $e ) . '</li>';
				}
				echo '</ul>';
			}
			if ( $r['imported'] ) {
				printf( '<p><a href="%s">%s</a></p>', esc_url( admin_url( 'edit.php?post_type=product' ) ), esc_html__( 'View products', 'psychart' ) );
			}
			echo '</div>';
		}
	}

	echo '<p>' . esc_html__( 'Creates 3 sample products (Dual-tone Denim Jacket, Editorial Set, Washed Flare Denim) with Colour and Size variations, size charts and demo images. Existing products with the same SKU are skipped. Images are downloaded from the theme GitHub repo, so this server needs internet access.', 'psychart' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'psychart_demo_import' );
	submit_button( __( 'Import demo products', 'psychart' ), 'primary', 'psychart_demo_import' );
	echo '</form></div>';
}
