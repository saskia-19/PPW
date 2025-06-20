<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['card_id']) || !isset($input['is_flagged'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$card_id = (int)$input['card_id'];
$is_flagged = (int)$input['is_flagged'];
$user_id = $_SESSION['user_id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=u985354573_fp_flashcards;charset=utf8", "u985354573_flashcards", "LookBack@2005");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user_progress record exists
    $checkStmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = :user_id AND card_id = :card_id");
    $checkStmt->execute(['user_id' => $user_id, 'card_id' => $card_id]);
    $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingRecord) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE user_progress 
            SET is_flagged = :is_flagged, 
                last_reviewed = NOW(),
                review_count = review_count + 1
            WHERE user_id = :user_id AND card_id = :card_id
        ");
        $result = $stmt->execute([
            'is_flagged' => $is_flagged,
            'user_id' => $user_id,
            'card_id' => $card_id
        ]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO user_progress (user_id, card_id, is_flagged, last_reviewed, review_count) 
            VALUES (:user_id, :card_id, :is_flagged, NOW(), 1)
        ");
        $result = $stmt->execute([
            'user_id' => $user_id,
            'card_id' => $card_id,
            'is_flagged' => $is_flagged
        ]);
    }
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Card flagged successfully',
            'card_id' => $card_id,
            'is_flagged' => $is_flagged,
            'user_id' => $user_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update card progress']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>