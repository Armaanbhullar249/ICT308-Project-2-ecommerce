# Warner's Electronics — ICT308 Project 2

Full-stack version of the Warner's Electronics project.

## Stack
- Frontend: HTML, CSS, JavaScript
- Backend: PHP 8+
- Database: MySQL
- Authentication: PHP sessions + password hashes stored in MySQL

## Run with XAMPP / PHP + MySQL
1. Put this project folder inside your web server folder, for example `C:\xampp\htdocs\warners-electronics`.
2. Start Apache and MySQL.
3. Make sure the MySQL database you already created is named `warners_electronics`.
4. If your MySQL username/password are different, edit `api/config/database.php`.
5. Open `http://localhost/warners-electronics/home.html` in the browser. Do not open the HTML files directly with `file://` because PHP APIs need a web server.

## Accounts
- Customers create their own accounts from `login.html` using **Create Account**.
- Admin uses the Admin record you already seeded in the `users` table.
- To create an Owner without hardcoding credentials, let that person create a normal customer account first. Then log in as Admin, open **Settings**, and use **Promote to Owner**.

## Backend features
- Database login/signup/logout and password hashing
- Session-based role access for Customer, Admin and Owner
- Product/category CRUD
- Product images uploaded to `uploads/products/`
- Cart stored in MySQL after login; guest cart is merged on login
- Checkout, orders, order items, stock deduction and tracking numbers
- Customer profile and settings
- Recommendation rules and customer activity history
- Search history
- Support messages
- Admin catalogue/reports
- Owner reports and customer access control

Small display-only product metadata that was not part of the SQL schema (gallery images and recommendation weight) is stored server-side in `uploads/products/meta_*.json` so the existing frontend feature is preserved without changing your database schema.

## Database connection
Default settings in `api/config/database.php` are:
- Host: `127.0.0.1`
- Port: `3306`
- Database: `warners_electronics`
- User: `root`
- Password: blank

You can edit those values or provide `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables.
