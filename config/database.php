<?php
/**
 * Database Configuration File
 * This file contains the database connection settings
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default XAMPP username
define('DB_PASS', '');     // Default XAMPP password (empty)
define('DB_NAME', 'artikel_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Create tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS kategori (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating kategori table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS artikel (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    konten TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    kategori_id INT(11) NOT NULL,
    tanggal_publikasi DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating artikel table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating admin table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS komentar (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    isi TEXT NOT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating komentar table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS newsletter (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating newsletter table: " . $conn->error);
}

// Check if default admin exists, if not create one
$sql = "SELECT * FROM admin LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create default admin user (username: admin, password: admin123)
    $default_username = 'admin';
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $default_nama = 'Administrator';
    $default_email = 'admin@portalartikel.com';
    
    $sql = "INSERT INTO admin (username, password, nama, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $default_username, $default_password, $default_nama, $default_email);
    
    if (!$stmt->execute()) {
        die("Error creating default admin: " . $stmt->error);
    }
    
    $stmt->close();
}

// Check if default categories exist, if not create them
$sql = "SELECT * FROM kategori LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create default categories
    $categories = [
        ['Teknologi', 'teknologi', 'fa-laptop'],
        ['Kesehatan', 'kesehatan', 'fa-heartbeat'],
        ['Pendidikan', 'pendidikan', 'fa-graduation-cap'],
        ['Kuliner', 'kuliner', 'fa-utensils']
    ];
    
    $sql = "INSERT INTO kategori (nama, slug, icon) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($categories as $category) {
        $stmt->bind_param("sss", $category[0], $category[1], $category[2]);
        
        if (!$stmt->execute()) {
            die("Error creating default category: " . $stmt->error);
        }
    }
    
    $stmt->close();
}

// Include roles configuration first to ensure user_roles table exists
require_once 'roles.php';

// Then include users configuration
require_once 'users.php';

return $conn;
?>