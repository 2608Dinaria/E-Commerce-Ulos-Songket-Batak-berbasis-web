-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert existing categories from products
INSERT IGNORE INTO categories (name)
SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '';
