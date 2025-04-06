<?php
/**
 * API endpoint to get user drawings
 * 
 * GET parameters:
 * - id: (optional) Get a specific drawing by ID
 * - limit: (optional) Limit number of drawings returned
 * - offset: (optional) Offset for pagination
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

// Check if requesting a specific drawing by ID
if (isset($_GET['id'])) {
    $drawing_id = (int)$_GET['id'];
    $drawing = get_drawing($drawing_id, $user_id);
    
    if (!$drawing) {
        echo json_encode([
            'success' => false,
            'message' => 'Drawing not found or access denied'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'drawings' => [$drawing]
    ]);
    exit;
}

// Get pagination parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Get drawings for the user
$drawings = get_user_drawings($user_id, $limit, $offset);

// Get total count for pagination
$total_count = db_count('drawings', 'user_id = ?', [$user_id]);

echo json_encode([
    'success' => true,
    'drawings' => $drawings,
    'total' => $total_count
]);
