<?php
session_start();
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login.']);
    exit;
}

// Ambil data dari form
$comment = $_POST['comment_text'] ?? '';
$deck_id = $_POST['deck_id'] ?? '';

if (empty($comment) || empty($deck_id)) {
    echo json_encode(['success' => false, 'message' => 'Komentar atau ID deck kosong.']);
    exit;
}

try {
    // Koneksi database
    $pdo = new PDO('mysql:host=localhost;dbname=u985354573_fp_flashcards;charset=utf8', 'u985354573_flashcards', 'LookBack@2005');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil satu kartu dari deck (asumsi semua komentar ditujukan ke salah satu kartu)
    $stmt = $pdo->prepare("SELECT card_id FROM cards WHERE deck_id = :deck_id LIMIT 1");
    $stmt->execute(['deck_id' => $deck_id]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$card) {
        echo json_encode(['success' => false, 'message' => 'Deck tidak ditemukan.']);
        exit;
    }

    // Simpan komentar
    $stmt = $pdo->prepare("
        INSERT INTO card_comments (user_id, card_id, comment, created_at)
        VALUES (:user_id, :card_id, :comment, NOW())
    ");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'card_id' => $card['card_id'],
        'comment' => $comment
    ]);

    // Ambil username pengguna
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'username' => htmlspecialchars($user['username']),
        'comment_html' => '<p>' . nl2br(htmlspecialchars($comment)) . '</p>'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan database.']);
    exit;
}
