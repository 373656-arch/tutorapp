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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .profile-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        button:active {
            background-color: #004085;
        }
        
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .logout-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-link a:hover {
            text-decoration: underline;
        }
        
        .current-info {
            background-color: #f9f9f9;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="profile-container">
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
                <input 
                    type="text" 
                    id="new_username" 
                    name="new_username" 
                    placeholder="Enter new username"
                    required
                >
            </div>
            <button type="submit">Update Username</button>
        </form>
        
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
