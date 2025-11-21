<?php
session_start();

// --- 1. Connect to the database ---
// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';

// --- 2. Fetch all users ---
// **FIXED:** Changed 'id' to 'user_id' based on the error message.
$sql = "SELECT user_id, username, email, total_score, current_level, is_banned FROM users ORDER BY user_id ASC";
$result = $conn->query($sql);
$users = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users</title>
    <!-- Load Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for status badges */
        .status-banned {
            @apply bg-red-100 text-red-800;
        }
        .status-active {
            @apply bg-green-100 text-green-800;
        }
        /* Ensure responsive scrolling for the table on small screens */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-4 sm:p-8 font-sans">

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-xl shadow-2xl">
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-900">User Management Dashboard</h1>
            <a href="../Main/main.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Main
            </a>
        </div>
        
        <!-- Display Success/Error Messages from toggle_ban.php -->
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="p-4 mb-4 text-sm font-medium 
                <?php echo strpos($_SESSION['admin_message'], 'Error') !== false ? 'text-red-700 bg-red-100' : 'text-blue-700 bg-blue-100'; ?> 
                rounded-lg" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['admin_message']); 
                unset($_SESSION['admin_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-100 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found in the database.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($users as $user): ?>
                    <tr>
                        <!-- FIXED: Displaying user_id -->
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 truncate max-w-[150px] sm:max-w-none"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['total_score']); ?></td>
                        
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <?php 
                            // Display status badge based on 'is_banned' value (0 or 1)
                            if ($user['is_banned']) {
                                echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-banned">Banned</span>';
                            } else {
                                echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-active">Active</span>';
                            }
                            ?>
                        </td>

                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php if ($user['is_banned']): ?>
                                <!-- Link to UNBAN (status=0) - FIXED: Passing user_id -->
                                <a href="toggle_ban.php?user_id=<?php echo $user['user_id']; ?>&status=0" 
                                   class="text-green-600 hover:text-green-900 font-semibold transition duration-150 ease-in-out">
                                    Unban
                                </a>
                            <?php else: ?>
                                <!-- Link to BAN (status=1) - FIXED: Passing user_id -->
                                <a href="toggle_ban.php?user_id=<?php echo $user['user_id']; ?>&status=1" 
                                   class="text-red-600 hover:text-red-900 font-semibold transition duration-150 ease-in-out">
                                    Ban
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>