// Size engine: estimates body measurements from height, weight, sex and shape,
// then picks the chart size whose garment measurements best match the chosen fit.
// It is a guide, not a guarantee: tune the constants below per product.
(function () {
  const IN = 2.54;
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));

  // Body circumference (cm) = base + slope * BMI
  const BODY = {
    men:   { chest: [66, 1.28], waist: [44, 1.6], hips: [70, 1.15] },
    women: { chest: [58, 1.4],  waist: [42, 1.4], hips: [68, 1.5] }
  };
  const SHAPE = { slim: { waist: -4, hips: -1 }, average: { waist: 0, hips: 0 }, fuller: { waist: 5, hips: 2 } };
  // How much room the garment is cut with at "as cut" (cm), and how each fit choice shifts it.
  const EASE = { chest: 22, waist: 28, hips: 14, length: 0 };
  const SHIFT = {
    close: { chest: -6, waist: -6, hips: -6, length: -2 },
    "as-cut": { chest: 0, waist: 0, hips: 0, length: 0 },
    relaxed: { chest: 6, waist: 6, hips: 6, length: 2 }
  };
  const TOL = { chest: 4, waist: 5, hips: 5, length: 2 };     // within this = "right"
  const SPAN = { chest: 12, waist: 14, hips: 14, length: 5 }; // cm from centre to either end of the gauge
  const WEIGHT = { chest: 3, waist: 1.5, hips: 1.5, length: 1 };
  const LABEL = { chest: "Chest", waist: "Waist", hips: "Hips", length: "Length" };
  const KEYS = ["chest", "waist", "hips", "length"];

  function bodyModel(sex, cm, kg, shape) {
    const t = BODY[sex] || BODY.men, s = SHAPE[shape] || SHAPE.average;
    const bmi = kg / Math.pow(cm / 100, 2);
    return {
      chest: t.chest[0] + t.chest[1] * bmi,
      waist: t.waist[0] + t.waist[1] * bmi + s.waist,
      hips: t.hips[0] + t.hips[1] * bmi + s.hips,
      length: 0.4 * cm
    };
  }

  function evaluate(row, body, fit, chart) {
    const k = chart.unit === "in" ? IN : 1, sh = SHIFT[fit] || SHIFT["as-cut"];
    return KEYS.map((m) => {
      const dev = row[m] * k - body[m] - (EASE[m] + sh[m]);
      return {
        key: m, label: LABEL[m], dev: Math.round(dev * 10) / 10,
        status: dev < -TOL[m] ? "snug" : dev > TOL[m] ? "roomy" : "ok",
        gauge: clamp(0.5 + dev / (2 * SPAN[m]), 0, 1)   // 0 = snug, 0.5 = just right, 1 = roomy
      };
    });
  }

  function recommend(input, chart) {
    chart = chart || window.PRODUCT.sizeChart;
    const cm = clamp(+input.cm || 175, 120, 230), kg = clamp(+input.kg || 70, 30, 200);
    const fit = input.fit || "as-cut", body = bodyModel(input.sex, cm, kg, input.shape || "average");
    const bySize = {}; let best = null;
    chart.rows.forEach((row) => {
      const ms = evaluate(row, body, fit, chart);
      const score = ms.reduce((a, m) => a + WEIGHT[m.key] * Math.pow(m.dev / TOL[m.key], 2), 0);
      bySize[row.size] = { measurements: ms, score };
      if (!best || score < best.score) best = { size: row.size, score };
    });
    const first = chart.rows[0].size, last = chart.rows[chart.rows.length - 1].size;
    const chestDev = bySize[best.size].measurements[0].dev;
    let note = null;
    if (best.size === first && chestDev > TOL.chest) note = "This may run large for you.";
    if (best.size === last && chestDev < -TOL.chest) note = "This may run small for you.";
    return { size: best.size, measurements: bySize[best.size].measurements, bySize, body, fit, note };
  }

  window.Fit = Object.assign(window.Fit || {}, { recommend });
})();
