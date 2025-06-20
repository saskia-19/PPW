<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

$card_id = $_GET['card_id'] ?? 0;

// Ambil komentar untuk card ini
$stmt = $pdo->prepare("SELECT cc.*, u.username 
                      FROM card_comments cc
                      JOIN users u ON cc.user_id = u.user_id
                      WHERE cc.card_id = ?
                      ORDER BY cc.created_at DESC");
$stmt->execute([$card_id]);
$comments = $stmt->fetchAll();
?>

<div data-layer="Comments" class="Comments" style="left: 985px; top: 238px; position: absolute; color: white; font-size: 40px; font-family: Mitr; font-weight: 400; word-wrap: break-word">Comments</div>

<?php foreach ($comments as $comment): ?>
    <div style="left: 769px; top: <?= 346 + ($loop->index * 150) ?>px; position: absolute; color: white; font-size: 32px; font-family: Rubik One; font-weight: 400; word-wrap: break-word">
        <?= htmlspecialchars($comment['username']) ?>
    </div>
    <div style="left: 795px; top: <?= 396 + ($loop->index * 150) ?>px; position: absolute; color: white; font-size: 32px; font-family: Mitr; font-weight: 400; word-wrap: break-word">
        <?= htmlspecialchars($comment['comment']) ?>
    </div>
<?php endforeach; ?>

<!-- Form untuk menambahkan komentar baru -->
<form method="POST" action="add_comment.php" style="position: absolute; left: 738px; top: <?= 346 + (count($comments) * 150) ?>px;">
    <input type="hidden" name="card_id" value="<?= $card_id ?>">
    <div style="width: 678px; height: 80px; left: 0px; top: 0px; position: absolute; background: #E5EFFF; border-radius: 30px"></div>
    <input type="text" name="comment" style="width: 668px; height: 70px; left: 5px; top: 5px; position: absolute; background: transparent; border: none; font-size: 32px; padding-left: 10px;" placeholder="Tulis komentar...">
    <button type="submit" style="position: absolute; left: 1331px; top: 7px; background: none; border: none; cursor: pointer;">
        <img src="/images/paper_plane_icon.png" style="width: 70px; height: 70px;">
    </button>
</form>