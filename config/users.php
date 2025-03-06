<?php
/**
 * User Management Configuration
 * This file handles user table creation and default user setup
 */

// Include database connection if not already included
if (!isset($conn)) {
    require_once 'database.php';
}

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    foto VARCHAR(255) DEFAULT 'default.jpg',
    bio TEXT,
    status TINYINT(1) DEFAULT 1,
    role_id INT(11) DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}

// Add foreign key constraint to role_id if it doesn't exist
$sql = "SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE 
       WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
       AND TABLE_NAME = 'users' 
       AND COLUMN_NAME = 'role_id' 
       AND REFERENCED_TABLE_NAME = 'user_roles'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Add foreign key constraint
    $sql = "ALTER TABLE users ADD CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES user_roles(id)";
    $conn->query($sql); // We don't die on error here as the roles table might not exist yet
}

// Check if default user exists, if not create one for testing
$sql = "SELECT * FROM users LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create default user (username: user, password: user123)
    $default_username = 'user';
    $default_password = password_hash('user123', PASSWORD_DEFAULT);
    $default_nama = 'Regular User';
    $default_email = 'user@example.com';
    $default_role_id = 4; // Subscriber role
    
    $sql = "INSERT INTO users (username, password, nama, email, role_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $default_username, $default_password, $default_nama, $default_email, $default_role_id);
    
    if (!$stmt->execute()) {
        die("Error creating default user: " . $stmt->error);
    }
    
    $stmt->close();
}

// Function to check if username exists
function username_exists($username, $conn) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to check if email exists
function email_exists($email, $conn) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Include this file in database.php to ensure users table is created
?>