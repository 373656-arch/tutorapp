<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    error_log("Error fetching user: " . $e->getMessage());
    $user = ['username' => 'Guest'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hightutor.ai - Elite AI Tutoring</title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
</head>
<body>
    <header class="header glass">
        <div style="font-size: 1.5rem; font-weight: bold; color: white;">hightutor.ai</div>
        <div class="header-user" style="display: flex; align-items: center; gap: 0.8rem;">
            <span style="white-space: nowrap; color: white;">Hi, <?= htmlspecialchars($user['username'] ?? 'Guest') ?></span>
            <a href="profile.php" style="padding: 0.5rem 1rem; border-radius: 4px; background: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; font-size: 0.9rem; white-space: nowrap; backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.3);">Profile</a>
            <a href="logout.php" class="logout-btn glass" style="background: rgba(220, 53, 69, 0.7);">Logout</a>
        </div>
    </header>

    <div class="chat-container glass" id="chat" style="margin-top: 80px; padding: 2rem; max-width: 900px; margin-left: auto; margin-right: auto; width: 90%;">
        <div class="message bot-message glass" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3);">
            Hello! I am your elite tutor. Select a mode and tell me what you're working on today.
        </div>
    </div>

    <!-- Flashcard and Quiz containers remain unchanged for now -->
    <div class="flashcard-container" id="flashcardContainer">
        <!-- Flashcard content -->
    </div>
    <div class="quiz-container" id="quizContainer">
        <!-- Quiz content -->
    </div>

    <div class="controls glass" style="position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-top: 1px solid rgba(255, 255, 255, 0.3); padding: 1.5rem; max-width: 900px; margin: 0 auto; width: 90%; box-sizing: border-box;">
        <select id="mode" class="glass" style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: white;">
            <option value="general">General (Socratic)</option>
            <option value="flashcards">Flashcards</option>
            <option value="turbo">Turbo (Bullets)</option>
            <option value="quiz">Quiz Practice</option>
            <option value="vocab">Vocab</option>
        </select>
        <select id="subject" class="glass" style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: white;">
            <option value="">Subject</option>
            <option value="algebra1">Algebra I</option>
            <option value="algebra2">Algebra II</option>
            <option value="geometry">Geometry</option>
            <option value="precalc">Precalculus</option>
            <option value="calculus">Calculus</option>
            <option value="statistics">Statistics</option>
            <option value="biology">Biology</option>
            <option value="chemistry">Chemistry</option>
            <option value="physics">Physics</option>
            <option value="env_science">Environmental Science</option>
            <option value="world_history">World History</option>
            <option value="american_history">American History</option>
            <option value="european_history">European History</option>
            <option value="english_comp">English Composition</option>
            <option value="literature">Literature</option>
            <option value="spanish">Spanish</option>
            <option value="french">French</option>
            <option value="mandarin">Mandarin Chinese</option>
            <option value="latin">Latin</option>
            <option value="economics">Economics</option>
            <option value="psychology">Psychology</option>
            <option value="computer_science">Computer Science</option>
        </select>
        <select id="level" class="glass" style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: white;">
            <option value="">Level</option>
            <option value="honors">Honors</option>
            <option value="ap">AP</option>
            <option value="ib">IB</option>
            <option value="ccap">CCAP</option>
            <option value="regular">Regular</option>
        </select>
        <input type="text" id="userInput" class="glass" placeholder="Ask a question or share a problem..." autocomplete="off" style="flex: 1; padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: white;">
        <button id="sendBtn" class="glass" style="padding: 0.8rem; background: rgba(100, 150, 255, 0.7); border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold;">Send</button>
    </div>

    <!-- Rest of your JavaScript and logic remains the same -->
    <script>
        // Your existing JavaScript here
    </script>
</body>
</html>