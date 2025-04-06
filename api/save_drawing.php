<?php
/**
 * API endpoint to save a drawing
 * Handles both new drawings and updates to existing drawings
 * 
 * Expected POST parameters:
 * - title: Drawing title
 * - description: Drawing description (optional)
 * - drawing_data: Base64-encoded image data
 * - drawing_id: ID of the drawing to update (optional, only for updates)
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

// Validate and sanitize inputs
if (!isset($_POST['title']) || !isset($_POST['drawing_data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$title = sanitize_input($_POST['title']);
$description = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
$drawing_data = $_POST['drawing_data'];

// Validate title
if (empty($title)) {
    echo json_encode([
        'success' => false,
        'message' => 'Title cannot be empty'
    ]);
    exit;
}

// Validate drawing data
if (empty($drawing_data) || strpos($drawing_data, 'data:image') !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid drawing data'
    ]);
    exit;
}

// Optimize the image data to reduce size
$optimized_drawing_data = optimize_base64_image($drawing_data);

// Check if this is an update or a new drawing
$drawing_id = isset($_POST['drawing_id']) ? (int)$_POST['drawing_id'] : 0;

if ($drawing_id > 0) {
    // Update existing drawing - first check if it belongs to the user
    $sql = "SELECT id FROM drawings WHERE id = ? AND user_id = ?";
    $drawing = db_query($sql, [$drawing_id, $user_id], false);
    
    if (!$drawing) {
        echo json_encode([
            'success' => false,
            'message' => 'Drawing not found or access denied'
        ]);
        exit;
    }
    
    // Update the drawing
    $updated = db_update('drawings', [
        'title' => $title,
        'description' => $description,
        'drawing_data' => $optimized_drawing_data
    ], 'id = ?', [$drawing_id]);
    
    if (!$updated) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update drawing'
        ]);
        exit;
    }
    
    // Record activity
    record_activity($user_id, 'edit', 'Drawing Updated', "Updated drawing: $title", $drawing_id);
    
} else {
    // Insert new drawing
    $data = [
        'user_id' => $user_id,
        'title' => $title,
        'description' => $description,
        'drawing_data' => $optimized_drawing_data
    ];
    
    $drawing_id = db_insert('drawings', $data);
    
    if (!$drawing_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save drawing'
        ]);
        exit;
    }
    
    // Record activity
    record_activity($user_id, 'create', 'New Drawing', "Created new drawing: $title", $drawing_id);
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Drawing saved successfully',
    'drawing_id' => $drawing_id
]);
