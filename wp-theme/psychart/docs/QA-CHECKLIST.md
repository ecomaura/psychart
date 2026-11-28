# QA checklist (run in a real WordPress + WooCommerce install)

Static checks already done on the code: PHP lint on every file, JS syntax, CSS brace balance, AJAX/nonce/escaping review.
Items below need a browser and a live store.

## Core flows
- [ ] Home loads; hero, tagline card, values, 3 story cards, manifesto, Get Access all render; story cards link to products.
- [ ] Menu drawer, search overlay (live results, empty state), bag drawer (empty state, add, +/-, remove, subtotal).
- [ ] Shop: Filters drawer, each Sort option, category filter, empty state, pagination.
- [ ] Product page: pick colour, pick size, Find my size advisor (3 steps), result selects the size, Add to bag opens the drawer.
- [ ] Sold-out variation (Washed Black XL on the jacket) cannot be added.
- [ ] Cart page (shortcode): change qty, Update bag, remove, discount code, Proceed to checkout.
- [ ] Checkout: guest order, logged-in order, payment gateway, order-received page.
- [ ] My Account: sign in, register (if enabled), lost password, orders, addresses, details, log out.
- [ ] About, FAQ (accordion), Shipping & Returns, Contact (send, error, honeypot), 404.

## Devices
- [ ] 360px phone, 390px phone, 768px tablet, 1024px, 1440px desktop. No horizontal scroll anywhere.
- [ ] WordPress admin bar visible: header and drawers sit below it.

## Accessibility
- [ ] Keyboard only: skip link, open/close each drawer (Esc closes, focus returns, Tab stays inside), FAQ toggle, all forms.
- [ ] Screen reader: drawer names, bag live region, form labels and error messages.
- [ ] Zoom to 200%: nothing clipped or overlapping.
- [ ] prefers-reduced-motion: transitions off.

## Performance
- [ ] Lighthouse mobile on Home, Shop, Product: note LCP (hero is eager-loaded and fetchpriority=high).
- [ ] Confirm product-page CSS/JS and Archivo/Inter fonts load only on product pages.
