<?php
// setup.php
// Setup script to help with initial configuration

require_once 'config.php';

$messages = [];
$errors = [];
$setupComplete = false;

// Check if setup is being run
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    
    // Test database connection
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        $errors[] = "Database connection failed: " . $conn->connect_error;
    } else {
        $messages[] = "✓ Database connection successful";
        
        // Check if database exists
        $dbExists = $conn->select_db(DB_NAME);
        if (!$dbExists) {
            // Try to create database
            if ($conn->query("CREATE DATABASE " . DB_NAME)) {
                $messages[] = "✓ Database '" . DB_NAME . "' created successfully";
                $conn->select_db(DB_NAME);
            } else {
                $errors[] = "✗ Failed to create database: " . $conn->error;
            }
        } else {
            $messages[] = "✓ Database '" . DB_NAME . "' exists";
        }
        
        // Check if tables exist
        $tablesExist = true;
        $requiredTables = ['admins', 'students', 'github_activities', 'leetcode_activities', 'linkedin_activities'];
        
        foreach ($requiredTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $tablesExist = false;
                break;
            }
        }
        
        if (!$tablesExist) {
            $messages[] = "⚠ Tables not found. Please import schema.sql manually through phpMyAdmin";
        } else {
            $messages[] = "✓ All required tables exist";
            
            // Check if admin user exists
            $result = $conn->query("SELECT COUNT(*) as count FROM admins");
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                $messages[] = "⚠ No admin users found. Please import schema.sql to create default admin";
            } else {
                $messages[] = "✓ Admin user(s) exist";
                $setupComplete = true;
            }
        }
        
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - College Admin Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">College Admin Monitor</h1>
                <p class="mt-2 text-gray-600">Setup & Configuration</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">System Setup</h2>
                
                <?php if (!empty($messages) || !empty($errors)): ?>
                    <div class="mb-6 space-y-4">
                        <?php if (!empty($messages)): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Setup Messages</h3>
                                <div class="text-sm text-blue-700 space-y-1">
                                    <?php foreach ($messages as $message): ?>
                                        <div><?php echo htmlspecialchars($message); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-red-800 mb-2">Errors</h3>
                                <div class="text-sm text-red-700 space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?php echo htmlspecialchars($error); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-2">Current Configuration</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div><strong>Database Host:</strong> <?php echo DB_HOST; ?></div>
                            <div><strong>Database User:</strong> <?php echo DB_USER; ?></div>
                            <div><strong>Database Name:</strong> <?php echo DB_NAME; ?></div>
                            <div><strong>GitHub API Token:</strong> <?php echo !empty(GITHUB_API_TOKEN) ? 'Set' : 'Not set'; ?></div>
                        </div>
                    </div>

                    <?php if ($setupComplete): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-green-800 mb-2">Setup Complete!</h3>
                            <p class="text-sm text-green-700 mb-3">Your system is ready to use.</p>
                            <a href="index.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                Go to Application
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="space-y-4">
                            <button type="submit" name="setup" value="1" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                Test Configuration
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Setup Instructions</h2>
                <div class="space-y-4 text-sm text-gray-600">
                    <div>
                        <h3 class="font-medium text-gray-900">1. Database Setup</h3>
                        <ul class="mt-1 ml-4 list-disc space-y-1">
                            <li>Ensure XAMPP MySQL is running</li>
                            <li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>
                            <li>Create database 'student_monitor'</li>
                            <li>Import the schema.sql file</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900">2. Default Login</h3>
                        <ul class="mt-1 ml-4 list-disc space-y-1">
                            <li>Username: <strong>admin</strong></li>
                            <li>Password: <strong>admin123</strong></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900">3. API Configuration (Optional)</h3>
                        <ul class="mt-1 ml-4 list-disc space-y-1">
                            <li>Add GitHub API token in config.php for better rate limits</li>
                            <li>Configure LinkedIn API credentials if available</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
