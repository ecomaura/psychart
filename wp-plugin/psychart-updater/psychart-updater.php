<?php
/**
 * Plugin Name:       PSYCHART Theme Updater
 * Description:       Updates the PSYCHART.CO theme from GitHub Releases. Works without any code in the theme, falls back when the GitHub API is rate limited or blocked, and shows exactly why a check failed.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            PSYCHART.CO
 * License:           GPL-2.0-or-later
 * Text Domain:       psychart-updater
 */
defined( 'ABSPATH' ) || exit;

final class Psychart_Updater {

	const VERSION = '1.0.0';
	const OPT     = 'psychart_updater_opts';
	const CACHE   = 'psychart_updater_latest';
	const DIAG    = 'psychart_updater_diag';
	const PAGE    = 'psychart-updater';

	public static function init() {
		add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'filter_check' ) );
		add_filter( 'site_transient_update_themes', array( __CLASS__, 'filter_read' ) );
		add_filter( 'upgrader_source_selection', array( __CLASS__, 'fix_source_dir' ), 10, 4 );
		add_filter( 'auto_update_theme', array( __CLASS__, 'auto_update' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'after_upgrade' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
			add_action( 'admin_post_psychart_updater_save', array( __CLASS__, 'handle_save' ) );
			add_action( 'admin_post_psychart_updater_check', array( __CLASS__, 'handle_check' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
		}
	}

	/* ---------- Settings ---------- */

	public static function opts() {
		$o = get_option( self::OPT, array() );
		return wp_parse_args( is_array( $o ) ? $o : array(), array(
			'repo'  => 'ecomaura/psychart',
			'slug'  => '',
			'token' => '',
			'auto'  => 0,
		) );
	}

	public static function repo() {
		$r = self::opts()['repo'];
		return preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $r ) ? $r : 'ecomaura/psychart';
	}

	/** Theme folder to update: the saved one, else the installed theme named PSYCHART.CO / text domain psychart. */
	public static function theme_slug() {
		static $slug = null;
		if ( null !== $slug ) {
			return $slug;
		}
		$slug  = '';
		$saved = preg_replace( '/[^A-Za-z0-9._-]/', '', (string) self::opts()['slug'] );
		if ( $saved && wp_get_theme( $saved )->exists() ) {
			return $slug = $saved;
		}
		foreach ( wp_get_themes() as $dir => $theme ) {
			if ( 'psychart' === $theme->get( 'TextDomain' ) || 'PSYCHART.CO' === $theme->get( 'Name' ) ) {
				return $slug = $dir;
			}
		}
		return $slug;
	}

	public static function installed_version() {
		$slug = self::theme_slug();
		return $slug ? (string) wp_get_theme( $slug )->get( 'Version' ) : '';
	}

	/* ---------- Talking to GitHub ---------- */

	private static function http( $method, $url, array $args, array &$log, $label ) {
		$args            = wp_parse_args( $args, array( 'timeout' => 10, 'redirection' => 5, 'headers' => array() ) );
		$args['headers'] = array_merge( array( 'User-Agent' => 'psychart-theme-updater/' . self::VERSION ), $args['headers'] );
		$res             = ( 'HEAD' === $method ) ? wp_remote_head( $url, $args ) : wp_remote_get( $url, $args );

		if ( is_wp_error( $res ) ) {
			$log[] = array( 'label' => $label, 'url' => $url, 'ok' => false, 'detail' => $res->get_error_message() );
			return null;
		}
		$code  = (int) wp_remote_retrieve_response_code( $res );
		$log[] = array( 'label' => $label, 'url' => $url, 'ok' => ( $code >= 200 && $code < 400 ), 'detail' => 'HTTP ' . $code );
		return $res;
	}

	private static function mark( array &$log, $ok, $detail ) {
		$i = count( $log ) - 1;
		if ( $i >= 0 ) {
			$log[ $i ]['ok']     = $ok;
			$log[ $i ]['detail'] = $detail;
		}
	}

	private static function build( $repo, $tag, $package, $url, $source ) {
		$version = ltrim( (string) $tag, 'vV' );
		if ( ! preg_match( '/^\d+(\.\d+){1,3}([-+][0-9A-Za-z.-]+)?$/', $version ) ) {
			return null;
		}
		$name = explode( '/', $repo )[1];
		$base = 'https://github.com/' . $repo . '/releases';
		if ( ! $package || 0 !== strpos( $package, 'https://github.com/' . $repo . '/' ) ) {
			$package = $base . '/download/' . rawurlencode( $tag ) . '/' . $name . '-' . $version . '.zip';
		}
		if ( ! $url || 0 !== strpos( $url, 'https://github.com/' ) ) {
			$url = $base . '/tag/' . rawurlencode( $tag );
		}
		return array( 'version' => $version, 'tag' => (string) $tag, 'package' => $package, 'url' => esc_url_raw( $url ), 'source' => $source );
	}

	/** 1) GitHub API (exact asset, but limited to 60 calls/hour per IP when anonymous). */
	private static function via_api( $repo, array &$log ) {
		$headers = array( 'Accept' => 'application/vnd.github+json' );
		$token   = self::opts()['token'];
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}
		$res = self::http( 'GET', 'https://api.github.com/repos/' . $repo . '/releases/latest', array( 'headers' => $headers ), $log, 'GitHub API' );
		if ( ! $res ) {
			return null;
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		if ( 200 !== $code ) {
			self::mark( $log, false, 'HTTP ' . $code . ( in_array( $code, array( 403, 429 ), true ) ? ' (rate limited: add a token in the settings below, or rely on the fallbacks)' : '' ) );
			return null;
		}
		$j = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $j ) || empty( $j['tag_name'] ) ) {
			self::mark( $log, false, 'Unexpected response' );
			return null;
		}
		$name    = explode( '/', $repo )[1];
		$package = '';
		foreach ( (array) ( $j['assets'] ?? array() ) as $a ) {
			if ( isset( $a['name'], $a['browser_download_url'] ) && preg_match( '/^' . preg_quote( $name, '/' ) . '.*\.zip$/', $a['name'] ) ) {
				$package = (string) $a['browser_download_url'];
				break;
			}
		}
		$r = self::build( $repo, $j['tag_name'], $package, $j['html_url'] ?? '', 'GitHub API' );
		if ( ! $r ) {
			self::mark( $log, false, 'Latest tag is not a version number: ' . $j['tag_name'] );
		} else {
			self::mark( $log, true, 'HTTP 200, latest ' . $r['version'] );
		}
		return $r;
	}

	/** 2) github.com/<repo>/releases/latest redirects to the newest tag. No API, no rate limit. */
	private static function via_redirect( $repo, array &$log ) {
		$res = self::http( 'HEAD', 'https://github.com/' . $repo . '/releases/latest', array( 'redirection' => 0 ), $log, 'Latest-release redirect' );
		if ( ! $res ) {
			return null;
		}
		$loc = wp_remote_retrieve_header( $res, 'location' );
		$loc = is_array( $loc ) ? (string) reset( $loc ) : (string) $loc;
		if ( ! preg_match( '#/releases/tag/([^/?\#]+)$#', $loc, $m ) ) {
			self::mark( $log, false, 'No redirect to a release (HTTP ' . (int) wp_remote_retrieve_response_code( $res ) . '). Does the repo have a published release?' );
			return null;
		}
		$r = self::build( $repo, rawurldecode( $m[1] ), '', '', 'Redirect' );
		self::mark( $log, (bool) $r, $r ? 'Latest ' . $r['version'] : 'Latest tag is not a version number' );
		return $r;
	}

	/** 3) The releases Atom feed. */
	private static function via_atom( $repo, array &$log ) {
		$res = self::http( 'GET', 'https://github.com/' . $repo . '/releases.atom', array(), $log, 'Releases feed' );
		if ( ! $res || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			return null;
		}
		if ( ! preg_match( '#<link[^>]+href="[^"]*/releases/tag/([^"/]+)"#', wp_remote_retrieve_body( $res ), $m ) ) {
			self::mark( $log, false, 'No releases found in the feed' );
			return null;
		}
		$r = self::build( $repo, rawurldecode( $m[1] ), '', '', 'Feed' );
		self::mark( $log, (bool) $r, $r ? 'Latest ' . $r['version'] : 'Latest tag is not a version number' );
		return $r;
	}

	/** Try the methods in order. With $all, run every method (for the diagnostics table). */
	public static function fetch_latest( $all = false ) {
		$repo    = self::repo();
		$log     = array();
		$release = null;
		foreach ( array( 'via_api', 'via_redirect', 'via_atom' ) as $method ) {
			if ( $release && ! $all ) {
				break;
			}
			$r = self::$method( $repo, $log );
			if ( $r && ! $release ) {
				$release = $r;
			}
		}
		update_option( self::DIAG, array(
			'time'   => time(),
			'repo'   => $repo,
			'ok'     => (bool) $release,
			'source' => $release ? $release['source'] : '',
			'log'    => $log,
		), false );
		return $release;
	}

	public static function refresh( $all = false ) {
		$r = self::fetch_latest( $all );
		set_site_transient( self::CACHE, $r ? $r : 'fail', $r ? 12 * HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS );
		return $r;
	}

	public static function cached() {
		$c = get_site_transient( self::CACHE );
		return is_array( $c ) ? $c : null;
	}

	/* ---------- WordPress update plumbing ---------- */

	private static function apply( $transient, $release ) {
		$slug = self::theme_slug();
		if ( ! $slug || ! $release || ! is_object( $transient ) ) {
			return $transient;
		}
		if ( version_compare( $release['version'], self::installed_version(), '>' ) ) {
			if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}
			$transient->response[ $slug ] = array(
				'theme'        => $slug,
				'new_version'  => $release['version'],
				'url'          => $release['url'],
				'package'      => $release['package'],
				'requires'     => '6.4',
				'requires_php' => '7.4',
			);
			if ( isset( $transient->no_update ) && is_array( $transient->no_update ) ) {
				unset( $transient->no_update[ $slug ] );
			}
		}
		return $transient;
	}

	/** Runs when WordPress rebuilds its theme update list (cron, Dashboard > Updates). Network allowed here. */
	public static function filter_check( $transient ) {
		if ( ! is_object( $transient ) || ! self::theme_slug() ) {
			return $transient;
		}
		if ( ! empty( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- only clears a cache
			delete_site_transient( self::CACHE );
		}
		$cached = get_site_transient( self::CACHE );
		$r      = ( false === $cached ) ? self::refresh() : ( is_array( $cached ) ? $cached : null );
		return self::apply( $transient, $r );
	}

	/** Runs on every read of the update list. Cache only, so pages never wait on GitHub. */
	public static function filter_read( $transient ) {
		return self::apply( $transient, self::cached() );
	}

	/** Make sure the new theme lands in the installed theme's folder, whatever the zip's top folder is called. */
	public static function fix_source_dir( $source, $remote_source, $upgrader, $hook_extra ) {
		if ( ! is_array( $hook_extra ) || empty( $hook_extra['theme'] ) || $hook_extra['theme'] !== self::theme_slug() ) {
			return $source;
		}
		if ( ! file_exists( trailingslashit( $source ) . 'style.css' ) ) {
			return $source;
		}
		$slug = $hook_extra['theme'];
		if ( basename( untrailingslashit( $source ) ) === $slug ) {
			return $source;
		}
		global $wp_filesystem;
		$new = trailingslashit( $remote_source ) . $slug;
		if ( $wp_filesystem && $wp_filesystem->move( untrailingslashit( $source ), $new, true ) ) {
			return trailingslashit( $new );
		}
		return new WP_Error( 'psychart_updater_rename', __( 'Could not prepare the theme folder for the update.', 'psychart-updater' ) );
	}

	public static function auto_update( $update, $item ) {
		if ( is_object( $item ) && isset( $item->theme ) && ! empty( self::opts()['auto'] ) && $item->theme === self::theme_slug() ) {
			return true;
		}
		return $update;
	}

	public static function after_upgrade( $upgrader, $info ) {
		if ( is_array( $info ) && isset( $info['type'] ) && 'theme' === $info['type'] ) {
			delete_site_transient( self::CACHE );
		}
	}

	/* ---------- Admin page ---------- */

	public static function menu() {
		add_theme_page( __( 'PSYCHART Updates', 'psychart-updater' ), __( 'PSYCHART Updates', 'psychart-updater' ), 'update_themes', self::PAGE, array( __CLASS__, 'page' ) );
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'themes.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Updates', 'psychart-updater' ) . '</a>' );
		return $links;
	}

	private static function back( $arg ) {
		wp_safe_redirect( add_query_arg( $arg, admin_url( 'themes.php?page=' . self::PAGE ) ) );
		exit;
	}

	public static function handle_save() {
		if ( ! current_user_can( 'update_themes' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'psychart-updater' ) );
		}
		check_admin_referer( 'psychart_updater_save' );

		$old  = self::opts();
		$repo = isset( $_POST['repo'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['repo'] ) ) ) : '';
		$repo = preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo ) ? $repo : 'ecomaura/psychart';
		$tok  = isset( $_POST['token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['token'] ) ) ) : '';
		if ( ! empty( $_POST['clear_token'] ) ) {
			$tok = '';
		} elseif ( '' === $tok ) {
			$tok = $old['token'];
		}
		update_option( self::OPT, array(
			'repo'  => $repo,
			'slug'  => isset( $_POST['slug'] ) ? preg_replace( '/[^A-Za-z0-9._-]/', '', wp_unslash( $_POST['slug'] ) ) : '',
			'token' => $tok,
			'auto'  => empty( $_POST['auto'] ) ? 0 : 1,
		), false );
		delete_site_transient( self::CACHE );
		self::back( array( 'saved' => 1 ) );
	}

	public static function handle_check() {
		if ( ! current_user_can( 'update_themes' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'psychart-updater' ) );
		}
		check_admin_referer( 'psychart_updater_check' );
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		self::refresh( true );
		delete_site_transient( 'update_themes' );
		if ( function_exists( 'wp_update_themes' ) ) {
			wp_update_themes();
		}
		self::back( array( 'checked' => 1 ) );
	}

	private static function host_allowed( $host, $list ) {
		foreach ( array_filter( array_map( 'trim', explode( ',', (string) $list ) ) ) as $p ) {
			if ( preg_match( '/^' . str_replace( '\*', '.*', preg_quote( $p, '/' ) ) . '$/i', $host ) ) {
				return true;
			}
		}
		return false;
	}

	private static function env_notes() {
		$notes = array();
		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
			$list    = defined( 'WP_ACCESSIBLE_HOSTS' ) ? WP_ACCESSIBLE_HOSTS : '';
			$missing = array();
			foreach ( array( 'github.com', 'api.github.com', 'release-assets.githubusercontent.com', 'objects.githubusercontent.com' ) as $h ) {
				if ( ! self::host_allowed( $h, $list ) ) {
					$missing[] = $h;
				}
			}
			if ( $missing ) {
				$notes[] = sprintf(
					/* translators: %s: host names */
					__( 'wp-config.php blocks outgoing requests (WP_HTTP_BLOCK_EXTERNAL) and does not allow: %s. Add them to WP_ACCESSIBLE_HOSTS, for example: define( \'WP_ACCESSIBLE_HOSTS\', \'api.wordpress.org,github.com,api.github.com,*.githubusercontent.com\' );', 'psychart-updater' ),
					implode( ', ', $missing )
				);
			}
		}
		return $notes;
	}

	public static function page() {
		if ( ! current_user_can( 'update_themes' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'psychart-updater' ) );
		}
		$o         = self::opts();
		$slug      = self::theme_slug();
		$installed = self::installed_version();
		$cached    = self::cached();
		$diag      = get_option( self::DIAG, array() );
		$newer     = $cached && $installed && version_compare( $cached['version'], $installed, '>' );

		echo '<div class="wrap"><h1>' . esc_html__( 'PSYCHART Theme Updates', 'psychart-updater' ) . '</h1>';

		if ( isset( $_GET['saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'psychart-updater' ) . '</p></div>';
		}
		if ( isset( $_GET['checked'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Checked GitHub. Results are in the table below.', 'psychart-updater' ) . '</p></div>';
		}
		if ( ! $slug ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'The PSYCHART.CO theme was not found. Install it first, or type its folder name under Settings.', 'psychart-updater' ) . '</p></div>';
		}
		foreach ( self::env_notes() as $n ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $n ) . '</p></div>';
		}

		echo '<table class="widefat striped" style="max-width:760px"><tbody>';
		printf( '<tr><th style="width:200px">%s</th><td>%s</td></tr>', esc_html__( 'Theme folder', 'psychart-updater' ), $slug ? '<code>' . esc_html( $slug ) . '</code>' : '&mdash;' );
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Installed version', 'psychart-updater' ), $installed ? esc_html( $installed ) : '&mdash;' );
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Latest on GitHub', 'psychart-updater' ), $cached ? esc_html( $cached['version'] ) . ' <small>(' . esc_html( $cached['source'] ) . ')</small>' : esc_html__( 'Not checked yet, or GitHub could not be reached', 'psychart-updater' ) );
		echo '<tr><th>' . esc_html__( 'Status', 'psychart-updater' ) . '</th><td>';
		if ( $newer && $slug ) {
			$url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-theme&theme=' . rawurlencode( $slug ) ), 'upgrade-theme_' . $slug );
			echo '<strong>' . esc_html__( 'Update available', 'psychart-updater' ) . '</strong> &nbsp; <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( sprintf( /* translators: %s: version */ __( 'Update to %s', 'psychart-updater' ), $cached['version'] ) ) . '</a>';
			echo '<p class="description">' . esc_html__( 'Or download the zip and upload it under Appearance > Themes:', 'psychart-updater' ) . ' <a href="' . esc_url( $cached['package'] ) . '">' . esc_html( basename( $cached['package'] ) ) . '</a></p>';
		} elseif ( $cached ) {
			echo esc_html__( 'Up to date', 'psychart-updater' );
		} else {
			echo esc_html__( 'Could not get the latest version. See the table below for the reason.', 'psychart-updater' );
		}
		echo '</td></tr></tbody></table>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:16px 0">';
		wp_nonce_field( 'psychart_updater_check' );
		echo '<input type="hidden" name="action" value="psychart_updater_check">';
		submit_button( __( 'Check GitHub now', 'psychart-updater' ), 'secondary', 'submit', false );
		echo '</form>';

		echo '<h2>' . esc_html__( 'Last check', 'psychart-updater' ) . '</h2>';
		if ( ! empty( $diag['log'] ) ) {
			echo '<p>' . esc_html( sprintf( /* translators: 1: time ago, 2: repo */ __( '%1$s ago, repo %2$s', 'psychart-updater' ), human_time_diff( $diag['time'] ), $diag['repo'] ) ) . '</p>';
			echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>' . esc_html__( 'Method', 'psychart-updater' ) . '</th><th>' . esc_html__( 'Result', 'psychart-updater' ) . '</th><th>URL</th></tr></thead><tbody>';
			foreach ( $diag['log'] as $row ) {
				printf(
					'<tr><td>%s</td><td style="color:%s">%s</td><td><code style="word-break:break-all">%s</code></td></tr>',
					esc_html( $row['label'] ),
					$row['ok'] ? '#1a7f37' : '#b32d2e',
					esc_html( ( $row['ok'] ? 'OK: ' : 'Failed: ' ) . $row['detail'] ),
					esc_html( $row['url'] )
				);
			}
			echo '</tbody></table>';
			echo '<p class="description">' . esc_html__( 'If every row fails with an error like "cURL error" or "timed out", your server cannot reach github.com. Ask your host to allow outgoing HTTPS to github.com, api.github.com and *.githubusercontent.com.', 'psychart-updater' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'No check has run yet. Click "Check GitHub now".', 'psychart-updater' ) . '</p>';
		}

		echo '<h2>' . esc_html__( 'Settings', 'psychart-updater' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><table class="form-table" role="presentation"><tbody>';
		wp_nonce_field( 'psychart_updater_save' );
		echo '<input type="hidden" name="action" value="psychart_updater_save">';
		printf( '<tr><th><label for="pu-repo">%s</label></th><td><input id="pu-repo" class="regular-text" name="repo" value="%s"><p class="description">owner/repo</p></td></tr>', esc_html__( 'GitHub repository', 'psychart-updater' ), esc_attr( self::repo() ) );
		printf( '<tr><th><label for="pu-slug">%s</label></th><td><input id="pu-slug" class="regular-text" name="slug" value="%s" placeholder="%s"><p class="description">%s</p></td></tr>', esc_html__( 'Theme folder', 'psychart-updater' ), esc_attr( $o['slug'] ), esc_attr( $slug ), esc_html__( 'Leave empty to detect automatically.', 'psychart-updater' ) );
		printf( '<tr><th><label for="pu-token">%s</label></th><td><input id="pu-token" class="regular-text" type="password" name="token" value="" autocomplete="new-password" placeholder="%s"><p class="description">%s</p><label><input type="checkbox" name="clear_token" value="1"> %s</label></td></tr>', esc_html__( 'GitHub token (optional)', 'psychart-updater' ), $o['token'] ? esc_attr__( '(saved)', 'psychart-updater' ) : '', esc_html__( 'Only needed if the GitHub API rate limit is hit. A fine-grained token with read-only access to public repositories is enough. The fallbacks work without one.', 'psychart-updater' ), esc_html__( 'Remove saved token', 'psychart-updater' ) );
		printf( '<tr><th>%s</th><td><label><input type="checkbox" name="auto" value="1" %s> %s</label></td></tr>', esc_html__( 'Auto-update', 'psychart-updater' ), checked( ! empty( $o['auto'] ), true, false ), esc_html__( 'Install new theme versions automatically', 'psychart-updater' ) );
		echo '</tbody></table>';
		submit_button( __( 'Save settings', 'psychart-updater' ) );
		echo '</form></div>';
	}
}

Psychart_Updater::init();
