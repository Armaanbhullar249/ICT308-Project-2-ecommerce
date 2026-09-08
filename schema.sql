-- Warner's Electronics — schema inferred from api/*.php
-- Import into database warners_electronics (phpMyAdmin Import, or mysql < schema.sql)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS warners_electronics
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warners_electronics;

DROP TABLE IF EXISTS support_messages;
DROP TABLE IF EXISTS customer_settings;
DROP TABLE IF EXISTS search_history;
DROP TABLE IF EXISTS product_activity;
DROP TABLE IF EXISTS recommendation_rules;
DROP TABLE IF EXISTS order_addresses;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS product_tags;
DROP TABLE IF EXISTS product_specs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','admin','owner') NOT NULL DEFAULT 'customer',
  first_name VARCHAR(80) NOT NULL DEFAULT '',
  last_name VARCHAR(80) NOT NULL DEFAULT '',
  email VARCHAR(190) NULL UNIQUE,
  phone VARCHAR(40) NOT NULL DEFAULT '',
  address VARCHAR(255) NOT NULL DEFAULT '',
  city VARCHAR(80) NOT NULL DEFAULT '',
  postal_code VARCHAR(20) NOT NULL DEFAULT '',
  country VARCHAR(80) NOT NULL DEFAULT '',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  brand VARCHAR(80) NOT NULL DEFAULT '',
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  subtitle VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT,
  image VARCHAR(255) NOT NULL DEFAULT '',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_specs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  specification VARCHAR(255) NOT NULL,
  CONSTRAINT fk_specs_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_tags (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  tag VARCHAR(80) NOT NULL,
  CONSTRAINT fk_tags_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recommendation_rules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rule_name VARCHAR(120) NOT NULL,
  trigger_action ENUM('view','cart','purchase') NOT NULL DEFAULT 'view',
  trigger_product_id INT UNSIGNED NOT NULL,
  recommended_product_id INT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_rules_trigger FOREIGN KEY (trigger_product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_rules_recommended FOREIGN KEY (recommended_product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('active','completed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_cart_product (cart_id, product_id),
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(40) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'processing',
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping_method VARCHAR(100) NOT NULL DEFAULT 'standard',
  payment_method VARCHAR(100) NOT NULL DEFAULT 'card',
  tracking_number VARCHAR(80) NOT NULL DEFAULT '',
  placed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(160) NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  address_type ENUM('billing','shipping') NOT NULL,
  first_name VARCHAR(80) NOT NULL DEFAULT '',
  last_name VARCHAR(80) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  phone VARCHAR(40) NOT NULL DEFAULT '',
  address VARCHAR(255) NOT NULL DEFAULT '',
  city VARCHAR(80) NOT NULL DEFAULT '',
  postal_code VARCHAR(20) NOT NULL DEFAULT '',
  country VARCHAR(80) NOT NULL DEFAULT '',
  CONSTRAINT fk_order_addresses_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_activity (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  activity_type ENUM('view','purchase') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_activity_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE search_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  search_term VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_search_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customer_settings (
  user_id INT UNSIGNED PRIMARY KEY,
  order_notifications TINYINT(1) NOT NULL DEFAULT 1,
  deal_notifications TINYINT(1) NOT NULL DEFAULT 1,
  recommendation_notifications TINYINT(1) NOT NULL DEFAULT 1,
  email_communication TINYINT(1) NOT NULL DEFAULT 1,
  sms_communication TINYINT(1) NOT NULL DEFAULT 0,
  marketing_communication TINYINT(1) NOT NULL DEFAULT 0,
  personalization TINYINT(1) NOT NULL DEFAULT 1,
  analytics TINYINT(1) NOT NULL DEFAULT 1,
  appearance ENUM('system','light','dark') NOT NULL DEFAULT 'system',
  CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  subject VARCHAR(190) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_support_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin login: username admin / password admin123 (change after first login)
INSERT INTO users (username, password_hash, role, first_name, last_name, email, status)
VALUES (
  'admin',
  '$2y$10$wtNaqfx3VfAHQ/qisHwj3esXjriPdTkFjrg.fsJJn0PCZ/jV3BxaC',
  'admin',
  'Store',
  'Admin',
  'admin@warners.local',
  'active'
);

INSERT INTO categories (name) VALUES
  ('Laptops'),
  ('Phones'),
  ('Audio'),
  ('Accessories');

INSERT INTO products (category_id, name, brand, price, stock, subtitle, description, image, is_active) VALUES
  (1, 'AeroBook 14', 'Warner', 1299.00, 12, 'Everyday laptop', 'A light 14-inch laptop for study and work.', '', 1),
  (1, 'StudioPro 16', 'Warner', 2199.00, 6, 'Creator laptop', 'High-performance 16-inch laptop for media work.', '', 1),
  (2, 'Pulse Phone', 'Warner', 899.00, 20, 'Flagship phone', 'A fast phone with a long-lasting battery.', '', 1),
  (2, 'Pulse Mini', 'Warner', 649.00, 18, 'Compact phone', 'A smaller phone that still handles daily apps well.', '', 1),
  (3, 'QuietBuds', 'Warner', 179.00, 40, 'Wireless earbuds', 'Noise-reducing earbuds for commute and focus.', '', 1),
  (3, 'Hall Speaker', 'Warner', 249.00, 15, 'Bluetooth speaker', 'Portable speaker with a full room sound.', '', 1),
  (4, 'ChargeHub', 'Warner', 59.00, 50, 'USB-C charger', 'A compact 65W charger for laptops and phones.', '', 1),
  (4, 'Shield Case', 'Warner', 29.00, 80, 'Phone case', 'Protective case for Pulse Phone.', '', 1);

INSERT INTO product_specs (product_id, specification) VALUES
  (1, '14-inch display'),
  (1, '16GB RAM'),
  (1, '512GB SSD'),
  (2, '16-inch display'),
  (2, '32GB RAM'),
  (2, '1TB SSD'),
  (3, '6.7-inch display'),
  (3, '128GB storage'),
  (5, 'Active noise reduction'),
  (5, '24-hour battery case');

INSERT INTO product_tags (product_id, tag) VALUES
  (1, 'laptop'), (1, 'study'),
  (2, 'laptop'), (2, 'pro'),
  (3, 'phone'), (4, 'phone'),
  (5, 'audio'), (6, 'audio'),
  (7, 'charger'), (8, 'case');

INSERT INTO recommendation_rules (rule_name, trigger_action, trigger_product_id, recommended_product_id, is_active) VALUES
  ('Phone needs a case', 'view', 3, 8, 1),
  ('Laptop needs a charger', 'cart', 1, 7, 1),
  ('Earbuds after a phone', 'purchase', 3, 5, 1);
