<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";
$user_id = $_SESSION['user_id'];

// Handle username update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['new_username']);

    if (empty($new_username)) {
        $error = "Username cannot be empty";
    } elseif (strlen($new_username) < 3) {
        $error = "Username must be at least 3 characters";
    } else {
        // Check if username already exists
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_stmt->execute([$new_username, $user_id]);
        
        if ($check_stmt->fetch()) {
            $error = "Username already taken";
        } else {
            // Update username
            $update_stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            if ($update_stmt->execute([$new_username, $user_id])) {
                $_SESSION['username'] = $new_username;
                $message = "Username updated successfully!";
            } else {
                $error = "Failed to update username";
            }
        }
    }
}

// Get current user info
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
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e2a, #1a1f3a);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        
        .profile-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: rgba(100, 150, 255, 0.7);
            box-shadow: 0 0 0 2px rgba(100, 150, 255, 0.3);
        }
        
        button {
            width: 100%;
            padding: 0.8rem;
            background: rgba(100, 150, 255, 0.7);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        button:hover {
            background: rgba(100, 150, 255, 0.9);
            transform: translateY(-2px);
        }
        
        .message {
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .success {
            background: rgba(40, 167, 69, 0.3);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        
        .error {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        
        .current-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .logout-link {
            text-align: center;
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .logout-link a {
            color: rgba(100, 150, 255, 1);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container glass">
        <h1>My Profile</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="current-info">
            <strong>Current Username:</strong> <?php echo htmlspecialchars($current_username); ?>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="new_username">New Username</label>
                <input type="text" id="new_username" name="new_username" placeholder="Enter new username" required>
            </div>
            <button type="submit">Update Username</button>
        </form>
        
        <div class="logout-link">
            <a href="index.php">Back to App</a> |
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>