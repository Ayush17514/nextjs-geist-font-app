<?php
// dashboard.php
require_once 'config.php';
require_once 'functions.php';

// Check if admin logged in
requireLogin();

$pageTitle = "Dashboard";

$conn = db_connect();
$result = $conn->query("SELECT id, email, github_username, leetcode_username, linkedin_url, created_at FROM students ORDER BY created_at DESC");
$students = $result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$studentCount = getStudentCount();
$recentActivityCount = getRecentActivityCount();

$conn->close();
?>

<?php include 'views/header.php'; ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">Student Monitoring Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Monitor and track student activities across GitHub, LeetCode, and LinkedIn</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">S</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo $studentCount; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">A</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Recent Activities (24h)</dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo $recentActivityCount; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">M</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Monitoring Status</dt>
                            <dd class="text-lg font-medium text-gray-900">Active</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Students</h2>
        </div>
        
        <?php if(empty($students)): ?>
            <div class="p-6">
                <div class="text-center py-12">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-400 font-semibold">!</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Students Found</h3>
                    <p class="text-gray-500">No student records found in the database. Please add students to start monitoring.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GitHub</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LeetCode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LinkedIn</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($student['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($student['github_username']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $student['leetcode_username'] ? e($student['leetcode_username']) : '<span class="text-gray-400">Not set</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $student['linkedin_url'] ? '<span class="text-green-600">Set</span>' : '<span class="text-gray-400">Not set</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($student['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="student_details.php?id=<?php echo e($student['id']); ?>" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/footer.php'; ?>
