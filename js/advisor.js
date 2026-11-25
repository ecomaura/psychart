(function () {
  const P = window.PRODUCT, F = window.Fit;
  const RANGE = {
    height: { cm: [140, 210], ftin: [55, 83] },   // ftin slider counts inches
    weight: { kg: [40, 140], lb: [90, 310] }
  };
  const state = { sex: "men", cm: 175, kg: 72, hUnit: "cm", wUnit: "kg" };
  let root, opener;

  const el = (html) => { const t = document.createElement("template"); t.innerHTML = html.trim(); return t.content.firstElementChild; };

  function build() {
    root = el(`
    <div class="adv" hidden>
      <div class="adv__scrim" data-close></div>
      <section class="adv__panel" role="dialog" aria-modal="true" aria-labelledby="adv-title" tabindex="-1">
        <header class="adv__bar">
          <h2 class="adv__name" id="adv-title">Size advisor</h2>
          <button type="button" class="adv__x" data-close aria-label="Close size advisor">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5l14 14M19 5 5 19"/></svg>
          </button>
        </header>
        <div class="adv__scroll">
          <div class="adv__prod"><span class="adv__thumb" id="adv-thumb"></span>
            <div><strong>${P.title}</strong><br><span>${P.currency}${P.price.toFixed(2)}</span></div></div>
          <div class="adv__progress" aria-hidden="true"><i class="on"></i><i></i><i></i></div>
          <p class="adv__step">Step 1 of 3 · Measurements</p>
          <h3 class="adv__h">Let’s find your fit</h3>
          <p class="adv__sub">Two measurements, about ten seconds.</p>

          <div class="seg" role="radiogroup" aria-label="Sizing type">
            <button type="button" role="radio" data-sex="women">Womenswear</button>
            <button type="button" role="radio" data-sex="men">Menswear</button>
          </div>

          <div class="adv__body">
            <svg class="adv__fig" id="adv-fig" viewBox="0 0 60 200" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
              <ellipse cx="30" cy="16" rx="9" ry="11"/><path d="M26 27v6l-14 6v46M34 27v6l14 6v46M12 39h36v52H12zM17 91v98h10V110h6v79h10V91"/>
            </svg>
            <div class="adv__metrics">
              ${metric("height", "Height", [["cm", "CM"], ["ftin", "FT/IN"]])}
              ${metric("weight", "Weight", [["kg", "KG"], ["lb", "LB"]])}
            </div>
          </div>

          <div class="est" aria-live="polite">
            <div><span class="est__k">Live estimate</span><span class="est__s">Refines in the next steps</span></div>
            <strong class="est__v" id="adv-est">M</strong>
          </div>
        </div>
        <footer class="adv__foot">
          <button type="button" class="adv__go" id="adv-continue">Continue →</button>
          <p class="adv__note" id="adv-note" role="status"></p>
        </footer>
      </section>
    </div>`);
    document.body.appendChild(root);
  }

  function metric(key, label, units) {
    return `
    <div class="metric" data-metric="${key}">
      <div class="metric__head"><span class="metric__label" id="lbl-${key}">${label}</span>
        <div class="unit" role="radiogroup" aria-label="${label} unit">
          ${units.map(([v, t]) => `<button type="button" role="radio" data-unit="${v}">${t}</button>`).join("")}
        </div></div>
      <p class="metric__val"><strong data-num></strong> <span data-u></span></p>
      <div class="metric__ctrl">
        <button type="button" class="step" data-dir="-1" aria-label="Decrease ${label.toLowerCase()}">−</button>
        <input type="range" step="1" aria-labelledby="lbl-${key}">
        <button type="button" class="step" data-dir="1" aria-label="Increase ${label.toLowerCase()}">+</button>
      </div>
    </div>`;
  }

  // slider value <-> canonical (cm / kg)
  const unitOf = (k) => (k === "height" ? state.hUnit : state.wUnit);
  const toSlider = (k) => k === "height"
    ? (state.hUnit === "cm" ? state.cm : state.cm / F.IN)
    : (state.wUnit === "kg" ? state.kg : state.kg / F.LB);
  const fromSlider = (k, v) => {
    if (k === "height") state.cm = state.hUnit === "cm" ? v : v * F.IN;
    else state.kg = state.wUnit === "kg" ? v : v * F.LB;
  };

  function render() {
    root.querySelectorAll("[data-sex]").forEach((b) => b.setAttribute("aria-checked", String(b.dataset.sex === state.sex)));
    ["height", "weight"].forEach((k) => {
      const m = root.querySelector(`[data-metric="${k}"]`), u = unitOf(k);
      const [min, max] = RANGE[k][u], input = m.querySelector("input");
      input.min = min; input.max = max;
      input.value = Math.round(toSlider(k));
      input.style.setProperty("--p", ((input.value - min) / (max - min)) * 100 + "%");
      input.setAttribute("aria-valuetext", valueText(k));
      const f = k === "height" ? F.formatHeight(state.cm, u) : F.formatWeight(state.kg, u);
      m.querySelector("[data-num]").textContent = f.num;
      m.querySelector("[data-u]").textContent = f.unit;
      m.querySelectorAll("[data-unit]").forEach((b) => b.setAttribute("aria-checked", String(b.dataset.unit === u)));
    });
    // figure grows slightly with height
    root.querySelector("#adv-fig").style.transform = `scaleY(${(0.9 + ((state.cm - 140) / 70) * 0.2).toFixed(3)})`;
    root.querySelector("#adv-est").textContent = F.estimateSize(state.sex, state.cm, state.kg);
  }

  function valueText(k) {
    const f = k === "height" ? F.formatHeight(state.cm, state.hUnit) : F.formatWeight(state.kg, state.wUnit);
    return (f.num + " " + f.unit).trim();
  }

  function bind() {
    root.addEventListener("click", (e) => {
      const t = e.target.closest("button,[data-close]");
      if (!t) return;
      if (t.hasAttribute("data-close")) return close();
      if (t.dataset.sex) { state.sex = t.dataset.sex; return render(); }
      const m = t.closest("[data-metric]");
      if (t.dataset.unit && m) {
        const k = m.dataset.metric;
        if (k === "height") state.hUnit = t.dataset.unit; else state.wUnit = t.dataset.unit;
        return render();
      }
      if (t.dataset.dir && m) {
        const k = m.dataset.metric, input = m.querySelector("input");
        const v = Math.min(+input.max, Math.max(+input.min, Math.round(toSlider(k)) + +t.dataset.dir));
        fromSlider(k, v); return render();
      }
      if (t.id === "adv-continue") {
        root.querySelector("#adv-note").textContent = "Fit preference is the next step.";
        document.dispatchEvent(new CustomEvent("sizeadvisor:continue", { detail: { ...state } }));
      }
    });
    root.addEventListener("input", (e) => {
      const m = e.target.closest("[data-metric]");
      if (m && e.target.type === "range") { fromSlider(m.dataset.metric, +e.target.value); render(); }
    });
    root.addEventListener("keydown", (e) => {
      if (e.key === "Escape") return close();
      if (e.key !== "Tab") return;
      const f = [...root.querySelectorAll("button,input")].filter((n) => !n.disabled && n.offsetParent !== null);
      const first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  }

  function open() {
    opener = document.activeElement;
    const c = window.Store.getState();
    root.querySelector("#adv-thumb").style.background = P.colours[c.colour].hex;
    root.querySelector("#adv-note").textContent = "";
    render();
    root.hidden = false;
    document.body.classList.add("adv-lock");
    requestAnimationFrame(() => root.classList.add("is-open"));
    root.querySelector(".adv__panel").focus();
  }
  function close() {
    root.classList.remove("is-open");
    document.body.classList.remove("adv-lock");
    setTimeout(() => { root.hidden = true; }, 220);
    if (opener && opener.focus) opener.focus();
  }

  build(); bind();
  document.addEventListener("sizeadvisor:open", open);
  window.SizeAdvisor = { open, close, getState: () => ({ ...state }) };
})();
