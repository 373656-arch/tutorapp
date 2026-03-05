<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .btn-logout { color: #fff; background-color: #dc3545; padding: 8px 15px; border-radius: 4px; text-decoration: none; }
        .btn-logout:hover { background-color: #c82333; }
        .btn-chat { color: #fff; background-color: #007bff; padding: 8px 15px; border-radius: 4px; text-decoration: none; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <div>
                <a href="test.php" class="btn-chat">Chat with AI</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>
        <p>This is your MVP website. You are successfully logged in.</p>
        <p>You can use the AI Chat feature to interact with the LLM integration.</p>
    </div>
</body>
</html>