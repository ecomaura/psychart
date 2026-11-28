# PSYCHART.CO: install and handover

Requirements: WordPress 6.4+, PHP 7.4+, WooCommerce 8.0+. No build step.

## 1. Install
1. WP Admin > Appearance > Themes > Add New > Upload Theme > `psychart-theme-1.0.0.zip` > Activate.
   On activation the About, FAQ, Shipping & Returns and Contact pages are created if missing.
2. Install and activate WooCommerce and run its setup wizard (it creates Shop, Cart, Checkout, My Account).
3. Settings > Permalinks: choose "Post name" and Save (the footer links use /faq/, /about/ and so on).

## 2. Cart and Checkout must use shortcodes
WooCommerce creates these pages with blocks. The theme styles the classic version:
- Pages > Cart: replace the content with `[woocommerce_cart]`
- Pages > Checkout: replace the content with `[woocommerce_checkout]`
- Pages > My Account: content should be `[woocommerce_my_account]`

## 3. Demo content (optional)
1. Products > All Products > Import > choose `demo/demo-products.csv` > Continue > Run the importer.
   It creates 3 variable products (Dual-tone Denim Jacket, Editorial Set, Washed Flare Denim) with Colour and Size variations, size charts and demo images (downloaded from the GitHub repo, so the server needs internet).
   One variation (Washed Black, XL) is out of stock on purpose, to show the sold-out state.
2. Appearance > Customize > Home page > The New Uniform: pick each product for the three cards.
3. Demo prices (120 to 180) and copy are placeholders.

## 4. Make it yours
- Appearance > Customize: Home page (all sections), About page (text, stats, contact recipient), Product page (store-wide delivery text), Site Identity (logo, footer tagline).
- Appearance > Menus: assign menus to Menu drawer and the three Footer locations (defaults are shown until you do).
- Replace the demo images (low-resolution video frames) in the Customizer image fields.
- FAQ and Shipping & Returns: the bundled text is placeholder. Write your own in the page editor and it replaces it.
- Newsletter signups appear under Subscribers in wp-admin. Hook `psychart_subscribed` to send them to your email service.
- Contact form uses `wp_mail()`. If mail does not arrive, install an SMTP plugin.

## 5. Per-product setup
See README.md > Phase 4: Setting up a product (Colour and Size attributes, variations, size chart box).
Products without a size chart hide the "Find my size" link.

## Full-page caching
Cart, bag, search and add-to-bag use admin-ajax and carry no nonce for cart actions (same as WooCommerce core), so they work behind page caches. Exclude /cart/, /checkout/ and /my-account/ from caching as WooCommerce advises.
