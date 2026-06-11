<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; // In a real app, hash this using password_hash()

    $query = "SELECT id FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        header("Location: dashboard.php");
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        #strength-text { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login / Register</h2>
    <?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form id="loginForm" method="POST" onsubmit="return validateForm()">
        <input type="text" id="username" name="username" placeholder="Username" required>
        <input type="password" id="password" name="password" placeholder="Password" onkeyup="checkPasswordStrength()" required>
        <span id="strength-text"></span>
        <button type="submit">Login</button>
    </form>
</div>

<script>
    // Week 3: JavaScript Form Validation
    function validateForm() {
        let user = document.getElementById("username").value;
        if (user.trim() === "") {
            alert("Username cannot be empty!");
            return false;
        }
        return true;
    }

    // Week 3: Password Strength Checker
    function checkPasswordStrength() {
        let pass = document.getElementById("password").value;
        let text = document.getElementById("strength-text");
        if (pass.length === 0) { text.innerHTML = ""; }
        else if (pass.length < 5) { text.innerHTML = "Weak"; text.style.color = "red"; }
        else if (pass.length < 8) { text.innerHTML = "Medium"; text.style.color = "orange"; }
        else { text.innerHTML = "Strong"; text.style.color = "green"; }
    }
</script>

</body>
</html>