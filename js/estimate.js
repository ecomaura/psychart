// Unit helpers (cm/in, kg/lb) and display formatting.
(function () {
  const IN = 2.54, LB = 0.45359237;
  function formatHeight(cm, unit) {
    if (unit === "cm") return { num: String(Math.round(cm)), unit: "cm" };
    const total = Math.round(cm / IN);
    return { num: Math.floor(total / 12) + "\u2032" + (total % 12) + "\u2033", unit: "" };
  }
  function formatWeight(kg, unit) {
    return unit === "kg" ? { num: String(Math.round(kg)), unit: "kg" } : { num: String(Math.round(kg / LB)), unit: "lb" };
  }

  window.Fit = Object.assign(window.Fit || {}, { formatHeight, formatWeight, IN, LB });
})();
