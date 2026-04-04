-- Create database
CREATE DATABASE IF NOT EXISTS odoo_pos_cafe;

USE odoo_pos_cafe;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    tax DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product variants
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute VARCHAR(100) NOT NULL,
    value VARCHAR(100) NOT NULL,
    extra_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Floors table
CREATE TABLE IF NOT EXISTS floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tables table
CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    table_number VARCHAR(50) NOT NULL,
    seats INT NOT NULL DEFAULT 4,
    status ENUM('available','occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE
);

-- POS sessions table
CREATE TABLE IF NOT EXISTS pos_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP NULL,
    closing_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    user_id INT NOT NULL,
    session_id INT,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES pos_sessions(id) ON DELETE SET NULL
);

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('cash','digital','upi') NOT NULL,
    status ENUM('pending','success','failed') DEFAULT 'pending',
    transaction_id VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Backend settings
CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT
);

-- Seed default payment settings
INSERT INTO config (config_key, config_value) VALUES
('payment_cash', '1'),
('payment_digital', '1'),
('payment_upi', '1'),
('payment_upi_id', '123@ybl.com')
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);

-- Seed default users
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@odoocafe.local', '$2y$10$6WbijGqmapW7Y6PhmkdPje2Z/h4.9zyoQP8pohTSGokeQNtwyaJFm', 'admin'),
('cashier', 'cashier@odoocafe.local', '$2y$10$sOeTgGfjBxz3AfbW4uVQh.WRUW3UIMV/eD3PRK5y0L/Z0Y7uYhqje', 'staff')
ON DUPLICATE KEY UPDATE email = VALUES(email), role = VALUES(role);
