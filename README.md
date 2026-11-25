# psychart – Loop Hoodie product page

Static, no build step. Open `index.html` or serve the folder (e.g. GitHub Pages).

- `data/product.js` – product data (colours, sizes, copy)
- `js/app.js` – page behaviour
- `css/styles.css`, `css/advisor.css` – styles
- `js/advisor.js` – size advisor drawer; `js/estimate.js` – unit helpers; `js/engine.js` – sizing engine (tune constants per product)

## Phases
1. Product page
2. Size advisor drawer, step 1 (measurements)
3. Body-shape step, fit-preference step, sizing engine
4. Result screen: size ring, per-measurement breakdown, size picker with YOU marker, Snug / Just right / Roomy gauges, cm/in size chart, add to bag / select on page / edit details. Answers are saved in `localStorage` on the device only. Radio groups support arrow keys, the drawer traps focus and closes on Esc, and the result is announced to screen readers.
