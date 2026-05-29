<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Academic Record System</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .login-container { width: 300px; margin: 100px auto; background: white; padding: 30px; border-radius: 5px; }
        input { width: 100%; padding: 8px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Teacher Academic Record System</h2>
        <form method="POST" action="authenticate.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if(isset($_GET['error'])): ?>
            <p class="error">Invalid username or password</p>
        <?php endif; ?>
    </div>
</body>
</html>