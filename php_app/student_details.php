<?php
// student_details.php
require_once 'config.php';
require_once 'functions.php';

// Check login
requireLogin();

$conn = db_connect();
$studentId = intval($_GET['id'] ?? 0);

if ($studentId <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Retrieve student basic info
$stmt = $conn->prepare("SELECT email, github_username, leetcode_username, linkedin_url, created_at FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = "Student Details - " . $student['email'];

// Retrieve latest activity data
$githubData = null;
$githubDate = null;
$leetcodeData = null;
$leetcodeDate = null;
$linkedinData = null;
$linkedinDate = null;

// GitHub Data
$stmt = $conn->prepare("SELECT data, fetched_at FROM github_activities WHERE student_id = ? ORDER BY fetched_at DESC LIMIT 1");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()){
    $githubData = json_decode($row['data'], true);
    $githubDate = $row['fetched_at'];
}
$stmt->close();

// LeetCode Data
$stmt = $conn->prepare("SELECT data, fetched_at FROM leetcode_activities WHERE student_id = ? ORDER BY fetched_at DESC LIMIT 1");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()){
    $leetcodeData = json_decode($row['data'], true);
    $leetcodeDate = $row['fetched_at'];
}
$stmt->close();

// LinkedIn Data
$stmt = $conn->prepare("SELECT data, fetched_at FROM linkedin_activities WHERE student_id = ? ORDER BY fetched_at DESC LIMIT 1");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()){
    $linkedinData = json_decode($row['data'], true);
    $linkedinDate = $row['fetched_at'];
}
$stmt->close();

$conn->close();
?>

<?php include 'views/header.php'; ?>

<div class="space-y-6">
    <!-- Back Button and Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 transition-colors">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Student Details</h1>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo e($student['email']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">GitHub Username</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo e($student['github_username']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">LeetCode Username</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?php echo $student['leetcode_username'] ? e($student['leetcode_username']) : '<span class="text-gray-400">Not set</span>'; ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">LinkedIn Profile</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?php if($student['linkedin_url']): ?>
                            <a href="<?php echo e($student['linkedin_url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View Profile</a>
                        <?php else: ?>
                            <span class="text-gray-400">Not set</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- GitHub Activity -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">GitHub Activity</h2>
        </div>
        <div class="px-6 py-4">
            <?php if($githubData): ?>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Updated: <?php echo formatDate($githubDate); ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Data Available
                        </span>
                    </div>
                    
                    <?php if(isset($githubData['user'])): ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Public Repositories</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($githubData['user']['public_repos'] ?? 0); ?></div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Followers</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($githubData['user']['followers'] ?? 0); ?></div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Following</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($githubData['user']['following'] ?? 0); ?></div>
                            </div>
                        </div>
                        
                        <?php if(isset($githubData['repositories']) && !empty($githubData['repositories'])): ?>
                            <div class="mt-6">
                                <h3 class="text-md font-medium text-gray-900 mb-3">Recent Repositories</h3>
                                <div class="space-y-2">
                                    <?php foreach(array_slice($githubData['repositories'], 0, 5) as $repo): ?>
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-medium text-gray-900"><?php echo e($repo['name']); ?></h4>
                                                    <?php if($repo['description']): ?>
                                                        <p class="text-sm text-gray-600 mt-1"><?php echo e($repo['description']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right text-sm text-gray-500">
                                                    <div><?php echo e($repo['language'] ?? 'N/A'); ?></div>
                                                    <div>Updated: <?php echo date('M j', strtotime($repo['updated_at'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-400 font-semibold">G</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No GitHub Data</h3>
                    <p class="text-gray-500">No GitHub activity data available for this student.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LeetCode Activity -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">LeetCode Activity</h2>
        </div>
        <div class="px-6 py-4">
            <?php if($leetcodeData): ?>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Updated: <?php echo formatDate($leetcodeDate); ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Data Available
                        </span>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e(json_encode($leetcodeData, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-400 font-semibold">L</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No LeetCode Data</h3>
                    <p class="text-gray-500">No LeetCode activity data available for this student.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LinkedIn Activity -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">LinkedIn Activity</h2>
        </div>
        <div class="px-6 py-4">
            <?php if($linkedinData): ?>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Updated: <?php echo formatDate($linkedinDate); ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Data Available
                        </span>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e(json_encode($linkedinData, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-400 font-semibold">L</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No LinkedIn Data</h3>
                    <p class="text-gray-500">No LinkedIn activity data available for this student.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>
