<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error   = "";
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['new_username']);

    if (empty($new_username)) {
        $error = "Username cannot be empty";
    } elseif (strlen($new_username) < 3) {
        $error = "Username must be at least 3 characters";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$new_username, $user_id]);
        if ($check->fetch()) {
            $error = "Username already taken";
        } else {
            $upd = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            if ($upd->execute([$new_username, $user_id])) {
                $_SESSION['username'] = $new_username;
                $message = "Username updated successfully!";
            } else {
                $error = "Failed to update username";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$current_username = $user['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - hightutor.ai</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="profile-body">
    <div class="profile-card">
        <h1>My Profile</h1>

        <?php if ($message): ?>
            <div class="msg-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-info">
            <strong>Current username:</strong> <?= htmlspecialchars($current_username) ?>
        </div>

        <form method="POST">
            <label for="new_username">New Username</label>
            <input type="text" id="new_username" name="new_username" placeholder="Enter new username" autocomplete="username" required>
            <button type="submit">Update Username</button>
        </form>

        <div class="profile-links">
            <a href="index.php">&#8592; Back to App</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
