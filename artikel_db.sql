-- Database creation
CREATE DATABASE IF NOT EXISTS artikel_db;
USE artikel_db;

-- Table: user_roles (must be created first for foreign key references)
CREATE TABLE IF NOT EXISTS user_roles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    permissions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO user_roles (id, name, description, permissions) VALUES
(1, 'Admin', 'Full access to all system features', '{"manage_users":true,"manage_roles":true,"manage_settings":true,"manage_articles":true,"manage_categories":true,"manage_comments":true,"view_statistics":true}'),
(2, 'Editor', 'Can manage all content but not users or settings', '{"manage_users":false,"manage_roles":false,"manage_settings":false,"manage_articles":true,"manage_categories":true,"manage_comments":true,"view_statistics":true}'),
(3, 'Author', 'Can create and edit own content only', '{"manage_users":false,"manage_roles":false,"manage_settings":false,"manage_articles":"own","manage_categories":false,"manage_comments":"own","view_statistics":"own"}'),
(4, 'Subscriber', 'Can comment and access premium content', '{"manage_users":false,"manage_roles":false,"manage_settings":false,"manage_articles":false,"manage_categories":false,"manage_comments":"own","view_statistics":false}')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Table: kategori
CREATE TABLE IF NOT EXISTS kategori (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: artikel
CREATE TABLE IF NOT EXISTS artikel (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    konten TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    kategori_id INT(11) NOT NULL,
    tanggal_publikasi DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- Table: admin (with role_id column)
CREATE TABLE IF NOT EXISTS admin (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role_id INT(11) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
);

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    foto VARCHAR(255) DEFAULT 'default.jpg',
    bio TEXT,
    status TINYINT(1) DEFAULT 1,
    role_id INT(11) DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
);

-- Table: komentar
CREATE TABLE IF NOT EXISTS komentar (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    isi TEXT NOT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
);

-- Table: newsletter
CREATE TABLE IF NOT EXISTS newsletter (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: article_views for analytics
CREATE TABLE IF NOT EXISTS article_views (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    referer VARCHAR(255),
    view_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    session_id VARCHAR(255),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
);

-- Table: search_logs for analytics
CREATE TABLE IF NOT EXISTS search_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    results_count INT(11) NOT NULL,
    ip_address VARCHAR(45),
    search_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: user_engagement for analytics
CREATE TABLE IF NOT EXISTS user_engagement (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    engagement_type ENUM('like', 'share', 'bookmark', 'time_spent') NOT NULL,
    user_identifier VARCHAR(255),
    value INT(11) DEFAULT 1,
    engagement_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
);

-- Insert default admin user
-- Username: admin, Password: admin123
INSERT INTO admin (username, password, nama, email, role_id) 
VALUES ('admin', '$2y$10$6Y5.Vzm9Vj9MfB7RUP9Xn.hR4AjGa4YfYr1C7a.MJaE0/XjX0gaPC', 'Administrator', 'admin@example.com', 1)
ON DUPLICATE KEY UPDATE id=id;

-- Insert default user
-- Username: user, Password: user123
INSERT INTO users (username, password, nama, email, role_id)
VALUES ('user', '$2y$10$YfwbXTFXDWNXrFkqJ8JfS.9KeYV2w.QJMj9D5wYwFNOz0RNBPBIBO', 'Regular User', 'user@example.com', 4)
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample categories
INSERT INTO kategori (nama, slug, icon) VALUES
('Teknologi', 'teknologi', 'fas fa-microchip'),
('Kesehatan', 'kesehatan', 'fas fa-heartbeat'),
('Pendidikan', 'pendidikan', 'fas fa-graduation-cap'),
('Bisnis', 'bisnis', 'fas fa-briefcase'),
('Gaya Hidup', 'gaya-hidup', 'fas fa-coffee')
ON DUPLICATE KEY UPDATE nama=VALUES(nama);