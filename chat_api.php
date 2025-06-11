<?php
// chat_api.php
header('Content-Type: application/json');

// !!! IMPORTANT: This file relies on Composer's autoloader !!!
// This file MUST be present and correctly populated by Composer.
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided.']);
    exit;
}

$client = new Client();

try {
    $response = $client->post(OPENROUTER_CHAT_ENDPOINT, [
        'headers' => [
            'Authorization' => 'Bearer ' . OPENROUTER_API_KEY,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'http://localhost/online_safety_platform/', // Replace with your actual domain
            'X-Title' => 'AI Online Safety Platform' // Replace with your app title
        ],
        'json' => [
            'model' => OPENROUTER_CHAT_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an AI assistant specializing in online safety. Provide clear, concise, and helpful advice on topics like phishing, cyberbullying, online privacy, scams, and safe internet practices for all ages. Always prioritize safety and offer actionable tips.'],
                ['role' => 'user', 'content' => $userMessage]
            ]
        ],
        'timeout' => 60 // Timeout in seconds
    ]);

    $data = json_decode($response->getBody()->getContents(), true);

    if (isset($data['choices'][0]['message']['content'])) {
        echo json_encode(['response' => $data['choices'][0]['message']['content']]);
    } else {
        error_log("OpenRouter API unexpected response: " . json_encode($data));
        echo json_encode(['error' => 'Unexpected API response from OpenRouter.']);
    }

} catch (RequestException $e) {
    $errorMessage = $e->getMessage();
    if ($e->hasResponse()) {
        $errorMessage .= " Response: " . $e->getResponse()->getBody()->getContents();
    }
    error_log("OpenRouter API Request failed: " . $errorMessage);
    echo json_encode(['error' => 'Failed to connect to AI service. ' . $errorMessage]);
} catch (Exception $e) {
    error_log("General error in chat_api.php: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}
?>