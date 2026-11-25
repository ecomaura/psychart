// Temporary rough estimate for the live chip. Phase 3 replaces this with the size-chart engine.
(function () {
  const SIZES = ["XS", "S", "M", "L", "XL", "XXL"];
  const LIMITS = { men: [58, 66, 76, 86, 96], women: [48, 56, 65, 75, 85] };

  function estimateSize(sex, cm, kg) {
    const score = kg + (cm - 170) * 0.3;
    const cuts = LIMITS[sex] || LIMITS.men;
    let i = 0;
    while (i < cuts.length && score >= cuts[i]) i++;
    return SIZES[i];
  }

  const IN = 2.54, LB = 0.45359237;
  function formatHeight(cm, unit) {
    if (unit === "cm") return { num: String(Math.round(cm)), unit: "cm" };
    const total = Math.round(cm / IN);
    return { num: Math.floor(total / 12) + "\u2032" + (total % 12) + "\u2033", unit: "" };
  }
  function formatWeight(kg, unit) {
    return unit === "kg" ? { num: String(Math.round(kg)), unit: "kg" } : { num: String(Math.round(kg / LB)), unit: "lb" };
  }

  window.Fit = Object.assign(window.Fit || {}, { estimateSize, formatHeight, formatWeight, IN, LB });
})();
