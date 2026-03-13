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
        .controls { background: white; padding: 1.5rem 2rem; border-top: 1px solid #ddd; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        select, input, button { padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; }
        select { min-width: 140px; }
        input { flex: 1; min-width: 200px; }
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
        
        /* Quiz Styles */
        .quiz-container { flex: 1; overflow-y: auto; padding: 2rem; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box; display: none; flex-direction: column; }
        .quiz-container.active { display: flex; }
        .quiz-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 2rem; }
        .quiz-progress { flex: 1; }
        .quiz-progress span { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .progress-bar { width: 100%; height: 8px; background: #ddd; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); width: 0%; transition: width 0.3s; }
        .quiz-score { font-size: 1.1rem; font-weight: bold; white-space: nowrap; }
        .quiz-content { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .quiz-question { font-size: 1.3rem; font-weight: bold; margin-bottom: 2rem; color: var(--dark); }
        .quiz-options { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; }
        .quiz-option { padding: 1.2rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: white; }
        .quiz-option:hover { border-color: var(--primary); background: #f0f8ff; }
        .quiz-option.selected { border-color: var(--primary); background: #e7f3ff; }
        .quiz-option.correct { border-color: #28a745; background: #d4edda; color: #155724; }
        .quiz-option.incorrect { border-color: #dc3545; background: #f8d7da; color: #721c24; }
        .quiz-option.disabled { cursor: not-allowed; opacity: 0.6; }
        .quiz-nav { display: flex; justify-content: center; gap: 1rem; margin-top: 1rem; }
        .quiz-nav-btn { min-width: 120px; }
        .quiz-complete { display: none; text-align: center; }
        .quiz-complete.active { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .final-score { margin: 2rem 0; }
        .score-value { font-size: 3rem; font-weight: bold; color: var(--primary); }
        .score-text { font-size: 1.2rem; margin-top: 0.5rem; color: #666; }
        .hidden { display: none !important; }
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
        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
            <button id="flashcardContinueBtn">Continue Chat</button>
            <button id="flashcardRetryBtn">Start Over</button>
        </div>
    </div>

    <div class="quiz-container" id="quizContainer">
        <div class="quiz-header">
            <div class="quiz-progress">
                <span id="quizCounter">Question 1/1</span>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            <div class="quiz-score">Score: <span id="quizScore">0</span>/<span id="quizTotal">0</span></div>
        </div>
        <div class="quiz-content">
            <div class="quiz-question" id="quizQuestion">Question</div>
            <div class="quiz-options" id="quizOptions"></div>
        </div>
        <div class="quiz-nav">
            <button id="quizPrevBtn" class="quiz-nav-btn">Previous</button>
            <button id="quizNextBtn" class="quiz-nav-btn">Next Question</button>
        </div>
        <div class="quiz-complete hidden" id="quizComplete">
            <h2>Quiz Complete!</h2>
            <div class="final-score">
                <div class="score-value" id="finalScore">0%</div>
                <div class="score-text" id="scoreMessage">Great job!</div>
            </div>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                <button id="quizContinueBtn">Continue Chat</button>
                <button id="quizRestartBtn">Start Over</button>
            </div>
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
        let quizQuestions = [];
        let currentQuestionIndex = 0;
        let quizScore = 0;
        let quizAnswers = [];

        // Quiz DOM elements
        const quizContainer = document.getElementById('quizContainer');
        const quizQuestion = document.getElementById('quizQuestion');
        const quizOptions = document.getElementById('quizOptions');
        const quizCounter = document.getElementById('quizCounter');
        const quizScore_ = document.getElementById('quizScore');
        const quizTotal = document.getElementById('quizTotal');
        const progressFill = document.getElementById('progressFill');
        const quizPrevBtn = document.getElementById('quizPrevBtn');
        const quizNextBtn = document.getElementById('quizNextBtn');
        const quizComplete = document.getElementById('quizComplete');
        const finalScore = document.getElementById('finalScore');
        const scoreMessage = document.getElementById('scoreMessage');
        const quizRestartBtn = document.getElementById('quizRestartBtn');
        const quizContinueBtn = document.getElementById('quizContinueBtn');
        
        // Flashcard action buttons
        const flashcardContinueBtn = document.getElementById('flashcardContinueBtn');
        const flashcardRetryBtn = document.getElementById('flashcardRetryBtn');

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
            quizContainer.classList.remove('active');
        }

        function showChatMode() {
            chat.classList.remove('hidden');
            flashcardContainer.classList.remove('active');
            quizContainer.classList.remove('active');
        }

        function showQuizMode() {
            chat.classList.add('hidden');
            flashcardContainer.classList.remove('active');
            quizContainer.classList.add('active');
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
            } else if (currentMode === 'quiz') {
                showChatMode();
                appendMessage('bot', 'Please enter the topic you want to be quizzed on.');
            } else {
                showChatMode();
            }
        });

        function displayQuizQuestion() {
            if (quizQuestions.length === 0) return;
            const q = quizQuestions[currentQuestionIndex];
            quizQuestion.textContent = q.question;
            quizOptions.innerHTML = '';
            
            q.options.forEach((option, index) => {
                const btn = document.createElement('div');
                btn.className = 'quiz-option';
                btn.textContent = option;
                btn.dataset.index = index;
                
                if (quizAnswers[currentQuestionIndex] !== undefined) {
                    btn.classList.add('disabled');
                    if (quizAnswers[currentQuestionIndex] === index) {
                        if (index === q.correctAnswer) {
                            btn.classList.add('correct');
                        } else {
                            btn.classList.add('incorrect');
                        }
                    }
                    if (index === q.correctAnswer) {
                        btn.classList.add('correct');
                    }
                } else {
                    btn.addEventListener('click', () => selectAnswer(index));
                }
                
                quizOptions.appendChild(btn);
            });
            
            quizCounter.textContent = `Question ${currentQuestionIndex + 1}/${quizQuestions.length}`;
            quizScore_.textContent = quizScore;
            quizTotal.textContent = quizQuestions.length;
            const progress = ((currentQuestionIndex + 1) / quizQuestions.length) * 100;
            progressFill.style.width = progress + '%';
            
            quizPrevBtn.disabled = currentQuestionIndex === 0;
            quizNextBtn.disabled = quizAnswers[currentQuestionIndex] === undefined;
        }

        function selectAnswer(index) {
            if (quizAnswers[currentQuestionIndex] !== undefined) return;
            
            quizAnswers[currentQuestionIndex] = index;
            if (index === quizQuestions[currentQuestionIndex].correctAnswer) {
                quizScore++;
            }
            
            displayQuizQuestion();
        }

        function showQuizResults() {
            quizComplete.classList.add('active');
            quizOptions.innerHTML = '';
            const percentage = Math.round((quizScore / quizQuestions.length) * 100);
            finalScore.textContent = percentage + '%';
            
            if (percentage >= 80) {
                scoreMessage.textContent = 'Excellent work!';
            } else if (percentage >= 60) {
                scoreMessage.textContent = 'Good job!';
            } else {
                scoreMessage.textContent = 'Keep practicing!';
            }
        }

        quizPrevBtn.addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                displayQuizQuestion();
            }
        });

        quizNextBtn.addEventListener('click', () => {
            if (currentQuestionIndex < quizQuestions.length - 1) {
                currentQuestionIndex++;
                displayQuizQuestion();
            } else {
                showQuizResults();
            }
        });

        quizRestartBtn.addEventListener('click', () => {
            quizComplete.classList.remove('active');
            currentQuestionIndex = 0;
            quizScore = 0;
            quizAnswers = [];
            displayQuizQuestion();
        });

        quizContinueBtn.addEventListener('click', () => {
            quizComplete.classList.remove('active');
            modeSelect.value = 'general';
            currentMode = 'general';
            showChatMode();
            appendMessage('bot', 'Welcome back! What else can I help you with?');
        });

        flashcardContinueBtn.addEventListener('click', () => {
            modeSelect.value = 'general';
            currentMode = 'general';
            showChatMode();
            appendMessage('bot', 'Welcome back! What else can I help you with?');
        });

        flashcardRetryBtn.addEventListener('click', () => {
            currentCardIndex = 0;
            flashcard.classList.remove('flipped');
            updateFlashcard();
        });

        async function parseQuizResponse(content) {
            try {
                let jsonMatch = content.match(/\[[\s\S]*\]/);
                if (jsonMatch) {
                    quizQuestions = JSON.parse(jsonMatch[0]);
                } else {
                    quizQuestions = [];
                }
                
                if (quizQuestions.length === 0) {
                    appendMessage('bot', 'Could not generate quiz. Please try a different topic.');
                    showChatMode();
                    return false;
                }
                
                currentQuestionIndex = 0;
                quizScore = 0;
                quizAnswers = [];
                displayQuizQuestion();
                return true;
            } catch (e) {
                appendMessage('bot', 'Error parsing quiz questions. Please try again.');
                showChatMode();
                return false;
            }
        }

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
                } else if (mode === 'quiz') {
                    appendMessage('bot', `Creating quiz for: ${message}`);
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ message: `Create a 5 question quiz about ${message}. Return as JSON array with fields: question, options (array of 4), correctAnswer (index)`, mode: 'quiz' })
                    });
                    
                    const data = await response.json();
                    if (data.choices && data.choices[0].message) {
                        const success = await parseQuizResponse(data.choices[0].message.content);
                        if (success) showQuizMode();
                    } else {
                        appendMessage('bot', 'Error: ' + (data.error || 'Unknown error'));
                    }
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