/* Product page behaviour. Ported from js/app.js of the static page.
   Same structure, ids, window.Store contract and rendering of swatches / sizes.
   What changed: the data (window.PRODUCT) now comes from WooCommerce variations,
   text and accordions are printed by PHP, and "add to bag" is a real AJAX add-to-cart. */
(function () {
  const P = window.PRODUCT;
  const C = window.PSYCHART || {};
  const $ = (id) => document.getElementById(id);
  const state = { colour: P.defaultColour || 0, size: null };
  const V = P.variations || [];
  const low = (s) => String(s == null ? "" : s).toLowerCase();
  let busy = false;

  const gallery = document.querySelector(".gallery__img");
  const firstImg = gallery ? { src: gallery.getAttribute("src"), srcset: gallery.getAttribute("srcset"), alt: gallery.getAttribute("alt") } : null;

  // Does variation v fit the chosen colour / size? An empty attribute on a variation means "any".
  function fits(v, colourSlug, sizeSlug) {
    const a = v.attrs || {};
    const okC = !P.hasColour || a.colour === "" || low(a.colour) === low(colourSlug);
    const okS = !P.hasSize || sizeSlug == null || a.size === "" || low(a.size) === low(sizeSlug);
    return okC && okS;
  }
  const colourSlug = () => (P.hasColour ? P.colours[state.colour].slug : "");
  const sizeObj = () => P.sizes.find((s) => s.label === state.size);
  function currentVariation() {
    if (P.type !== "variable") return null;
    if (P.hasSize && !state.size) return null;
    const list = V.filter((v) => fits(v, colourSlug(), P.hasSize ? sizeObj().slug : null));
    return list.find((v) => v.inStock) || list[0] || null;
  }

  // Which sizes can be bought in the chosen colour. The advisor reads P.sizes[].available too.
  function syncAvailability() {
    if (P.type !== "variable") return;
    P.sizes.forEach((s) => { s.available = V.some((v) => v.inStock && fits(v, colourSlug(), s.slug)); });
    if (state.size && !(sizeObj() || {}).available) state.size = null;
  }

  function syncPrice() {
    const v = currentVariation();
    $("price").innerHTML = v && v.priceHtml ? v.priceHtml : P.priceHtml;
  }

  function syncImage() {
    if (!gallery || !P.hasColour) return;
    const v = V.find((x) => x.image && fits(x, colourSlug(), null));
    const im = v ? v.image : null;
    gallery.setAttribute("src", im ? im.src : firstImg.src);
    if (im && im.srcset) gallery.setAttribute("srcset", im.srcset);
    else if (!im && firstImg.srcset) gallery.setAttribute("srcset", firstImg.srcset);
    else gallery.removeAttribute("srcset");
    gallery.setAttribute("alt", im ? im.alt : firstImg.alt);
  }

  // Colour swatches
  function renderColours() {
    const wrap = $("swatches");
    if (!wrap) return;
    wrap.innerHTML = "";
    P.colours.forEach((c, i) => {
      const b = document.createElement("button");
      b.type = "button";
      b.className = "swatch";
      b.style.setProperty("--c", c.hex);
      b.setAttribute("role", "radio");
      b.setAttribute("aria-label", c.name);
      b.setAttribute("aria-checked", String(i === state.colour));
      b.addEventListener("click", () => { state.colour = i; syncAvailability(); renderColours(); renderSizes(); syncPrice(); clearStatus(); });
      wrap.appendChild(b);
    });
    $("colour-name").textContent = P.colours[state.colour].name;
    if (gallery) gallery.style.backgroundColor = P.colours[state.colour].hex;
    syncImage();
  }

  // Sizes
  function renderSizes() {
    const wrap = $("sizes");
    if (!wrap) return;
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

  // Public so the size advisor can set the size on the page.
  function selectSize(label) {
    state.size = label;
    renderSizes();
    syncPrice();
    clearStatus();
  }

  function setStatus(msg, kind) {
    const el = $("status");
    el.textContent = msg;
    el.className = "status " + (kind ? "is-" + kind : "");
  }
  function clearStatus() { setStatus("", ""); }

  const colourName = () => (P.hasColour ? P.colours[state.colour].name : "");
  const addedMsg = (label) => "Added: " + P.title + (colourName() ? ", " + colourName() : "") + (label ? ", size " + label : "") + ".";

  // Real add-to-cart. The server resolves the variation from colour + size.
  function addToCart(label) {
    if (busy) return Promise.resolve();
    if (!P.inStock) { setStatus("Sorry, this is sold out.", "err"); return Promise.resolve(); }
    const btn = $("add");
    busy = true; btn.disabled = true;
    const body = new URLSearchParams({ action: "psychart_add_to_cart", product_id: P.id });
    if (P.hasColour) body.set("colour", colourSlug());
    if (P.hasSize) body.set("size", sizeObj().slug);
    return fetch(C.ajax, { method: "POST", credentials: "same-origin", body })
      .then((r) => r.json())
      .then((res) => {
        if (res && res.success) {
          setStatus(addedMsg(label), "ok");
          if (window.Psychart && window.Psychart.bag) window.Psychart.bag.added();
        } else {
          setStatus((res && res.data && res.data.message) || (C.i18n && C.i18n.error) || "Something went wrong.", "err");
        }
      })
      .catch(() => setStatus((C.i18n && C.i18n.error) || "Something went wrong.", "err"))
      .then(() => { busy = false; btn.disabled = false; });
  }

  $("add").addEventListener("click", () => {
    if (P.hasSize && !state.size) {
      $("sizes").classList.add("is-error");
      setStatus("Select a size to add this to your bag.", "err");
      return;
    }
    addToCart(state.size);
  });

  // Opens the advisor drawer.
  const find = $("find-size");
  if (find) find.addEventListener("click", () => {
    document.dispatchEvent(new CustomEvent("sizeadvisor:open"));
  });

  function addToBag(label) {
    selectSize(label);
    return addToCart(label);
  }

  window.Store = { selectSize, addToBag, getState: () => ({ ...state, colourName: P.colours[state.colour].name }) };
  syncAvailability();
  renderColours();
  renderSizes();
  syncPrice();
})();
