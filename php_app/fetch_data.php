<?php
// fetch_data.php
require_once 'config.php';
require_once 'functions.php';

// Check if admin logged in (for web access)
if (isset($_SERVER['HTTP_HOST'])) {
    requireLogin();
    $pageTitle = "Fetch Data";
}

$conn = db_connect();
$messages = [];
$errors = [];

// Fetch all students
$result = $conn->query("SELECT id, email, github_username, leetcode_username, linkedin_url FROM students");
$students = $result->fetch_all(MYSQLI_ASSOC);

if (empty($students)) {
    $messages[] = "No students found in database.";
} else {
    foreach ($students as $student) {
        $studentId = $student['id'];
        $studentEmail = $student['email'];
        
        $messages[] = "Processing student: " . $studentEmail;
        
        // Fetch GitHub data
        if (!empty($student['github_username'])) {
            $messages[] = "Fetching GitHub data for: " . $student['github_username'];
            $githubData = fetchGitHubData($student['github_username']);
            
            if ($githubData) {
                $stmt = $conn->prepare("INSERT INTO github_activities (student_id, data) VALUES (?, ?)");
                $dataJSON = json_encode($githubData);
                $stmt->bind_param("is", $studentId, $dataJSON);
                
                if ($stmt->execute()) {
                    $messages[] = "✓ GitHub data saved successfully";
                } else {
                    $errors[] = "✗ Failed to save GitHub data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "✗ Failed to fetch GitHub data for: " . $student['github_username'];
            }
        } else {
            $messages[] = "- No GitHub username set";
        }

        // Fetch LeetCode data (if username provided)
        if (!empty($student['leetcode_username'])) {
            $messages[] = "Fetching LeetCode data for: " . $student['leetcode_username'];
            $leetcodeData = fetchLeetCodeData($student['leetcode_username']);
            
            if ($leetcodeData) {
                $stmt = $conn->prepare("INSERT INTO leetcode_activities (student_id, data) VALUES (?, ?)");
                $dataJSON = json_encode($leetcodeData);
                $stmt->bind_param("is", $studentId, $dataJSON);
                
                if ($stmt->execute()) {
                    $messages[] = "✓ LeetCode data saved successfully";
                } else {
                    $errors[] = "✗ Failed to save LeetCode data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "✗ Failed to fetch LeetCode data for: " . $student['leetcode_username'];
            }
        } else {
            $messages[] = "- No LeetCode username set";
        }

        // Fetch LinkedIn data (if URL provided)
        if (!empty($student['linkedin_url'])) {
            $messages[] = "Fetching LinkedIn data for profile";
            $linkedinData = fetchLinkedInData($student['linkedin_url']);
            
            if ($linkedinData) {
                $stmt = $conn->prepare("INSERT INTO linkedin_activities (student_id, data) VALUES (?, ?)");
                $dataJSON = json_encode($linkedinData);
                $stmt->bind_param("is", $studentId, $dataJSON);
                
                if ($stmt->execute()) {
                    $messages[] = "✓ LinkedIn data saved successfully";
                } else {
                    $errors[] = "✗ Failed to save LinkedIn data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "✗ Failed to fetch LinkedIn data";
            }
        } else {
            $messages[] = "- No LinkedIn URL set";
        }
        
        $messages[] = "---";
    }
}

$conn->close();

// If accessed via command line, just output messages
if (!isset($_SERVER['HTTP_HOST'])) {
    foreach ($messages as $message) {
        echo $message . "\n";
    }
    foreach ($errors as $error) {
        echo $error . "\n";
    }
    echo "Data fetch complete.\n";
    exit();
}

// If accessed via web, show HTML page
?>

<?php include 'views/header.php'; ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">Fetch Student Data</h1>
            <p class="mt-1 text-sm text-gray-600">Manually trigger data collection from GitHub, LeetCode, and LinkedIn</p>
        </div>
    </div>

    <!-- Results -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Fetch Results</h2>
        </div>
        <div class="px-6 py-4">
            <?php if (!empty($messages) || !empty($errors)): ?>
                <div class="space-y-4">
                    <!-- Success Messages -->
                    <?php if (!empty($messages)): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-blue-800 mb-2">Process Log</h3>
                            <div class="text-sm text-blue-700 space-y-1">
                                <?php foreach ($messages as $message): ?>
                                    <div class="font-mono"><?php echo e($message); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-red-800 mb-2">Errors</h3>
                            <div class="text-sm text-red-700 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <div class="font-mono"><?php echo e($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Summary -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-green-800 mb-2">Summary</h3>
                        <div class="text-sm text-green-700">
                            <p>Processed <?php echo count($students); ?> student(s)</p>
                            <p>Success messages: <?php echo count($messages); ?></p>
                            <p>Errors: <?php echo count($errors); ?></p>
                            <p>Completed at: <?php echo date('Y-m-d H:i:s'); ?></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-400 font-semibold">!</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Data to Fetch</h3>
                    <p class="text-gray-500">No students found or no data could be fetched.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="flex space-x-4">
                <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Back to Dashboard
                </a>
                <a href="fetch_data.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Fetch Again
                </a>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">Automation Note</h3>
        <p class="text-sm text-yellow-700">
            For automated data collection, you can set up a cron job to run this script periodically:
        </p>
        <code class="block mt-2 text-xs bg-yellow-100 p-2 rounded">
            # Run every hour<br>
            0 * * * * /usr/bin/php <?php echo __DIR__; ?>/fetch_data.php
        </code>
    </div>
</div>

<?php include 'views/footer.php'; ?>
