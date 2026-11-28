# PSYCHART.CO theme (v0.2.0 — Phases 0 to 2)

Classic PHP theme for WooCommerce. No build step.

## Install
1. WP Admin → Appearance → Themes → Add New → Upload Theme → `psychart.zip` → Activate.
2. Install/activate WooCommerce, run its setup wizard (creates Shop, Cart, Checkout, My Account pages).
3. Appearance → Menus: optionally assign menus to *Menu drawer* and the three *Footer* locations. Without menus, defaults are shown.
4. Appearance → Customize → Site Identity: optional logo image (otherwise the PSYCHART.CO wordmark text is used).

## What is in this phase
- Header (menu / wordmark / search / bag with count), sticky.
- Left menu drawer, full-screen live product search, right bag drawer (real WooCommerce cart: quantity, remove, subtotal, checkout).
- "Get Access" signup (saved under Subscribers in wp-admin; hook `psychart_subscribed` to forward to an email service) and footer.
- Fonts self-hosted: Bebas Neue and DM Sans (OFL licences in assets/fonts).

## Hooks for later phases
- `window.Psychart.bag.added()` — refresh bag and open drawer after any add-to-cart (Phase 4 product page will call this).
- `document.dispatchEvent(new Event('psychart:added'))` does the same.
- Filter `psychart_show_newsletter` (return false to hide the block on a template).

## Phase 2: Home page
- `front-page.php` is used automatically as the home page.
- Edit everything in Appearance > Customize > Home page (Hero, Alter ego intro, The New Uniform, Manifesto). Footer tagline is under Site Identity.
- Story cards link to a WooCommerce product chosen in the Customizer (empty = Shop). Their image defaults to the product's image, or the bundled demo image if no product is set.
- Demo images in `assets/img/demo/` are low-resolution frames from the design video. Replace them via the Customizer image fields before launch.
