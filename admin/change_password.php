<?php
require_once 'auth.php';
requireLogin();
require_once '../src/db.php';

$conn = getDbConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All fields are required';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New password and confirm password do not match';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters long';
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['admin_user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!password_verify($current_password, $row['password'])) {
                $error = 'Current password is incorrect';
            } else {
                // Update password
                $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $_SESSION['admin_user_id']);
                if ($stmt->execute()) {
                    $success = 'Password updated successfully';
                } else {
                    $error = 'Failed to update password';
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - (not)badtech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#1e3c72]">Change Password</h1>
            <div>
                <a href="dashboard.php" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg hover:bg-[#1e3c72] mr-2">Dashboard</a>
                <a href="settings.php" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg hover:bg-[#1e3c72] mr-2">Settings</a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
            </div>
        </div>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="text-green-600 text-sm mb-4"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                <div>
                    <label for="current_password" class="block text-sm font-bold text-gray-800 mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-bold text-gray-800 mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-bold text-gray-800 mb-1">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                </div>
                <button type="submit" class="w-full bg-[#2a5298] text-white p-3 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>