<?php

$apiKey = getenv('GROQLLM_API_KEY');

if (!$apiKey) {
    die("GROQLLM_API_KEY not found in Replit Secrets.");
}

$responseText = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["message"])) {

    $userMessage = trim($_POST["message"]);

    $url = "https://api.groq.com/openai/v1/chat/completions";

    $data = [
        "model" => "llama-3.1-8b-instant",
        "messages" => [
            [
                "role" => "system",
                "content" => "Respond in exactly one clear sentence only."
            ],
            [
                "role" => "user",
                "content" => $userMessage
            ]
        ],
        "temperature" => 0.7,
        "max_tokens" => 60
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey"
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        $responseText = $decoded["choices"][0]["message"]["content"] ?? "No response.";
    } else {
        $responseText = "API Error (HTTP $httpCode)";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quick Chatbot</title>
    <style>
        body { font-family: Arial; background: #111; color: #fff; text-align: center; padding-top: 50px; }
        input { width: 300px; padding: 10px; }
        button { padding: 10px 15px; }
        .response { margin-top: 20px; font-size: 18px; }
    </style>
</head>
<body>

<h1>CHATBOT - MICHAELBRANCH</h1>

<form method="POST">
    <input type="text" name="message" placeholder="Ask something..." required>
    <button type="submit">Send</button>
</form>

<?php if (!empty($responseText)): ?>
    <div class="response">
        <strong>Bot:</strong> <?= htmlspecialchars($responseText) ?>
    </div>
<?php endif; ?>

</body>
</html>