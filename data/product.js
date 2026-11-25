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
  details: [
    { title: "Materials & care", open: true,
      body: "100% cotton loopback fleece, 480 gsm, garment dyed. Wash cold inside out, tumble dry low." },
    { title: "Fit",
      body: "Boxy, true to size with a dropped shoulder. Size down for a closer fit." },
    { title: "Delivery & returns",
      body: "Free delivery on orders over $100. Returns accepted within 30 days, unworn and with tags." }
  ]
};
