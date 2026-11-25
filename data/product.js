// Single source of truth for the product. Phase 3 adds the size chart + fit logic here.
window.PRODUCT = {
  id: "loop-hoodie",
  category: "Apparel / Fleece",
  title: "Loop Hoodie",
  price: 118,
  currency: "$",
  description:
    "Heavyweight 480 gsm loopback fleece, cut boxy with a dropped shoulder, layer hood and ribbed cuffs and hem. Built to be worn every day.",
  colours: [
    { name: "Washed black", hex: "#222222" },
    { name: "Stone grey", hex: "#a3a3a3" },
    { name: "Forest", hex: "#1f4d33" },
    { name: "Bone", hex: "#e6dfd2" }
  ],
  sizes: [
    { label: "XS", available: true },
    { label: "S", available: true },
    { label: "M", available: true },
    { label: "L", available: true },
    { label: "XL", available: true },
    { label: "XXL", available: true }
  ],
  // Garment measurements, as printed on the size chart (inches).
  sizeChart: {
    unit: "in",
    rows: [
      { size: "XS", chest: 41.7, waist: 39.4, hips: 39.4, length: 26.0 },
      { size: "S", chest: 44.1, waist: 41.7, hips: 41.7, length: 26.8 },
      { size: "M", chest: 46.5, waist: 44.1, hips: 44.1, length: 27.6 },
      { size: "L", chest: 48.8, waist: 46.5, hips: 46.5, length: 28.3 },
      { size: "XL", chest: 51.2, waist: 48.8, hips: 48.8, length: 29.1 },
      { size: "XXL", chest: 53.5, waist: 51.2, hips: 51.2, length: 29.9 }
    ]
  },
  details: [
    { title: "Materials & care", open: true,
      body: "100% cotton loopback fleece, 480 gsm, garment dyed. Wash cold inside out, tumble dry low." },
    { title: "Fit",
      body: "Boxy, true to size with a dropped shoulder. Size down for a closer fit." },
    { title: "Delivery & returns",
      body: "Free delivery on orders over $100. Returns accepted within 30 days, unworn and with tags." }
  ]
};
