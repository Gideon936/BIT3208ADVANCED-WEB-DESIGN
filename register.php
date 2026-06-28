<?php
session_start();
require 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Week 6: Input Validation
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $message = "Username and password are required!";
    } else {
        $username = $_POST['username'];
        
        // Week 7: Password Hashing (Never store plain text passwords)
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Week 6: Prevent SQL Injection using Prepared Statements
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute()) {
            $message = "<span style='color:green;'>Registration Successful! <a href='index.php'>Click here to Login</a></span>";
        } else {
            $message = "<span style='color:red;'>Error: Username might already exist.</span>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Task Manager</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Create Account</h2>
    <p><?php echo $message; ?></p>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <p style="text-align:center; font-size: 12px;"><a href="index.php">Already have an account? Login here.</a></p>
</div>
</body>
</html>