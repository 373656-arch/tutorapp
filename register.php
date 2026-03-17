<?php
session_start();
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "Username already exists";
        } else {
            $message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - hightutor.ai</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>Create Account</h2>
        <?php if ($message): ?>
            <p class="error-msg"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" autocomplete="username" required>
            <input type="password" name="password" placeholder="Password" autocomplete="new-password" required>
            <button type="submit">Register</button>
        </form>
        <div class="auth-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
