<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_id = $_POST['card_id'];
    $comment = $_POST['comment'];
    
    $stmt = $pdo->prepare("INSERT INTO card_comments (user_id, card_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $card_id, $comment]);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>