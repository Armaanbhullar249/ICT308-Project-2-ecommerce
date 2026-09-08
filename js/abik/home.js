/* Abik — customer home (public browse) */
Warners.renderHeader("home");
Warners.bindAddButtons();

const products = Warners.getProducts();

document.getElementById("featured").innerHTML = products
  .slice(0, 8)
  .map(Warners.productCard)
  .join("");

document.getElementById("home-recs").innerHTML = Warners.getRecommendations()
  .slice(0, 8)
  .map(Warners.productCard)
  .join("");

const count = document.getElementById("home-count");
if (count) count.textContent = `(${products.length})`;

const all = document.getElementById("home-all");
if (all) all.innerHTML = products.map(Warners.productCard).join("");
