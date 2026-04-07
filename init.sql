USE web_sales;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    promo_price DECIMAL(10,2) DEFAULT NULL,
    demo_link VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) NOT NULL UNIQUE,
    key_value TEXT
);

CREATE TABLE IF NOT EXISTS traffic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(50),
    user_agent TEXT,
    page_visited VARCHAR(255),
    visit_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin: admin / admin123 (hashed)
INSERT IGNORE INTO users (username, password) VALUES ('admin', '$2y$10$wE9mHhU8P3n.z3E.Z/0X.ezX28LwEqGq8HjBxKxPZ1sSxg8W0qHve');

-- Insert default settings
INSERT IGNORE INTO settings (key_name, key_value) VALUES 
('site_title', 'Premium Web Solutions'),
('site_description', 'Boutique web development and ready-to-use premium templates.'),
('developer_contact', 'https://wa.me/628000000000'),
('meta_keywords', 'web agency, premium web, templates');
