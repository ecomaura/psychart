# Updating from GitHub (no zip uploads)

The theme checks the latest GitHub Release of `ecomaura/psychart` and offers it in
**Appearance > Themes** and **Dashboard > Updates**. Click Update; nothing to upload.
WordPress checks about every 12 hours. Dashboard > Updates > "Check again" checks immediately.

## First time (once per site)
Install release **1.0.0** by uploading the `psychart-1.0.0.zip` attached to the GitHub release
(Appearance > Themes > Add New > Upload Theme). Every later version updates from inside WordPress.

## Publishing an update (you or me)
1. Change the version in BOTH places to the same number, e.g. `1.0.1`:
   - `wp-theme/psychart/style.css` > `Version:`
   - `wp-theme/psychart/functions.php` > `PSYCHART_VERSION`
2. Commit and push to `main`.
3. Tag and push the tag (the tag is the bare number, no "v" needed):
   ```
   git tag 1.0.1
   git push origin 1.0.1
   ```
4. GitHub Actions (`.github/workflows/release.yml`) checks the versions match, builds `psychart-1.0.1.zip`
   and publishes the release. If the versions do not match, the run fails and nothing is published.
5. Sites see the update within hours (or immediately via "Check again").

## Notes
- The repo must stay public. For a private repo the updater cannot download the zip without a token.
- Pre-releases and drafts are ignored.
- Only a release with an attached `psychart-<version>.zip` is offered, and only from this repo's GitHub URL.
- Turn it off on a site with `add_filter( 'psychart_updater_enabled', '__return_false' );`.
- Change the repo with `define( 'PSYCHART_GH_REPO', 'owner/repo' );` in wp-config.php.
- Back up (or use a staging site) before updating a live store, as with any theme update.
