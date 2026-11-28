# PSYCHART Theme Updater (plugin)

Updates the PSYCHART.CO theme from GitHub Releases, independent of any code inside the theme.

- Tries the GitHub API, then the github.com "latest release" redirect, then the releases feed. The last two have no API rate limit.
- Finds the theme even if its folder was renamed (e.g. `psychart-main`) and installs the update into that folder.
- Appearance > PSYCHART Updates: installed vs latest version, an **Update** button, a "Check GitHub now" button and a table showing exactly which request failed and why.
- Warns when wp-config.php blocks GitHub (`WP_HTTP_BLOCK_EXTERNAL` without `WP_ACCESSIBLE_HOSTS`).
- Optional GitHub token (rate limit only) and auto-update toggle.

Install: Plugins > Add New > Upload Plugin > `psychart-updater.zip`. The repo must stay public.
Release zips must be named `psychart-<version>.zip` with a top-level `psychart/` folder (release.yml already does this).
