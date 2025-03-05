-- Create database if not exists
CREATE DATABASE IF NOT EXISTS artikel_db;
USE artikel_db;

-- Create admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create kategori (categories) table
CREATE TABLE IF NOT EXISTS kategori (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create artikel (articles) table
CREATE TABLE IF NOT EXISTS artikel (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    konten TEXT NOT NULL,
    gambar VARCHAR(255),
    kategori_id INT(11) NOT NULL,
    tanggal_publikasi DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
);

-- Create komentar (comments) table
CREATE TABLE IF NOT EXISTS komentar (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    isi TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
);

-- Create article_views table for analytics
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

-- Create search_logs table for analytics
CREATE TABLE IF NOT EXISTS search_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    results_count INT(11) NOT NULL,
    ip_address VARCHAR(45),
    search_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create user_engagement table for analytics
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
INSERT INTO admin (username, password, nama, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com');

-- Insert sample categories
INSERT INTO kategori (nama, slug, deskripsi) VALUES
('Teknologi', 'teknologi', 'Artikel seputar teknologi terkini'),
('Kesehatan', 'kesehatan', 'Informasi tentang kesehatan dan gaya hidup sehat'),
('Pendidikan', 'pendidikan', 'Artikel tentang dunia pendidikan');