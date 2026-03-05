<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hightutor.ai - Elite AI Tutoring</title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <style>
        :root { --primary: #007bff; --dark: #1a1a1a; --light: #f4f4f4; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--light); margin: 0; display: flex; flex-direction: column; height: 100vh; }
        header { background: var(--dark); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .chat-container { flex: 1; overflow-y: auto; padding: 2rem; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        .message { margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; max-width: 85%; line-height: 1.6; }
        .user-message { background: var(--primary); color: white; margin-left: auto; }
        .bot-message { background: white; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .controls { background: white; padding: 1.5rem 2rem; border-top: 1px solid #ddd; display: flex; gap: 1rem; align-items: center; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        select, input, button { padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; }
        input { flex: 1; }
        button { background: var(--primary); color: white; border: none; cursor: pointer; font-weight: bold; transition: opacity 0.2s; }
        button:hover { opacity: 0.9; }
        button:disabled { background: #ccc; }
        .logout-btn { background: #dc3545; font-size: 0.9rem; padding: 0.5rem 1rem; text-decoration: none; color: white; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <div style="font-size: 1.5rem; font-weight: bold;">hightutor.ai</div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span>Hi, <?= htmlspecialchars($user['username']) ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="chat-container" id="chat">
        <div class="message bot-message">
            Hello! I am your elite tutor. Select a mode and tell me what you're working on today.
        </div>
    </div>

    <div class="controls">
        <select id="mode">
            <option value="general">General (Socratic)</option>
            <option value="flashcards">Flashcards</option>
            <option value="turbo">Turbo (Bullets)</option>
            <option value="quiz">Quiz Practice</option>
            <option value="vocab">Vocab</option>
        </select>
        <input type="text" id="userInput" placeholder="Ask a question or share a problem..." autocomplete="off">
        <button id="sendBtn">Send</button>
    </div>

    <script>
        const chat = document.getElementById('chat');
        const modeSelect = document.getElementById('mode');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');

        function appendMessage(role, text) {
            const div = document.createElement('div');
            div.className = `message ${role}-message`;
            div.innerHTML = role === 'bot' ? marked.parse(text) : text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        async function sendMessage() {
            const message = userInput.value.trim();
            const mode = modeSelect.value;
            if (!message) return;

            userInput.value = '';
            userInput.disabled = true;
            sendBtn.disabled = true;
            appendMessage('user', message);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type: application/json' },
                    body: JSON.stringify({ message, mode })
                });
                
                const data = await response.json();
                if (data.choices && data.choices[0].message) {
                    appendMessage('bot', data.choices[0].message.content);
                } else {
                    appendMessage('bot', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (e) {
                appendMessage('bot', 'Error connecting to the tutor.');
            } finally {
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
    </script>
</body>
</html>