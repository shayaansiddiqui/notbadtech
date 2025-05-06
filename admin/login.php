<?php
   require_once 'auth.php';

   if (isLoggedIn()) {
       header('Location: dashboard.php');
       exit;
   }

   $error = '';
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $username = trim($_POST['username'] ?? '');
       $password = trim($_POST['password'] ?? '');
       if (login($username, $password)) {
           header('Location: dashboard.php');
           exit;
       } else {
           $error = 'Invalid username or password';
       }
   }
   ?>

   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Admin Login - (not)badtech</title>
       <script src="https://cdn.tailwindcss.com"></script>
       <link rel="stylesheet" href="../css/styles.css">
   </head>
   <body class="bg-gradient-to-br from-[#1e3c72] to-[#2a5298] text-white min-h-screen flex items-center justify-center p-5">
       <div class="max-w-md w-full text-center bg-white/95 text-gray-800 rounded-3xl p-8 shadow-xl">
           <h1 class="text-3xl text-[#1e3c72] mb-6 font-bold">Admin Login</h1>
           <?php if ($error): ?>
               <p class="text-red-600 text-sm mb-4"><?php echo htmlspecialchars($error); ?></p>
           <?php endif; ?>
           <form method="POST" class="space-y-4">
               <div class="text-left">
                   <label for="username" class="block text-sm font-bold text-gray-800 mb-1">Username</label>
                   <input type="text" id="username" name="username" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
               </div>
               <div class="text-left">
                   <label for="password" class="block text-sm font-bold text-gray-800 mb-1">Password</label>
                   <input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
               </div>
               <button type="submit" class="w-full bg-[#2a5298] text-white p-3 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">Login</button>
           </form>
       </div>
   </body>
   </html>