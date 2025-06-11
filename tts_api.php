<?php
// tts_api.php
header('Content-Type: application/json');

// !!! IMPORTANT: This file relies on Composer's autoloader !!!
// This file MUST be present and correctly populated by Composer.
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$input = json_decode(file_get_contents('php://input'), true);
$text = $input['text'] ?? '';

if (empty($text)) {
    echo json_encode(['error' => 'No text provided for TTS.']);
    exit;
}

$client = new Client();
$ttsUrl = ELEVENLABS_TTS_ENDPOINT . ELEVENLABS_VOICE_ID;

try {
    $response = $client->post($ttsUrl, [
        'headers' => [
            'Accept' => 'audio/mpeg',
            'xi-api-key' => ELEVENLABS_API_KEY,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'text' => $text,
            'model_id' => 'eleven_monolingual_v1', // Or other appropriate model like 'eleven_multilingual_v2'
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ],
        'timeout' => 30 // Timeout in seconds
    ]);

    if ($response->getStatusCode() === 200) {
        $audioContent = $response->getBody()->getContents();
        $filename = uniqid('tts_') . '.mp3';
        $filepath = AUDIO_DIR . $filename;

        // Ensure audio directory exists and is writable
        if (!is_dir(AUDIO_DIR)) {
            mkdir(AUDIO_DIR, 0775, true);
        }
        if (!is_writable(AUDIO_DIR)) {
            error_log('TTS Error: Audio directory not writable: ' . AUDIO_DIR);
            echo json_encode(['error' => 'Server error: Audio directory not writable.']);
            exit;
        }

        file_put_contents($filepath, $audioContent);

        // Return the URL for the audio file
        $audioUrl = 'audio/' . $filename; // Relative path from index.html
        header('Content-Type: application/json');
        echo json_encode(['audio_url' => $audioUrl]);
    } else {
        $errorBody = $response->getBody()->getContents();
        error_log("ElevenLabs API TTS failed with status " . $response->getStatusCode() . ": " . $errorBody);
        echo json_encode(['error' => 'ElevenLabs TTS API error: ' . $errorBody]);
    }

} catch (RequestException $e) {
    $errorMessage = $e->getMessage();
    if ($e->hasResponse()) {
        $errorMessage .= " Response: " . $e->getResponse()->getBody()->getContents();
    }
    error_log("ElevenLabs TTS Request failed: " . $errorMessage);
    echo json_encode(['error' => 'Failed to connect to ElevenLabs TTS service. ' . $errorMessage]);
} catch (Exception $e) {
    error_log("General error in tts_api.php: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred for TTS.']);
}
?>