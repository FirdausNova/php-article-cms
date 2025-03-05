<?php
/**
 * Analytics System
 * This file contains functions for tracking and analyzing article statistics
 */

// Include database connection if not already included
require_once 'database.php';

// Create analytics tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS article_views (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    referer VARCHAR(255),
    view_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    session_id VARCHAR(255),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating article_views table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS search_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    results_count INT(11) NOT NULL,
    ip_address VARCHAR(45),
    search_date DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating search_logs table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS user_engagement (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT(11) NOT NULL,
    engagement_type ENUM('like', 'share', 'bookmark', 'time_spent') NOT NULL,
    user_identifier VARCHAR(255),
    value INT(11) DEFAULT 1,
    engagement_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating user_engagement table: " . $conn->error);
}

/**
 * Record a page view for an article
 *
 * @param int $artikel_id The article ID
 * @return bool True if successful, false otherwise
 */
function record_article_view($artikel_id) {
    global $conn;
    
    // Get visitor information
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $session_id = session_id();
    
    // Check if this view is already recorded in this session to prevent duplicate counts
    $sql = "SELECT id FROM article_views 
            WHERE artikel_id = ? AND session_id = ? AND view_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $artikel_id, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Already viewed in this session recently
        return true;
    }
    
    // Record the view
    $sql = "INSERT INTO article_views (artikel_id, ip_address, user_agent, referer, session_id) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $artikel_id, $ip_address, $user_agent, $referer, $session_id);
    
    return $stmt->execute();
}

/**
 * Record user engagement with an article
 *
 * @param int $artikel_id The article ID
 * @param string $engagement_type Type of engagement (like, share, bookmark, time_spent)
 * @param int $value Value for the engagement (default 1, used for time_spent in seconds)
 * @return bool True if successful, false otherwise
 */
function record_engagement($artikel_id, $engagement_type, $value = 1) {
    global $conn;
    
    // Get user identifier (session ID or user ID if logged in)
    $user_identifier = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : session_id();
    
    // Record the engagement
    $sql = "INSERT INTO user_engagement (artikel_id, engagement_type, user_identifier, value) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $artikel_id, $engagement_type, $user_identifier, $value);
    
    return $stmt->execute();
}

/**
 * Log a search query
 *
 * @param string $search_term The search term
 * @param int $results_count Number of results found
 * @return bool True if successful, false otherwise
 */
function log_search($search_term, $results_count) {
    global $conn;
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Log the search
    $sql = "INSERT INTO search_logs (search_term, results_count, ip_address) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $search_term, $results_count, $ip_address);
    
    return $stmt->execute();
}

/**
 * Get view count for an article
 *
 * @param int $artikel_id The article ID
 * @return int Number of views
 */
function get_article_views($artikel_id) {
    global $conn;
    
    $sql = "SELECT COUNT(DISTINCT session_id) as view_count 
            FROM article_views 
            WHERE artikel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $artikel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['view_count'] ?? 0;
}

/**
 * Get popular articles based on views
 *
 * @param int $limit Number of articles to return
 * @param int $days Number of days to look back
 * @return array Array of popular articles with view counts
 */
function get_popular_articles($limit = 5, $days = 30) {
    global $conn;
    
    $sql = "SELECT a.id, a.judul, a.slug, a.gambar, COUNT(DISTINCT av.session_id) as view_count 
            FROM artikel a 
            JOIN article_views av ON a.id = av.artikel_id 
            WHERE av.view_date > DATE_SUB(NOW(), INTERVAL ? DAY) 
            GROUP BY a.id 
            ORDER BY view_count DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $days, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = [];
    
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    
    return $articles;
}

/**
 * Get trending search terms
 *
 * @param int $limit Number of terms to return
 * @param int $days Number of days to look back
 * @return array Array of trending search terms with counts
 */
function get_trending_searches($limit = 10, $days = 7) {
    global $conn;
    
    $sql = "SELECT search_term, COUNT(*) as search_count 
            FROM search_logs 
            WHERE search_date > DATE_SUB(NOW(), INTERVAL ? DAY) 
            GROUP BY search_term 
            ORDER BY search_count DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $days, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $terms = [];
    
    while ($row = $result->fetch_assoc()) {
        $terms[] = $row;
    }
    
    return $terms;
}

/**
 * Get engagement statistics for an article
 *
 * @param int $artikel_id The article ID
 * @return array Engagement statistics
 */
function get_article_engagement($artikel_id) {
    global $conn;
    
    $stats = [
        'likes' => 0,
        'shares' => 0,
        'bookmarks' => 0,
        'avg_time_spent' => 0
    ];
    
    // Get likes, shares, bookmarks
    $sql = "SELECT engagement_type, COUNT(*) as count 
            FROM user_engagement 
            WHERE artikel_id = ? AND engagement_type IN ('like', 'share', 'bookmark') 
            GROUP BY engagement_type";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $artikel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $type = $row['engagement_type'];
        $count = $row['count'];
        
        if ($type == 'like') $stats['likes'] = $count;
        if ($type == 'share') $stats['shares'] = $count;
        if ($type == 'bookmark') $stats['bookmarks'] = $count;
    }
    
    // Get average time spent
    $sql = "SELECT AVG(value) as avg_time 
            FROM user_engagement 
            WHERE artikel_id = ? AND engagement_type = 'time_spent'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $artikel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stats['avg_time_spent'] = round($row['avg_time'] ?? 0);
    
    return $stats;
}

// Return true to indicate the analytics system is ready
return true;
?>