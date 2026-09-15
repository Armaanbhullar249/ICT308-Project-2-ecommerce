/* Abik — customer home (public browse) */
Warners.renderHeader("home");
Warners.bindAddButtons();

const products = Warners.getProducts();
const featured = [];
const seenCats = new Set();
products.forEach((p) => {
  if (featured.length >= 8) return;
  if (seenCats.has(p.category)) return;
  seenCats.add(p.category);
  featured.push(p);
});
while (featured.length < 8 && featured.length < products.length) {
  const next = products.find((p) => !featured.includes(p));
  if (!next) break;
  featured.push(next);
}

document.getElementById("featured").innerHTML = featured.map(Warners.productCard).join("");

const recs = Warners.getRecommendations().slice(0, 8);
const recGrid = document.getElementById("home-recs");
if (recGrid) {
  recGrid.innerHTML = recs.length
    ? recs.map(Warners.productCard).join("")
    : `<div class="panel meta">View a product or add something to your cart to see personal picks.</div>`;
}

const count = document.getElementById("home-count");
if (count) count.textContent = `(${products.length})`;

const all = document.getElementById("home-all");
if (all) all.innerHTML = products.map(Warners.productCard).join("");
