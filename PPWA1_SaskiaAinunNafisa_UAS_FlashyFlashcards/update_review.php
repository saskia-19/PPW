<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_id = $_POST['card_id'];
    $review_date = $_POST['review_date'];
    
    $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, card_id, next_review_date, last_reviewed, review_count) 
                          VALUES (?, ?, ?, NOW(), 1) 
                          ON DUPLICATE KEY UPDATE 
                          next_review_date = VALUES(next_review_date), 
                          last_reviewed = VALUES(last_reviewed), 
                          review_count = review_count + 1");
    $stmt->execute([$_SESSION['user_id'], $card_id, $review_date]);
}

header("Location: profile.php");
exit();
?>