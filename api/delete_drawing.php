<?php
/**
 * API endpoint to delete a drawing
 * 
 * Expected POST parameters:
 * - drawing_id: ID of the drawing to delete
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

// Validate drawing ID
if (!isset($_POST['drawing_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing drawing ID'
    ]);
    exit;
}

$drawing_id = (int)$_POST['drawing_id'];

// Check if drawing exists and belongs to user
$sql = "SELECT title FROM drawings WHERE id = ? AND user_id = ?";
$drawing = db_query($sql, [$drawing_id, $user_id], false);

if (!$drawing) {
    echo json_encode([
        'success' => false,
        'message' => 'Drawing not found or access denied'
    ]);
    exit;
}

// Delete the drawing
$deleted = db_delete('drawings', 'id = ? AND user_id = ?', [$drawing_id, $user_id]);

if (!$deleted) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete drawing'
    ]);
    exit;
}

// Record the activity
record_activity($user_id, 'delete', 'Drawing Deleted', "Deleted drawing: {$drawing['title']}");

echo json_encode([
    'success' => true,
    'message' => 'Drawing deleted successfully'
]);
