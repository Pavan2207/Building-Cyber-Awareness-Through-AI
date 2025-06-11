<?php
// backend/config.php - KEEP THIS FILE SECURE!

// --- OpenRouter API Configuration ---
// GET YOUR KEY from https://openrouter.ai/
// REMINDER: YOU ARE PUBLICLY SHARING YOUR KEY! REVOKE IMMEDIATELY IF REAL!
define('OPENROUTER_API_KEY', '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'); // <<< REPLACE with YOUR ACTUAL OpenRouter Key
define('OPENROUTER_CHAT_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions');
define('OPENROUTER_CHAT_MODEL', 'mistralai/mistral-nemo'); // Or 'google/gemini-pro' or other models available on OpenRouter

// --- ElevenLabs API Configuration for Text-to-Speech ---
// GET YOUR KEY from https://elevenlabs.io/
// REMINDER: YOU ARE PUBLICLY SHARING YOUR KEY! REVOKE IMMEDIATELY IF REAL!
define('ELEVENLABS_API_KEY', 'sk_dfc87cdb6dfa9d37266f35ed8fabd1504b1c3078bf35225e'); // <<< REPLACE with YOUR ACTUAL ElevenLabs Key (starts with 'sk_')
define('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'); // Default: Adam. You can find other IDs on ElevenLabs website
define('ELEVENLABS_TTS_ENDPOINT', 'https://api.elevenlabs.io/v1/text-to-speech/');

// --- D-ID API Configuration (for Animated Scenarios) ---
// GET YOUR KEY from https://studio.d-id.com/api-key
// REMINDER: YOU ARE PUBLICLY SHARING YOUR KEY! REVOKE IMMEDIATELY IF REAL!
// Ensure this is your actual D-ID API Key string from their dashboard, not an encoded email or password.
define('D_ID_API_KEY', 'cGF2YW5rdW1hcmtvdG5pMkBnbWFpbC5jb20:12OSJwUIzv176wULIrPID'); // <<< REPLACE with YOUR ACTUAL D-ID API Key
define('D_ID_TALKING_AVATAR_ENDPOINT', 'https://api.d-id.com/talks');
// This is a publicly accessible example avatar. For your own, upload it and use its URL.
define('D_ID_DEFAULT_AVATAR_URL', 'https://d-id-public-images.s3.amazonaws.com/deid/face-template.jpg');
// You can use the same ElevenLabs voice for D-ID's text-to-speech, or choose a different one
define('D_ID_VOICE_ID', ELEVENLABS_VOICE_ID);
// If you want D-ID to generate its own voice, remove D_ID_VOICE_ID and provide its 'script' type 'text' only.

// --- Paths for generated audio/video ---
define('AUDIO_DIR', __DIR__ . '/../audio/'); // Directory to save generated audio files
?>