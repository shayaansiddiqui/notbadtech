<?php
   session_start();
   require_once '../src/db.php';

   function isLoggedIn() {
       return isset($_SESSION['admin_user_id']);
   }

   function requireLogin() {
       if (!isLoggedIn()) {
           header('Location: login.php');
           exit;
       }
   }

   function login($username, $password) {
       $conn = getDbConnection();
       $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
       $stmt->bind_param("s", $username);
       $stmt->execute();
       $result = $stmt->get_result();
       if ($row = $result->fetch_assoc()) {
           if (password_verify($password, $row['password'])) {
               $_SESSION['admin_user_id'] = $row['id'];
               $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
               $stmt->close();
               $conn->close();
               return true;
           }
       }
       $stmt->close();
       $conn->close();
       return false;
   }

   function logout() {
       session_unset();
       session_destroy();
       header('Location: login.php');
       exit;
   }

   function generateCsrfToken() {
       if (!isset($_SESSION['csrf_token'])) {
           $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
       }
       return $_SESSION['csrf_token'];
   }

   function verifyCsrfToken($token) {
       return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
   }
   ?>