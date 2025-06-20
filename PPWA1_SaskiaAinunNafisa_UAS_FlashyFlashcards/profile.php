<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

if ($_POST && isset($_FILES['profile_picture'])) {
    $upload_dir = 'images/profiles/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $new_filename = $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$upload_path, $_SESSION['user_id']]);
        header("Location: profile.php");
        exit;
    }
}

if ($_POST && isset($_POST['update_review_date'])) {
    $stmt = $pdo->prepare("UPDATE user_progress SET next_review_date = ? WHERE progress_id = ? AND user_id = ?");
    $stmt->execute([$_POST['next_review_date'], $_POST['progress_id'], $_SESSION['user_id']]);
    header("Location: profile.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
    up.*, 
    d.title AS deck_title, 
    c.deck_id,
    c.front_text,
    c.back_text,
    (
        SELECT COUNT(*) 
        FROM cards 
        WHERE deck_id = c.deck_id AND is_flagged = 1
    ) AS flagged_count
    FROM user_progress up
    JOIN cards c ON up.card_id = c.card_id
    JOIN decks d ON c.deck_id = d.deck_id
    WHERE up.user_id = ?
    ORDER BY up.last_reviewed DESC

");

$stmt->execute([$_SESSION['user_id']]);
$progress_data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="styles.css"> <!-- optional -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            text-align: center;
        }

        .profile-container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 2em;
            border-radius: 1em;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1em;
        }

        .upload-form {
            margin-bottom: 1em;
        }

        h1 {
            margin-bottom: 0.3em;
        }

        .deck-progress {
            margin-top: 2em;
            text-align: left;
        }

        .deck-card {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 0.5em;
            padding: 1em;
            margin-bottom: 1em;
        }

        .deck-card h3 {
            margin: 0 0 0.5em;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <?php if ($user['profile_picture']): ?>
            <img src="uploads/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-picture">
        <?php else: ?>
            <img src="images/User.png" alt="Default Profile" class="profile-picture">
        <?php endif; ?>

        <form class="upload-form" action="upload_profile_picture.php" method="post" enctype="multipart/form-data">
            <input type="file" name="profile_picture" required>
            <button type="submit">Upload Foto Profil Baru</button>
        </form>

        <h1><?= htmlspecialchars($user['username']) ?></h1>

        <div class="deck-progress">
            <h2>Progress Belajar</h2>

            <?php if (count($progress_data) === 0): ?>
                <p>Kamu belum memulai belajar apa pun.</p>
            <?php else: ?>
                <?php foreach ($progress_data as $progress): ?>
                    <div class="deck-card">
                        <h3><?= htmlspecialchars($progress['deck_title']) ?></h3>
                        <p><strong>Kartu:</strong> <?= htmlspecialchars($progress['front_text']) ?> → <?= htmlspecialchars($progress['back_text']) ?></p>
                        <p><strong>Terakhir Review:</strong> <?= htmlspecialchars($progress['last_reviewed']) ?: 'Belum pernah' ?></p>
                        <p><strong>Review Count:</strong> <?= $progress['review_count'] ?></p>
                        <p><strong>Flagged di deck ini:</strong> <?= $progress['flagged_count'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>