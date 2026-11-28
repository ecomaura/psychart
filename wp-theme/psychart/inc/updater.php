<?php
/**
 * GitHub updater. Offers theme updates in Appearance > Themes and Dashboard > Updates
 * from the latest GitHub Release of PSYCHART_GH_REPO (public repo, no token needed).
 *
 * A release counts when its tag is a version number higher than the installed one
 * and it has an attached asset named psychart-<version>.zip (built by .github/workflows/release.yml).
 *
 * Disable with: add_filter( 'psychart_updater_enabled', '__return_false' );
 */
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PSYCHART_GH_REPO' ) ) {
	define( 'PSYCHART_GH_REPO', 'ecomaura/psychart' );
}

/** Reduce a GitHub release payload (decoded JSON) to what WordPress needs, or null. */
function psychart_updater_parse_release( $release ) {
	if ( ! is_array( $release ) || empty( $release['tag_name'] ) || ! empty( $release['draft'] ) || ! empty( $release['prerelease'] ) ) {
		return null;
	}
	$version = ltrim( (string) $release['tag_name'], 'vV' );
	if ( ! preg_match( '/^\d+(\.\d+){1,3}([-+][0-9A-Za-z.-]+)?$/', $version ) ) {
		return null;
	}
	$package = '';
	if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
		foreach ( $release['assets'] as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && preg_match( '/^psychart-.+\.zip$/', $asset['name'] ) ) {
				$package = (string) $asset['browser_download_url'];
				break;
			}
		}
	}
	if ( '' === $package || 0 !== strpos( $package, 'https://github.com/' . PSYCHART_GH_REPO . '/' ) ) {
		return null;
	}
	return array(
		'version' => $version,
		'package' => $package,
		'url'     => isset( $release['html_url'] ) ? esc_url_raw( $release['html_url'] ) : 'https://github.com/' . PSYCHART_GH_REPO . '/releases',
	);
}

/**
 * Fallback when the GitHub API is rate limited or blocked (common on shared hosting):
 * github.com/<repo>/releases/latest redirects to the latest release tag, and the zip name is predictable.
 */
function psychart_updater_latest_via_redirect() {
	$res = wp_remote_head( 'https://github.com/' . PSYCHART_GH_REPO . '/releases/latest', array(
		'timeout'     => 8,
		'redirection' => 0,
		'headers'     => array( 'User-Agent' => 'psychart-theme-updater' ),
	) );
	if ( is_wp_error( $res ) ) {
		return null;
	}
	$location = (string) wp_remote_retrieve_header( $res, 'location' );
	if ( ! preg_match( '#/releases/tag/([^/?\#]+)$#', $location, $m ) ) {
		return null;
	}
	$tag     = rawurldecode( $m[1] );
	$version = ltrim( $tag, 'vV' );
	$base    = 'https://github.com/' . PSYCHART_GH_REPO . '/releases';
	return psychart_updater_parse_release( array(
		'tag_name' => $tag,
		'html_url' => $base . '/tag/' . rawurlencode( $tag ),
		'assets'   => array(
			array(
				'name'                 => 'psychart-' . $version . '.zip',
				'browser_download_url' => $base . '/download/' . rawurlencode( $tag ) . '/psychart-' . $version . '.zip',
			),
		),
	) );
}

/** Latest usable release, cached (12h on success, 15 min when GitHub is unreachable or has nothing). */
function psychart_updater_latest() {
	$cached = get_site_transient( 'psychart_gh_release' );
	if ( false !== $cached ) {
		return $cached ?: null;
	}
	$res = wp_remote_get( 'https://api.github.com/repos/' . PSYCHART_GH_REPO . '/releases/latest', array(
		'timeout' => 8,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'psychart-theme-updater',
		),
	) );
	$release = null;
	if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
		$release = psychart_updater_parse_release( json_decode( wp_remote_retrieve_body( $res ), true ) );
	}
	if ( ! $release ) {
		$release = psychart_updater_latest_via_redirect();
	}
	set_site_transient( 'psychart_gh_release', $release ? $release : '', $release ? 12 * HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS );
	return $release;
}

add_filter( 'pre_set_site_transient_update_themes', function ( $transient ) {
	if ( ! apply_filters( 'psychart_updater_enabled', true ) || ! is_object( $transient ) ) {
		return $transient;
	}
	$slug = get_template();
	if ( ! empty( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- only clears a cache
		delete_site_transient( 'psychart_gh_release' );
	}
	$release = psychart_updater_latest();
	if ( $release && version_compare( $release['version'], PSYCHART_VERSION, '>' ) ) {
		$transient->response[ $slug ] = array(
			'theme'        => $slug,
			'new_version'  => $release['version'],
			'url'          => $release['url'],
			'package'      => $release['package'],
			'requires'     => '6.4',
			'requires_php' => '7.4',
		);
	} elseif ( isset( $transient->response[ $slug ] ) && empty( $transient->response[ $slug ]['package'] ) ) {
		unset( $transient->response[ $slug ] );
	}
	return $transient;
} );

// Forget the cached release once a theme update has run, so the notice disappears.
add_action( 'upgrader_process_complete', function ( $upgrader, $info ) {
	if ( isset( $info['type'] ) && 'theme' === $info['type'] ) {
		delete_site_transient( 'psychart_gh_release' );
	}
}, 10, 2 );
