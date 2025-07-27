<?php
// functions.php
// Helper functions for College Admin Student Monitoring System

require_once 'config.php';

/**
 * Connect to the MySQL database.
 * Returns: mysqli connection object or exits on error.
 */
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database Connection Error: " . $conn->connect_error);
        die("Database connection failed. Please check your configuration.");
    }
    $conn->set_charset("utf8");
    return $conn;
}

/**
 * Fetch data from GitHub for the given username.
 * Uses cURL to call GitHub API.
 */
function fetchGitHubData($username) {
    $url = "https://api.github.com/users/" . urlencode($username);
    $reposUrl = "https://api.github.com/users/" . urlencode($username) . "/repos";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "CollegeAdminMonitorApp/1.0");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Add token if available
    if(defined('GITHUB_API_TOKEN') && !empty(GITHUB_API_TOKEN)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: token " . GITHUB_API_TOKEN
        ]);
    }
    
    $userResponse = curl_exec($ch);
    if(curl_errno($ch)){
        error_log("GitHub API Error for user ($username): " . curl_error($ch));
        curl_close($ch);
        return null;
    }
    
    $userData = json_decode($userResponse, true);
    
    // Fetch repositories
    curl_setopt($ch, CURLOPT_URL, $reposUrl);
    $reposResponse = curl_exec($ch);
    $reposData = json_decode($reposResponse, true);
    
    curl_close($ch);
    
    if($userData && $reposData) {
        return [
            'user' => $userData,
            'repositories' => $reposData,
            'total_repos' => count($reposData),
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
    
    return null;
}

/**
 * Fetch data from LeetCode by scraping the user profile page.
 * Note: This is a basic implementation. LeetCode doesn't have official API.
 */
function fetchLeetCodeData($username) {
    $url = "https://leetcode.com/" . urlencode($username) . "/";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $data = @file_get_contents($url, false, $context);
    if (!$data) {
        error_log("LeetCode scraping failed for user: $username");
        return null;
    }
    
    // Basic parsing - extract title and some basic info
    $result = [];
    
    if (preg_match('/<title>(.*?)<\/title>/is', $data, $matches)) {
        $result['title'] = trim($matches[1]);
    }
    
    // Try to extract some profile information (this is basic and may need adjustment)
    if (preg_match('/Problems Solved.*?(\d+)/is', $data, $matches)) {
        $result['problems_solved'] = intval($matches[1]);
    }
    
    $result['profile_url'] = $url;
    $result['last_checked'] = date('Y-m-d H:i:s');
    $result['status'] = 'Profile accessible';
    
    return $result;
}

/**
 * Fetch LinkedIn data.
 * This function is a placeholder; LinkedIn API typically requires authentication.
 */
function fetchLinkedInData($profileUrl) {
    // Basic validation
    if (!filter_var($profileUrl, FILTER_VALIDATE_URL)) {
        error_log("Invalid LinkedIn URL: $profileUrl");
        return null;
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $data = @file_get_contents($profileUrl, false, $context);
    if (!$data) {
        error_log("LinkedIn data fetch failed for URL: $profileUrl");
        return null;
    }
    
    $result = [];
    
    // Extract page title
    if (preg_match('/<title>(.*?)<\/title>/is', $data, $matches)) {
        $result['title'] = trim($matches[1]);
    }
    
    // Try to extract name from meta tags
    if (preg_match('/<meta property="og:title" content="(.*?)"/is', $data, $matches)) {
        $result['name'] = trim($matches[1]);
    }
    
    $result['profile_url'] = $profileUrl;
    $result['last_checked'] = date('Y-m-d H:i:s');
    $result['status'] = 'Profile accessible';
    
    return $result;
}

/**
 * Utility function to safely escape output.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in as admin
 */
function isLoggedIn() {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

/**
 * Get student count
 */
function getStudentCount() {
    $conn = db_connect();
    $result = $conn->query("SELECT COUNT(*) as count FROM students");
    $row = $result->fetch_assoc();
    $conn->close();
    return $row['count'];
}

/**
 * Get recent activity count
 */
function getRecentActivityCount() {
    $conn = db_connect();
    $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
    
    $query = "SELECT 
        (SELECT COUNT(*) FROM github_activities WHERE fetched_at > '$yesterday') +
        (SELECT COUNT(*) FROM leetcode_activities WHERE fetched_at > '$yesterday') +
        (SELECT COUNT(*) FROM linkedin_activities WHERE fetched_at > '$yesterday') as count";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $conn->close();
    return $row['count'];
}
?>
