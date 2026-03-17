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
    if (!$user) throw new Exception("User not found");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
</head>
<body>

<header class="header">
    <div class="header-logo">hightutor.ai</div>
    <div class="header-user">
        <span>Hi, <?= htmlspecialchars($user['username'] ?? 'Guest') ?></span>
        <a href="profile.php" class="btn-glass">Profile</a>
        <a href="logout.php" class="btn-glass btn-danger">Logout</a>
    </div>
</header>

<div class="app-layout">
    <div class="chat-scroll" id="chatScroll">
        <div class="chat-inner" id="chat">
            <div class="message bot-message">
                Hello! I&rsquo;m your hightutor.ai assistant. Select a mode and subject, then ask me anything!
            </div>
        </div>
    </div>

    <!-- Flashcard view (hidden by default) -->
    <div class="chat-scroll" id="flashcardView" style="display:none;">
        <div class="chat-inner">
            <div class="flashcard-wrap">
                <div class="flashcard" id="flashcard" onclick="flipCard()">
                    <div class="flashcard-inner">
                        <div class="flashcard-front" id="fcFront">Click to generate flashcards first.</div>
                        <div class="flashcard-back" id="fcBack"></div>
                    </div>
                </div>
                <div class="flashcard-counter" id="fcCounter"></div>
                <div class="flashcard-controls">
                    <button class="btn-glass" onclick="prevCard()">&#8592; Prev</button>
                    <button class="btn-glass" onclick="flipCard()">Flip</button>
                    <button class="btn-glass" onclick="nextCard()">Next &#8594;</button>
                </div>
                <div style="text-align:center; margin-top:1rem;">
                    <button class="btn-glass" onclick="exitFlashcards()">Back to Chat</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="controls-bar">
    <div class="controls-inner">
        <select id="mode">
            <option value="general">General (Socratic)</option>
            <option value="flashcards">Flashcards</option>
            <option value="turbo">Turbo (Bullets)</option>
            <option value="quiz">Quiz Practice</option>
            <option value="vocab">Vocab</option>
        </select>
        <select id="subject">
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
        <select id="level">
            <option value="">Level</option>
            <option value="honors">Honors</option>
            <option value="ap">AP</option>
            <option value="ib">IB</option>
            <option value="ccap">CCAP</option>
            <option value="regular">Regular</option>
        </select>
        <input type="text" id="userInput" placeholder="Ask a question or share a problem..." autocomplete="off">
        <button class="btn-send" id="sendBtn">Send</button>
    </div>
</div>

<script>
const chat       = document.getElementById('chat');
const chatScroll = document.getElementById('chatScroll');
const userInput  = document.getElementById('userInput');
const sendBtn    = document.getElementById('sendBtn');
const modeSelect = document.getElementById('mode');

// Flashcard state
let flashcards = [];
let fcIndex = 0;

function scrollToBottom() {
    chatScroll.scrollTop = chatScroll.scrollHeight;
}

function appendMessage(role, content) {
    const div = document.createElement('div');
    div.className = `message ${role === 'user' ? 'user-message' : 'bot-message'}`;
    if (role === 'bot' && typeof marked !== 'undefined') {
        div.innerHTML = marked.parse(content);
    } else {
        div.textContent = content;
    }
    chat.appendChild(div);
    scrollToBottom();
    return div;
}

function showTyping() {
    const div = document.createElement('div');
    div.className = 'message bot-message typing';
    div.id = 'typingIndicator';
    div.innerHTML = '<span></span><span></span><span></span>';
    chat.appendChild(div);
    scrollToBottom();
}

function removeTyping() {
    const t = document.getElementById('typingIndicator');
    if (t) t.remove();
}

async function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;

    const mode    = modeSelect.value;
    const subject = document.getElementById('subject').value;
    const level   = document.getElementById('level').value;

    appendMessage('user', message);
    userInput.value = '';
    userInput.disabled = true;
    sendBtn.disabled = true;
    showTyping();

    try {
        const payload = { message, mode };
        if (subject) payload.subject = subject;
        if (level)   payload.level   = level;

        const res  = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        removeTyping();

        if (data.error) {
            appendMessage('bot', 'Error: ' + data.error);
            return;
        }

        const reply = data.choices?.[0]?.message?.content ?? 'No response received.';

        if (mode === 'flashcards') {
            handleFlashcards(reply);
        } else {
            appendMessage('bot', reply);
        }
    } catch (e) {
        removeTyping();
        appendMessage('bot', 'Connection error. Please try again.');
        console.error(e);
    } finally {
        userInput.disabled = false;
        sendBtn.disabled = false;
        userInput.focus();
    }
}

// Flashcard handling
function handleFlashcards(reply) {
    try {
        const jsonMatch = reply.match(/\[[\s\S]*\]/);
        if (!jsonMatch) throw new Error('No JSON array found');
        flashcards = JSON.parse(jsonMatch[0]);
        if (!flashcards.length) throw new Error('Empty array');
        fcIndex = 0;
        showFlashcardView();
    } catch (e) {
        appendMessage('bot', 'Could not parse flashcards. Here\'s the raw response:\n\n' + reply);
    }
}

function showFlashcardView() {
    document.getElementById('chatScroll').style.display = 'none';
    document.getElementById('flashcardView').style.display = 'flex';
    renderCard();
}

function exitFlashcards() {
    document.getElementById('flashcardView').style.display = 'none';
    document.getElementById('chatScroll').style.display = 'flex';
    appendMessage('bot', `Done reviewing ${flashcards.length} flashcard(s)! Ask me anything else.`);
}

function renderCard() {
    if (!flashcards.length) return;
    const card = flashcards[fcIndex];
    document.getElementById('fcFront').textContent = card.front ?? card.question ?? 'No front';
    document.getElementById('fcBack').textContent  = card.back  ?? card.answer  ?? 'No back';
    document.getElementById('fcCounter').textContent = `Card ${fcIndex + 1} of ${flashcards.length}`;
    document.getElementById('flashcard').classList.remove('flipped');
}

function flipCard() {
    document.getElementById('flashcard').classList.toggle('flipped');
}

function nextCard() {
    if (fcIndex < flashcards.length - 1) {
        fcIndex++;
        renderCard();
    }
}

function prevCard() {
    if (fcIndex > 0) {
        fcIndex--;
        renderCard();
    }
}

// Event listeners
sendBtn.addEventListener('click', sendMessage);
userInput.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
</script>

</body>
</html>
