<?php
/**
 * API endpoint to recognize drawing using Gemini API
 * 
 * Expected POST parameters:
 * - image_data: Base64-encoded image data
 */

session_start();
header('Content-Type: application/json');

include '../includes/config.php';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate and get image data
if (!isset($_POST['image_data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing image data'
    ]);
    exit;
}

$image_data = $_POST['image_data'];

// Validate image data format
if (empty($image_data) || strpos($image_data, 'data:image') !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid image data format'
    ]);
    exit;
}

// Extract the base64 part from the data URL
$parts = explode(',', $image_data);
if (count($parts) !== 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid image data'
    ]);
    exit;
}

$base64_image = $parts[1];

// Check if Gemini API key is available
// if (empty(GEMINI_API_KEY)) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'API key not configured'
//     ]);
//     exit;
// }

// Prepare the API request
$apiKey = "AIzaSyA8o7cHAtM8CMG-ilry804CFWM8Iy9Mb2U";
$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// Prepare the request data
$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Describe what is drawn in this image in detail. Please identify what the drawing appears to represent. Also, provide a confidence score (from 0-100%) indicating how certain you are of your identification."
                ],
                [
                    "inline_data" => [
                        "mime_type" => "image/png",
                        "data" => $base64_image
                    ]
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "topK" => 32,
        "topP" => 1,
        "maxOutputTokens" => 4096
    ]
];

// Send request to Gemini API
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Process API response
if ($status != 200) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to connect to recognition service',
        'status' => $status
    ]);
    exit;
}

$result = json_decode($response, true);

// Check if response is valid
if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid response from recognition service'
    ]);
    exit;
}

$recognition_text = $result['candidates'][0]['content']['parts'][0]['text'];

// Extract confidence score from the response, default to 70% if not found
$confidence = 70;
if (preg_match('/confidence score:?\s*(\d+)/i', $recognition_text, $matches) || 
    preg_match('/(\d+)%\s*confidence/i', $recognition_text, $matches)) {
    $confidence = (int) $matches[1];
    // Ensure confidence is within range
    $confidence = max(0, min(100, $confidence));
}

// Return the recognition results
echo json_encode([
    'success' => true,
    'recognition_text' => $recognition_text,
    'confidence' => $confidence
]);

// Record activity for AI recognition (optional)
record_activity($user_id, 'edit', 'AI Recognition', "Used AI to recognize drawing");
