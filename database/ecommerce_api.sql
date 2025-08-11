-- E-Commerce API Database Dump
-- Created: 2025-01-11
-- Database: ecommerce_api

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS ecommerce_api;
CREATE DATABASE ecommerce_api;
\c ecommerce_api;

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Create users table
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    category_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create carts table
CREATE TABLE carts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create cart_items table
CREATE TABLE cart_items (
    id BIGSERIAL PRIMARY KEY,
    cart_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_cart_items_cart_id ON cart_items(cart_id);
CREATE INDEX idx_cart_items_product_id ON cart_items(product_id);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- Insert sample data

-- Insert users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Test User', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Hosting Hizmetleri', 'Web hosting ve sunucu hizmetleri'),
('Domain Hizmetleri', 'Alan adı kayıt ve yönetim hizmetleri'),
('Yazılım Ürünleri', 'Yazılım şirketi ürünleri ve çözümleri');

-- Insert products
INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES
-- Hosting Hizmetleri
('Başlangıç Hosting Paketi', 'Küçük web siteleri için uygun başlangıç hosting paketi', 29.99, 100, 1),
('Kurumsal Hosting Paketi', 'Büyük işletmeler için gelişmiş hosting çözümü', 89.99, 50, 1),
('VPS Sunucu', 'Sanal özel sunucu hizmeti', 149.99, 30, 1),
('Dedicated Sunucu', 'Özel sunucu hizmeti yüksek performans için', 299.99, 20, 1),
('Cloud Hosting', 'Bulut tabanlı hosting çözümü', 199.99, 40, 1),

-- Domain Hizmetleri
('.com Alan Adı', 'Yıllık .com alan adı kayıt hizmeti', 14.99, 500, 2),
('.com.tr Alan Adı', 'Türkiye için .com.tr alan adı kayıt hizmeti', 19.99, 300, 2),
('.net Alan Adı', 'Yıllık .net alan adı kayıt hizmeti', 16.99, 400, 2),
('.org Alan Adı', 'Organizasyonlar için .org alan adı', 18.99, 250, 2),
('Domain Transfer Hizmeti', 'Mevcut alan adınızı bize transfer edin', 9.99, 1000, 2),

-- Yazılım Ürünleri
('E-Ticaret Yazılımı', 'Online mağaza kurulumu için tam kapsamlı yazılım', 599.99, 25, 3),
('CRM Yazılımı', 'Müşteri ilişkileri yönetimi yazılımı', 399.99, 35, 3),
('Muhasebe Yazılımı', 'İşletme muhasebe işlemleri için yazılım', 299.99, 45, 3),
('Web Tasarım Yazılımı', 'Profesyonel web sitesi tasarım yazılımı', 199.99, 60, 3),
('Güvenlik Yazılımı', 'Siber güvenlik ve koruma yazılımı', 149.99, 80, 3);

-- Create carts for users
INSERT INTO carts (user_id) VALUES
(1), -- Admin cart
(2); -- User cart

-- Grant permissions (if needed)
-- GRANT ALL PRIVILEGES ON DATABASE ecommerce_api TO your_user;
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO your_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO your_user;
