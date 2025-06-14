// --- STEP 3: Generate Video Avatar via D-ID ---
// D-ID commonly uses X-API-KEY header for authentication.
// Ensure D_ID_API_KEY in config.php holds your actual API key string.
$didPayload = [
    'script' => [
        'type' => 'audio', // We are providing audio generated by ElevenLabs
        'audio_url' => $audioUrl
    ],
    'presenter_id' => 'default', // Using a default ID. Alternatively, use 'url' and D_ID_DEFAULT_AVATAR_URL.
    'source_url' => D_ID_DEFAULT_AVATAR_URL // Use the provided default avatar URL
];

$didResponse = $client->post(D_ID_TALKING_AVATAR_ENDPOINT, [
    'headers' => [
        'accept' => 'application/json',
        'x-api-key' => D_ID_API_KEY, // Use X-API-KEY header with the raw API key
        'Content-Type' => 'application/json',
    ],
    'json' => $didPayload
]);

$didData = json_decode($didResponse->getBody()->__toString(), true);

// ... (rest of your animation_api.php code for polling, etc.)