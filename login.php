<?php
session_start();
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $message = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - hightutor.ai</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>hightutor.ai</h2>
        <?php if ($message): ?>
            <p class="error-msg"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" autocomplete="username" required>
            <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
            <button type="submit">Login</button>
        </form>
        <div class="auth-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>
