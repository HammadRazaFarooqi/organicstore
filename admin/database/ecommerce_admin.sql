-- SQL schema for ecommerce-admin
-- Create Database
CREATE DATABASE IF NOT EXISTS ecommerce_admin;
USE ecommerce_admin;

-- Admin Users Table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Users Table (customers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Discounts Table
CREATE TABLE discounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    product_id INT,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Settings Table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Admin User (password: admin123)
INSERT INTO admin_users (email, password, name) VALUES 
('admin@ecommerce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Insert Sample Data
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic devices and gadgets'),
('Clothing', 'Fashion and apparel'),
('Books', 'Books and educational materials');

INSERT INTO products (name, description, price, category_id, image) VALUES 
('Smartphone', 'Latest smartphone with advanced features', 599.99, 1, 'smartphone.jpg'),
('Laptop', 'High-performance laptop for professionals', 1299.99, 1, 'laptop.jpg'),
('T-Shirt', 'Comfortable cotton t-shirt', 29.99, 2, 'tshirt.jpg');

INSERT INTO users (name, email, phone, address) VALUES 
('John Doe', 'john@example.com', '+1234567890', '123 Main St, City, State'),
('Jane Smith', 'jane@example.com', '+0987654321', '456 Oak Ave, City, State');

INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'E-commerce Admin'),
('site_email', 'admin@ecommerce.com'),
('timezone', 'UTC');


ALTER TABLE products ADD discount_id INT NULL;
