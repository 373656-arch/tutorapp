<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'SystemPrompt.php';

header('Content-Type: application/json');

$apiKey = $_ENV['GROQLLM_API_KEY'];
if (!$apiKey) {
    echo json_encode(['error' => 'API Key missing in Replit Secrets']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$mode = $input['mode'] ?? 'general';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Message is empty']);
    exit;
}

$systemContent = SystemPrompt::getPrompt($mode);

$url = "https://api.groq.com/openai/v1/chat/completions";
$data = [
    "model" => "llama-3.1-8b-instant",
    "messages" => [
        ["role" => "system", "content" => $systemContent],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => 0.7
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
    echo $response;
} else {
    echo json_encode(['error' => "API Error ($httpCode)", 'raw' => $response]);
}
?>
