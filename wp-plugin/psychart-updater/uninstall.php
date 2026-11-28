<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'psychart_updater_opts' );
delete_option( 'psychart_updater_diag' );
delete_site_transient( 'psychart_updater_latest' );
