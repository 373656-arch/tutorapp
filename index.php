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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <style>
        :root { --primary: #007bff; --dark: #1a1a1a; --light: #f4f4f4; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--light); margin: 0; display: flex; flex-direction: column; height: 100vh; }
        header { background: var(--dark); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .chat-container { flex: 1; overflow-y: auto; padding: 2rem; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; display: flex; flex-direction: column; }
        .chat-container.hidden { display: none; }
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
        
        /* Flashcard Styles */
        .flashcard-container { flex: 1; overflow-y: auto; padding: 2rem; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; display: none; flex-direction: column; align-items: center; justify-content: center; }
        .flashcard-container.active { display: flex; }
        .flashcard { perspective: 1000px; width: 100%; max-width: 600px; height: 300px; position: relative; }
        .flashcard-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); }
        .flashcard.flipped .flashcard-inner { transform: rotateY(180deg); }
        .flashcard-front, .flashcard-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; align-items: center; justify-content: center; border-radius: 8px; padding: 1rem; box-sizing: border-box; font-size: 1.1rem; }
        .flashcard-front { background: white; border: 1px solid #ddd; color: black; }
        .flashcard-back { background: var(--primary); color: white; transform: rotateY(180deg); }
        .flashcard-nav { display: flex; justify-content: center; gap: 1rem; margin-top: 1rem; width: 100%; }
    </style>
</head>
<body>
    <header>
        <div style="font-size: 1.5rem; font-weight: bold;">hightutor.ai</div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span>Hi, <?= htmlspecialchars($user['username'] ?? 'Guest') ?></span>
            <a href="profile.php" style="padding: 0.5rem 1rem; border-radius: 4px; background: #6c757d; color: white; text-decoration: none; font-size: 0.9rem;">Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="chat-container" id="chat">
        <div class="message bot-message">
            Hello! I am your elite tutor. Select a mode and tell me what you're working on today.
        </div>
    </div>

    <div class="flashcard-container" id="flashcardContainer">
        <div class="flashcard" id="flashcard">
            <div class="flashcard-inner">
                <div class="flashcard-front" id="flashcardFront">Front of the card</div>
                <div class="flashcard-back" id="flashcardBack">Back of the card</div>
            </div>
        </div>
        <div class="flashcard-nav">
            <button id="prevBtn">Previous</button>
            <button id="flipBtn">Flip Card</button>
            <button id="nextBtn">Next</button>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <span id="cardCounter">1/1</span>
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
        const flashcardContainer = document.getElementById('flashcardContainer');
        const modeSelect = document.getElementById('mode');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');

        // Flashcard elements
        const flashcard = document.getElementById('flashcard');
        const flashcardFront = document.getElementById('flashcardFront');
        const flashcardBack = document.getElementById('flashcardBack');
        const flipBtn = document.getElementById('flipBtn');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const cardCounter = document.getElementById('cardCounter');

        let currentMode = 'general';
        let flashcards = [];
        let currentCardIndex = 0;

        function appendMessage(role, text) {
            const div = document.createElement('div');
            div.className = `message ${role}-message`;
            div.innerHTML = role === 'bot' ? marked.parse(text) : text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        function showFlashcardMode() {
            chat.classList.add('hidden');
            flashcardContainer.classList.add('active');
        }

        function showChatMode() {
            chat.classList.remove('hidden');
            flashcardContainer.classList.remove('active');
        }

        async function fetchFlashcards(topic) {
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: `Create 5 flashcards about ${topic}`, mode: 'flashcards' })
                });
                
                const data = await response.json();
                let content = data.choices && data.choices[0].message ? data.choices[0].message.content : '[]';
                
                // Try to parse JSON from the response
                try {
                    let jsonMatch = content.match(/\[[\s\S]*\]/);
                    if (jsonMatch) {
                        flashcards = JSON.parse(jsonMatch[0]);
                    } else {
                        // Fallback: parse the response manually
                        flashcards = [];
                    }
                } catch (e) {
                    appendMessage('bot', 'Error parsing flashcards. Please try a different topic.');
                    showChatMode();
                    return;
                }
                
                if (flashcards.length === 0) {
                    appendMessage('bot', 'Could not generate flashcards. Please try a different topic.');
                    showChatMode();
                    return;
                }
                
                currentCardIndex = 0;
                updateFlashcard();
            } catch (e) {
                console.error('Flashcard fetch error:', e);
                appendMessage('bot', 'Error generating flashcards.');
                showChatMode();
            }
        }

        function updateFlashcard() {
            if (flashcards.length === 0) return;
            flashcardFront.textContent = flashcards[currentCardIndex].front;
            flashcardBack.textContent = flashcards[currentCardIndex].back;
            cardCounter.textContent = `${currentCardIndex + 1}/${flashcards.length}`;
        }

        flipBtn.addEventListener('click', () => {
            flashcard.classList.toggle('flipped');
        });

        prevBtn.addEventListener('click', () => {
            if (currentCardIndex > 0) {
                currentCardIndex--;
                flashcard.classList.remove('flipped');
                updateFlashcard();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentCardIndex < flashcards.length - 1) {
                currentCardIndex++;
                flashcard.classList.remove('flipped');
                updateFlashcard();
            }
        });

        modeSelect.addEventListener('change', () => {
            currentMode = modeSelect.value;
            if (currentMode === 'flashcards') {
                showChatMode();
                appendMessage('bot', 'Please enter the topic or subject you want flashcards for.');
            } else {
                showChatMode();
            }
        });

        async function sendMessage() {
            console.log("Send button clicked");
            const message = userInput.value.trim();
            const mode = modeSelect.value;
            if (!message) return;

            userInput.value = '';
            userInput.disabled = true;
            sendBtn.disabled = true;
            appendMessage('user', message);

            try {
                console.log("Sending:", { message, mode });
                if (mode === 'flashcards') {
                    appendMessage('bot', `Generating flashcards for: ${message}`);
                    await fetchFlashcards(message);
                    showFlashcardMode();
                } else {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ message, mode })
                    });
                    
                    const data = await response.json();
                    console.log("API Response:", data);
                    if (data.choices && data.choices[0].message) {
                        appendMessage('bot', data.choices[0].message.content);
                    } else {
                        appendMessage('bot', 'Error: ' + (data.error || 'Unknown error'));
                    }
                }
            } catch (e) {
                console.error("Fetch error:", e);
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