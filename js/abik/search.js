/* Abik — search + filters (public browse) */
Warners.renderHeader("search");
Warners.bindAddButtons();

const params = new URLSearchParams(location.search);
const searchInput = document.querySelector(".header-search input");
const searchForm = document.querySelector(".header-search");
const searchLayout = document.querySelector(".search-layout");
const filters = document.querySelector(".filters");
const preCat = params.get("cat") || "";

if (searchInput && !searchInput.value) searchInput.value = params.get("q") || "";

document.getElementById("cat-filters").innerHTML = Warners.getCategories()
  .map(
    (c) =>
      `<label><input type="checkbox" data-cat="${c}" ${
        preCat === c ? "checked" : ""
      } /> ${c}</label>`
  )
  .join("");

document.getElementById("brand-filters").innerHTML = Warners.getBrands()
  .map((b) => `<label><input type="checkbox" data-brand="${b}" /> ${b}</label>`)
  .join("") || `<p class="meta">No brands yet.</p>`;

function isSearchMode() {
  return !!(searchInput?.value || "").trim();
}

function readPriceRange(root) {
  const minEl = root.querySelector("[data-price-min]");
  const maxEl = root.querySelector("[data-price-max]");
  return {
    minPrice: minEl?.value.trim() || null,
    maxPrice: maxEl?.value.trim() || null,
  };
}

function getActiveCats() {
  if (isSearchMode()) {
    return [...document.querySelectorAll("[data-cat]:checked")].map((e) =>
      e.getAttribute("data-cat")
    );
  }
  return preCat ? [preCat] : [];
}

function getActiveBrands() {
  if (!isSearchMode()) return [];
  return [...document.querySelectorAll("[data-brand]:checked")].map((e) =>
    e.getAttribute("data-brand")
  );
}

function updateFiltersVisibility() {
  const searching = isSearchMode();
  if (filters) filters.hidden = !searching;
  searchLayout?.classList.toggle("search-layout--browse", !searching);
}

function render() {
  updateFiltersVisibility();

  const q = (searchInput?.value || "").trim().toLowerCase();
  const cats = getActiveCats();
  const brands = getActiveBrands();
  const { minPrice, maxPrice } = isSearchMode()
    ? readPriceRange(filters)
    : { minPrice: null, maxPrice: null };

  const matched = Warners.filterProducts({ q, cats, brands, minPrice, maxPrice });
  const list = isSearchMode() ? Warners.rankSearchResults(matched, q) : matched;

  const heading = document.getElementById("search-heading");
  if (heading) {
    if (isSearchMode()) {
      heading.textContent = "Search results";
    } else if (preCat) {
      heading.textContent = preCat;
    } else {
      heading.textContent = "All products";
    }
  }

  const hint = document.getElementById("search-hint");
  if (hint) {
    if (isSearchMode()) hint.textContent = "Results are ranked by how closely the name, brand and category match your search.";
    else if (preCat) hint.textContent = `Showing the ${preCat} category. Use search to narrow by name or brand.`;
    else hint.textContent = "Use the header search to filter by name, or open a category from the menu.";
  }

  const countEl = document.getElementById("count");
  if (countEl) {
    if (isSearchMode()) {
      countEl.textContent = `Showing ${list.length} results sorted by relevance.`;
    } else if (preCat) {
      countEl.textContent = `Showing ${list.length} products in ${preCat}.`;
    } else {
      countEl.textContent = `Showing ${list.length} products.`;
    }
  }

  document.getElementById("results").innerHTML = list.length
    ? list.map(Warners.productCard).join("")
    : `<div class="panel">No products match your filters.</div>`;
}

searchForm?.addEventListener("submit", (e) => {
  e.preventDefault();
  const q = (searchInput?.value || "").trim();
  if (q) Warners.recordSearch(q);
  syncQueryInUrl();
  render();
});

filters.addEventListener("change", render);
filters.addEventListener("input", (e) => {
  if (e.target.matches("[data-price-min], [data-price-max]")) render();
});

let searchTimer = 0;
searchInput?.addEventListener("input", () => {
  syncQueryInUrl();
  window.clearTimeout(searchTimer);
  searchTimer = window.setTimeout(render, 120);
});

if (searchInput?.value) {
  searchInput.focus();
  searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
}

render();

function syncQueryInUrl() {
  const url = new URL(location.href);
  const q = (searchInput?.value || "").trim();
  if (q) url.searchParams.set("q", q);
  else url.searchParams.delete("q");
  history.replaceState(null, "", url);
}
