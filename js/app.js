(function () {
  const P = window.PRODUCT;
  const $ = (id) => document.getElementById(id);
  const state = { colour: 0, size: null };

  $("crumb").textContent = P.category;
  $("title").textContent = P.title;
  $("price").textContent = P.currency + P.price.toFixed(2);
  $("desc").textContent = P.description;
  document.title = P.title;

  // Colour swatches
  function renderColours() {
    const wrap = $("swatches");
    wrap.innerHTML = "";
    P.colours.forEach((c, i) => {
      const b = document.createElement("button");
      b.type = "button";
      b.className = "swatch";
      b.style.setProperty("--c", c.hex);
      b.setAttribute("role", "radio");
      b.setAttribute("aria-label", c.name);
      b.setAttribute("aria-checked", String(i === state.colour));
      b.addEventListener("click", () => { state.colour = i; renderColours(); clearStatus(); });
      wrap.appendChild(b);
    });
    $("colour-name").textContent = P.colours[state.colour].name;
    document.querySelector(".gallery__img").style.backgroundColor = P.colours[state.colour].hex;
  }

  // Sizes
  function renderSizes() {
    const wrap = $("sizes");
    wrap.innerHTML = "";
    P.sizes.forEach((s) => {
      const b = document.createElement("button");
      b.type = "button";
      b.className = "size";
      b.textContent = s.label;
      b.disabled = !s.available;
      b.setAttribute("role", "radio");
      b.setAttribute("aria-checked", String(state.size === s.label));
      b.addEventListener("click", () => selectSize(s.label));
      wrap.appendChild(b);
    });
    $("size-name").textContent = state.size || "Select";
    wrap.classList.remove("is-error");
  }

  // Public so the size advisor (Phase 2+) can set the size on the page.
  function selectSize(label) {
    state.size = label;
    renderSizes();
    clearStatus();
  }

  function setStatus(msg, kind) {
    const el = $("status");
    el.textContent = msg;
    el.className = "status " + (kind ? "is-" + kind : "");
  }
  function clearStatus() { setStatus("", ""); }

  $("add").addEventListener("click", () => {
    if (!state.size) {
      $("sizes").classList.add("is-error");
      setStatus("Select a size to add this to your bag.", "err");
      return;
    }
    setStatus("Added: " + P.title + ", " + P.colours[state.colour].name + ", size " + state.size + ".", "ok");
  });

  // Phase 2 opens the advisor drawer from here.
  $("find-size").addEventListener("click", () => {
    document.dispatchEvent(new CustomEvent("sizeadvisor:open"));
  });

  // Accordions
  $("details").innerHTML = P.details.map((d) =>
    "<details" + (d.open ? " open" : "") + "><summary>" + d.title + "</summary><p>" + d.body + "</p></details>"
  ).join("");

  function addToBag(label) {
    selectSize(label);
    setStatus("Added: " + P.title + ", " + P.colours[state.colour].name + ", size " + label + ".", "ok");
  }

  window.Store = { selectSize, addToBag, getState: () => ({ ...state, colourName: P.colours[state.colour].name }) };
  renderColours();
  renderSizes();
})();
