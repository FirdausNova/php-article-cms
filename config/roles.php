<?php
/**
 * Role Management System
 * This file contains functions for managing user roles and permissions
 */

// Include database connection if not already included
require_once 'database.php';

// Define role constants
define('ROLE_ADMIN', 1);       // Full access to everything
define('ROLE_EDITOR', 2);      // Can manage content but not users or settings
define('ROLE_AUTHOR', 3);      // Can create and edit own content only
define('ROLE_SUBSCRIBER', 4);  // Can comment and access premium content

// Create roles table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user_roles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    permissions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating user_roles table: " . $conn->error);
}

// Modify admin table to include role_id
$sql = "SHOW COLUMNS FROM admin LIKE 'role_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Add role_id column to admin table
    $sql = "ALTER TABLE admin ADD role_id INT(11) DEFAULT 1";
    if ($conn->query($sql) !== TRUE) {
        die("Error adding role_id to admin table: " . $conn->error);
    }
    
    // Add foreign key constraint
    $sql = "ALTER TABLE admin ADD CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES user_roles(id)";
    if ($conn->query($sql) !== TRUE) {
        // If this fails, it might be because the roles don't exist yet
        // We'll continue and insert the roles first
    }
}

// Check if default roles exist, if not create them
$sql = "SELECT * FROM user_roles LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create default roles with JSON-encoded permissions
    $roles = [
        [
            'Admin', 
            'Full access to all system features', 
            json_encode([
                'manage_users' => true,
                'manage_roles' => true,
                'manage_settings' => true,
                'manage_articles' => true,
                'manage_categories' => true,
                'manage_comments' => true,
                'view_statistics' => true
            ])
        ],
        [
            'Editor', 
            'Can manage all content but not users or settings', 
            json_encode([
                'manage_users' => false,
                'manage_roles' => false,
                'manage_settings' => false,
                'manage_articles' => true,
                'manage_categories' => true,
                'manage_comments' => true,
                'view_statistics' => true
            ])
        ],
        [
            'Author', 
            'Can create and edit own content only', 
            json_encode([
                'manage_users' => false,
                'manage_roles' => false,
                'manage_settings' => false,
                'manage_articles' => 'own',
                'manage_categories' => false,
                'manage_comments' => 'own',
                'view_statistics' => 'own'
            ])
        ],
        [
            'Subscriber', 
            'Can comment and access premium content', 
            json_encode([
                'manage_users' => false,
                'manage_roles' => false,
                'manage_settings' => false,
                'manage_articles' => false,
                'manage_categories' => false,
                'manage_comments' => 'own',
                'view_statistics' => false
            ])
        ]
    ];
    
    $sql = "INSERT INTO user_roles (id, name, description, permissions) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $id = 1;
    foreach ($roles as $role) {
        $stmt->bind_param("isss", $id, $role[0], $role[1], $role[2]);
        
        if (!$stmt->execute()) {
            die("Error creating default role: " . $stmt->error);
        }
        $id++;
    }
    
    $stmt->close();
}

/**
 * Check if a user has permission for a specific action
 *
 * @param int $admin_id The admin ID to check permissions for
 * @param string $permission The permission to check
 * @param int $resource_id Optional resource ID for own-resource permissions
 * @return bool True if user has permission, false otherwise
 */
function has_permission($admin_id, $permission, $resource_id = null) {
    global $conn;
    
    // Get user's role
    $sql = "SELECT r.permissions, a.id as admin_id 
            FROM admin a 
            JOIN user_roles r ON a.role_id = r.id 
            WHERE a.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false; // User not found
    }
    
    $row = $result->fetch_assoc();
    $permissions = json_decode($row['permissions'], true);
    
    // If permission doesn't exist in the array
    if (!isset($permissions[$permission])) {
        return false;
    }
    
    // If permission is boolean true, user has full permission
    if ($permissions[$permission] === true) {
        return true;
    }
    
    // If permission is 'own', check if user owns the resource
    if ($permissions[$permission] === 'own' && $resource_id !== null) {
        // Check ownership based on the permission type
        switch ($permission) {
            case 'manage_articles':
                $sql = "SELECT COUNT(*) as count FROM artikel WHERE id = ? AND author_id = ?";
                break;
            case 'manage_comments':
                $sql = "SELECT COUNT(*) as count FROM komentar WHERE id = ? AND admin_id = ?";
                break;
            default:
                return false; // Unknown resource type
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $resource_id, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
    
    return false;
}

/**
 * Get all available roles
 *
 * @return array Array of role objects
 */
function get_all_roles() {
    global $conn;
    
    $sql = "SELECT * FROM user_roles ORDER BY id";
    $result = $conn->query($sql);
    $roles = [];
    
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    
    return $roles;
}

/**
 * Get role details by ID
 *
 * @param int $role_id The role ID to get
 * @return array|null Role details or null if not found
 */
function get_role_by_id($role_id) {
    global $conn;
    
    $sql = "SELECT * FROM user_roles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Return true to indicate the roles system is ready
return true;
?>