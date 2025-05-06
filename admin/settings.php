<?php
   require_once 'auth.php';
   requireLogin();
   require_once '../src/db.php';

   $conn = getDbConnection();
   $error = '';
   $success = '';

   // Fetch current settings
   $settings = $conn->query("SELECT * FROM settings WHERE id = 1")->fetch_assoc();

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
           $error = 'Invalid CSRF token';
       } else {
           $youtube_url = trim($_POST['youtube_url'] ?? '');
           $signup_button_color = trim($_POST['signup_button_color'] ?? '');
           $background_color_start = trim($_POST['background_color_start'] ?? '');
           $background_color_end = trim($_POST['background_color_end'] ?? '');
           $page_title = trim($_POST['page_title'] ?? '');
           $paragraph_text = trim($_POST['paragraph_text'] ?? '');
           $site_title = trim($_POST['site_title'] ?? '');

           // Basic validation
           if (empty($youtube_url) || empty($signup_button_color) || empty($background_color_start) || empty($background_color_end) || empty($page_title) || empty($paragraph_text) || empty($site_title)) {
               $error = 'All fields are required';
           } elseif (!filter_var($youtube_url, FILTER_VALIDATE_URL)) {
               $error = 'Invalid YouTube URL';
           } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $signup_button_color) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $background_color_start) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $background_color_end)) {
               $error = 'Invalid color format';
           } else {
               $stmt = $conn->prepare("
                   UPDATE settings
                   SET youtube_url = ?,
                       signup_button_color = ?,
                       background_color_start = ?,
                       background_color_end = ?,
                       page_title = ?,
                       paragraph_text = ?,
                       site_title = ?,
                       updated_at = CURRENT_TIMESTAMP
                   WHERE id = 1
               ");
               $stmt->bind_param(
                   "sssssss",
                   $youtube_url,
                   $signup_button_color,
                   $background_color_start,
                   $background_color_end,
                   $page_title,
                   $paragraph_text,
                   $site_title
               );

               if ($stmt->execute()) {
                   $success = 'Settings updated successfully';
                   $settings = $conn->query("SELECT * FROM settings WHERE id = 1")->fetch_assoc();
               } else {
                   $error = 'Failed to update settings';
               }
               $stmt->close();
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
       <title>Admin Settings - (not)badtech</title>
       <script src="https://cdn.tailwindcss.com"></script>
       <link rel="stylesheet" href="../css/styles.css">
   </head>
   <body class="bg-gray-100 min-h-screen">
       <div class="container mx-auto p-6">
           <div class="flex justify-between items-center mb-6">
               <h1 class="text-3xl font-bold text-[#1e3c72]">Admin Settings</h1>
               <div>
                   <a href="dashboard.php" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg hover:bg-[#1e3c72] mr-2">Dashboard</a>
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
                       <label for="youtube_url" class="block text-sm font-bold text-gray-800 mb-1">YouTube URL</label>
                       <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url']); ?>" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                   </div>
                   <div>
                       <label for="signup_button_color" class="block text-sm font-bold text-gray-800 mb-1">Sign Up Button Color</label>
                       <input type="color" id="signup_button_color" name="signup_button_color" value="<?php echo htmlspecialchars($settings['signup_button_color']); ?>" required class="w-full h-10 p-1 border border-gray-300 rounded-lg">
                   </div>
                   <div>
                       <label for="background_color_start" class="block text-sm font-bold text-gray-800 mb-1">Background Color (Start)</label>
                       <input type="color" id="background_color_start" name="background_color_start" value="<?php echo htmlspecialchars($settings['background_color_start']); ?>" required class="w-full h-10 p-1 border border-gray-300 rounded-lg">
                   </div>
                   <div>
                       <label for="background_color_end" class="block text-sm font-bold text-gray-800 mb-1">Background Color (End)</label>
                       <input type="color" id="background_color_end" name="background_color_end" value="<?php echo htmlspecialchars($settings['background_color_end']); ?>" required class="w-full h-10 p-1 border border-gray-300 rounded-lg">
                   </div>
                   <div>
                       <label for="page_title" class="block text-sm font-bold text-gray-800 mb-1">Page Title</label>
                       <input type="text" id="page_title" name="page_title" value="<?php echo htmlspecialchars($settings['page_title']); ?>" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                   </div>
                   <div>
                       <label for="paragraph_text" class="block text-sm font-bold text-gray-800 mb-1">Paragraph Text</label>
                       <textarea id="paragraph_text" name="paragraph_text" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"><?php echo htmlspecialchars($settings['paragraph_text']); ?></textarea>
                   </div>
                   <div>
                       <label for="site_title" class="block text-sm font-bold text-gray-800 mb-1">Site Title</label>
                       <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                   </div>
                   <button type="submit" class="w-full bg-[#2a5298] text-white p-3 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">Save Settings</button>
               </form>
           </div>
       </div>
   </body>
   </html>