# ICT308 Project 2 — Team split

Five people. Each person commits **only their files** using **their own GitHub name and email** so they appear under Contributors.

Shared files (`js/app.js`, `js/data.js`, `css/styles.css`, `css/theme-dark.css`) stay in the repo; Abik already has them on GitHub. Everyone else should commit the files listed under their name.

## How each person gets onto Contributors

1. Clone / pull the GitHub repo.
2. Set git to **your GitHub account** (use the email on your GitHub profile):

```bash
git config user.name "Your Full Name"
git config user.email "your-github-email@example.com"
```

3. Copy your files from `team-share/<your-name>/` into the project (they match the live site).
4. Commit and push **from your computer**, not Cursor:

```bash
git add <your files>
git commit -m "YourName: your pages and scripts"
git push
```

If GitHub still does not count you, the email in `git config user.email` does not match a verified email on GitHub.

---

## Abik — customer browse + shared shell

Pages: `home.html`, `search.html`, `index.html`

| Files |
|-------|
| `home.html` |
| `search.html` |
| `index.html` |
| `js/abik/home.js` |
| `js/abik/search.js` |
| `css/abik.css` |
| `js/app.js` (shared header, cart, auth helpers) |
| `js/data.js` (product catalogue) |
| `css/styles.css` (shared look) |
| `css/theme-dark.css` (dark / system appearance) |
| `TEAM.md` |
| `README.md` |

```bash
git add home.html search.html index.html js/abik css/abik.css js/app.js js/data.js css/styles.css css/theme-dark.css TEAM.md README.md
git commit -m "Abik: home, search, shared header and theme"
```

---

## Kushal — login + admin + owner

Pages: `login.html`, `admin.html`, `owner.html`

| Files |
|-------|
| `login.html` |
| `admin.html` |
| `owner.html` |
| `js/kushal/login.js` |
| `js/kushal/admin.js` |
| `js/kushal/owner.js` |
| `js/kushal/reports.js` |
| `css/kushal.css` |

```bash
git add login.html admin.html owner.html js/kushal css/kushal.css
git commit -m "Kushal: login, admin dashboard, owner workspace"
```

---

## Sukhman — cart, checkout, account, settings, support

Pages: `cart.html`, `checkout.html`, `account.html`, `settings.html`, `support.html`

| Files |
|-------|
| `cart.html` |
| `checkout.html` |
| `account.html` |
| `settings.html` |
| `support.html` |
| `js/sukhman/cart.js` |
| `js/sukhman/checkout.js` |
| `js/sukhman/account.js` |
| `js/sukhman/settings.js` |
| `js/sukhman/support.js` |
| `css/sukhman.css` |

```bash
git add cart.html checkout.html account.html settings.html support.html js/sukhman css/sukhman.css
git commit -m "Sukhman: cart, checkout, account, settings, support"
```

---

## Armajit — product details

Pages: `product.html`

| Files |
|-------|
| `product.html` |
| `js/armajit/product.js` |
| `css/armajit.css` |

```bash
git add product.html js/armajit css/armajit.css
git commit -m "Armajit: product detail page and gallery"
```

---

## Vedic — recommendations

Pages: `recommendations.html`

| Files |
|-------|
| `recommendations.html` |
| `js/vedic/recommendations.js` |
| `css/vedic.css` |

```bash
git add recommendations.html js/vedic css/vedic.css
git commit -m "Vedic: recommendations page"
```

---

## Shared (do not rewrite alone)

- `js/app.js` — login, roles, header, cart helpers, appearance
- `js/data.js` — product list
- `css/styles.css` — shared look
- `css/theme-dark.css` — dark mode for every page

To change **your page only**, put CSS in `css/<your-name>.css`.
