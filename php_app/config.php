<?php
// config.php
// Configuration file for College Admin Student Monitoring System

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Default XAMPP MySQL username
define('DB_PASS', '');      // Default XAMPP MySQL password (empty)
define('DB_NAME', 'student_monitor');

// API credentials (placeholders – update manually as needed)
define('GITHUB_API_TOKEN', ''); // Set your GitHub token here
define('LINKEDIN_API_KEY', ''); // Set your LinkedIn API key here

// Application settings
define('APP_NAME', 'College Admin Monitor');
define('APP_VERSION', '1.0.0');

// Error reporting (for debugging locally)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for login management
session_start();

// Set timezone
date_default_timezone_set('America/New_York');
?>
