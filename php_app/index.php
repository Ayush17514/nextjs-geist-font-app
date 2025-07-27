<?php
// index.php
// Entry point for College Admin Student Monitoring System

require_once 'config.php';
require_once 'functions.php';

// Redirect based on login status
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
