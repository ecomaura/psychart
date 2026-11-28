# PSYCHART.CO theme (v1.0.0 — Phases 0 to 6)

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

## Phase 3: Shop, search results, filters
- `woocommerce/archive-product.php` renders Shop, category/tag archives and product search results (`?s=...&post_type=product`).
- Sort (Newest, Price: Low to High, Price: High to Low, Name: A to Z) uses WooCommerce's own ordering.
- Filters drawer: categories (links), Size/Colour (uses product attributes named `size`, `colour` or `color`), price range, in-stock only. Works without JavaScript; the drawer reopens after each choice.
- `woocommerce/content-product.php` is the product card (4:5 image, second gallery image on hover, Sold out / Sale badge).
- Grid: 2 columns on phones, 3 on tablets, 4 on desktop; 12 products per page (`loop_shop_per_page`).
- Add more filterable attributes with the `psychart_filter_attributes` filter.

## Phase 4: Product page (ported as-is from the repo's static page)
- `woocommerce/single-product.php` has the same markup, ids and classes as the static `index.html`.
- `assets/pdp/` holds the page assets: `css/advisor.css`, `js/advisor.js`, `js/engine.js`, `js/estimate.js` are byte-for-byte the repo files. `css/styles.css` is the repo file with only its token block scoped to `body.single-product` (the global resets are dropped because the theme provides them). `js/pdp.js` is `app.js` with the data source and add-to-bag swapped (see below). `css/pdp-theme.css` is glue (fonts, page base styles).
- Fonts: the page keeps its original Archivo + Inter, self-hosted (OFL licences in `assets/pdp/fonts`) and loaded on product pages only. To use the theme fonts instead, set `--display` and `--text` in `body.single-product` (top of `css/styles.css`).

### Setting up a product
1. Products > Attributes: create **Colour** and **Size** (slugs `colour`/`color` and `size`). Add terms. On each Colour term set the *Swatch colour*.
2. Create a **Variable product**, tick *Used for variations* on both attributes, generate variations, set price/stock/image per variation.
3. In the product edit screen, **PSYCHART product page** box: size chart (one line per size: `Size, Chest, Waist, Hips, Length`, inches or cm), Materials & care, Fit, Delivery & returns. Without a size chart the "Find my size" link is hidden.
4. Short description = the paragraph under the price. Product category = the small label above the title.
5. Store-wide Delivery & returns text: Customize > Product page.
Simple (non-variable) products also work: no colour/size pickers, just Add to bag.

### How the pieces connect
- `inc/product.php` builds `window.PRODUCT` (same shape as the repo's `data/product.js`) from the product's variations, so the advisor and engine read it unchanged.
- Colour change updates which sizes are in stock, the price (variation price) and the image (variation image).
- `window.Store.addToBag()` (used by the advisor) and the Add to bag button post to the `psychart_add_to_cart` AJAX action. The server resolves the variation from colour + size, adds it to the real WooCommerce cart, then the bag drawer refreshes and opens.
- Filters: `psychart_pdp_extra_images` (false = show only the main image), `psychart_colour_names` (colour name > hex map), `psychart_pdp_max_variations` (default 100).

## Phase 5: Cart, checkout, account, content pages, 404
**Set the Cart and Checkout pages to the classic shortcodes** (WooCommerce 8+ creates them as blocks): edit the Cart page and replace its content with `[woocommerce_cart]`, the Checkout page with `[woocommerce_checkout]`. My Account uses `[woocommerce_my_account]`. The theme styles the shortcode versions.

- `page.php` wraps Cart, Checkout, My Account and any other page.
- `woocommerce/cart/cart.php`, `cart/cart-empty.php`: bag-style cart (thumbnail, quantity, remove, discount code, totals, sticky on desktop). Cross-sells are removed.
- Checkout, order-received and account dashboard/orders/addresses are styled with CSS only (no template overrides), so WooCommerce updates and payment gateways keep working.
- `woocommerce/myaccount/form-login.php`: Sign in + Create account (the register panel shows only if registration is enabled in WooCommerce > Settings > Accounts & Privacy).
- Content pages are slug-based templates: `page-about.php` (about), `page-faq.php` (faq), `page-shipping-returns.php` (shipping-returns), `page-contact.php` (contact). They are created automatically on activation if missing, matching the footer links.
  - About text and the three stats: Customize > About page.
  - FAQ and Shipping & Returns: the bundled copy is placeholder text. Writing anything in the page editor replaces it. FAQ defaults can also be changed with the `psychart_faq_items` filter.
  - Contact: form posts to `admin-post.php`, nonce + honeypot + 1-per-minute limit per IP, sends with `wp_mail()` to Customize > About page > Contact form recipient (default: admin email). Hook `psychart_contact_sent` to log or forward. Use an SMTP plugin on hosts where `wp_mail()` is unreliable.
- `404.php`: Shop / Home / Search.

## Phase 6: QA and handover
- Install, demo content and customisation: `docs/INSTALL.md`. Browser test list: `docs/QA-CHECKLIST.md`.
- `demo/demo-products.csv`: 3 variable products for WooCommerce's importer.
- `inc/performance.php`: drops emoji, oEmbed discovery and (on home, shop, product and 404) block-editor CSS.
- Fixes: bag quantity increases now respect stock; live-search output escapes quotes; skip link focuses `<main>`; header and drawers clear the admin bar; larger bag +/- targets; tablet and print polish.
- `screenshot.png` for Appearance > Themes.
