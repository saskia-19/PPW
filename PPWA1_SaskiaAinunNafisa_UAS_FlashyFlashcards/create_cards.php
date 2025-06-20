<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

// Cek apakah deck_id ada
if (!isset($_GET['deck_id'])) {
    header("Location: your_decks.php");
    exit;
}

$deck_id = $_GET['deck_id'];

// Verifikasi deck milik user
$stmt = $pdo->prepare("SELECT * FROM decks WHERE deck_id = ? AND user_id = ?");
$stmt->execute([$deck_id, $_SESSION['user_id']]);
$deck = $stmt->fetch();

if (!$deck) {
    header("Location: your_decks.php");
    exit;
}

// Handle pembuatan kartu tunggal
if ($_POST && isset($_POST['create_card'])) {
    $front_text = trim($_POST['front_text']);
    $back_text = trim($_POST['back_text']);
    $hint = trim($_POST['hint']);
    
    if (!empty($front_text) && !empty($back_text) && !empty($hint)) {
        $stmt = $pdo->prepare("INSERT INTO cards (deck_id, front_text, back_text, hint, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$deck_id, $front_text, $back_text, $hint]);
        
        header("Location: create_cards.php?deck_id=" . $deck_id . "&success=1");
        exit;
    }
}

// Handle pembuatan kartu banyak sekaligus
if ($_POST && isset($_POST['create_bulk'])) {
    $cards_data = $_POST['cards_data'];
    $lines = explode("\n", $cards_data);
    $success_count = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $parts = explode('|', $line);
        if (count($parts) >= 2) {
            $front_text = trim($parts[0]);
            $back_text = trim($parts[1]);
            $hint = trim($parts[2]);
            
            if (!empty($front_text) && !empty($back_text) && !empty($hint)) {
                $stmt = $pdo->prepare("INSERT INTO cards (deck_id, front_text, back_text, hint, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$deck_id, $front_text, $back_text, $hint]);
                $success_count++;
            }
        }
    }
    
    header("Location: create_cards.php?deck_id=" . $deck_id . "&bulk_success=" . $success_count);
    exit;
}

// Ambil kartu yang sudah ada
$stmt = $pdo->prepare("SELECT * FROM cards WHERE deck_id = ? ORDER BY created_at DESC");
$stmt->execute([$deck_id]);
$existing_cards = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Create Cards - <?= htmlspecialchars($deck['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Mitr&display=swap" rel="stylesheet" />
<style>
  body {
    font-family: 'Mitr', sans-serif;
    margin: 0; background: #fff; color: #000;
  }
  header {
    padding: 15px 30px;
    background: #3787FF;
    color: white;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
  }
  .container {
    max-width: 1200px;
    margin: 40px auto 80px;
    padding: 0 20px;
  }
  .success-msg {
    width: 400px;
    margin: 0 auto 30px;
    padding: 15px;
    background: #4CAF50;
    border-radius: 10px;
    color: white;
    font-size: 18px;
    text-align: center;
  }
  .title-main {
    font-size: 48px;
    font-weight: 400;
    margin-bottom: 40px;
    text-align: center;
  }
  .btn-back {
    display: inline-block;
    margin-bottom: 40px;
    background: #3787FF;
    color: white;
    padding: 12px 30px;
    border-radius: 20px;
    font-size: 20px;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .btn-back:hover {
    background: #2c65cc;
  }
  form {
    margin-bottom: 60px;
  }
  form textarea {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    border: 2px solid #E5EFFF;
    border-radius: 15px;
    resize: vertical;
    font-family: 'Mitr', sans-serif;
  }
  .form-group {
    margin-bottom: 20px;
  }
  .form-flex {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
  }
  .form-flex > div {
    flex: 1;
    min-width: 280px;
  }
  label {
    display: block;
    font-size: 24px;
    margin-bottom: 10px;
  }
  button {
    display: block;
    background: #3787FF;
    border: none;
    border-radius: 25px;
    color: white;
    font-size: 24px;
    padding: 15px 0;
    width: 200px;
    cursor: pointer;
    margin: 0 auto;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }
  button:hover {
    background: #2c65cc;
    transform: scale(1.05);
  }
  .btn-bulk {
    background: #FF6B35;
    width: 250px;
  }
  .btn-bulk:hover {
    background: #cc5630;
  }
  .bulk-instruction {
    font-size: 18px;
    color: #666;
    margin-bottom: 15px;
  }

  /* =======================
     Grid untuk kartu yang sudah dibuat
  ======================= */
  .cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
  }
  .card {
    background: linear-gradient(115deg, #37B2FF 0%, #00378A 100%);
    border-radius: 15px;
    padding: 20px;
    min-height: 220px;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
    cursor: default;
  }
  .card:hover {
    transform: translateY(-5px);
  }
  .card .title {
    font-weight: 600;
    font-size: 18px;
    margin-bottom: 10px;
  }
  .card .content {
    font-size: 16px;
    line-height: 1.4;
    flex-grow: 1;
    white-space: pre-wrap;
    overflow-wrap: break-word;
  }
  .card .date {
    font-size: 12px;
    opacity: 0.7;
    text-align: right;
    margin-top: 10px;
  }

  /* Start belajar button */
  .start-learn {
    text-align: center;
    margin: 60px 0;
  }
  .start-learn a {
    display: inline-block;
    background: #4CAF50;
    color: white;
    padding: 18px 50px;
    border-radius: 30px;
    font-size: 28px;
    text-decoration: none;
    font-weight: 400;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }
  .start-learn a:hover {
    background: #3b8d3b;
    transform: scale(1.05);
  }
</style>
</head>
<body>

<header>
    Buat Kartu untuk "<?= htmlspecialchars($deck['title']) ?>"
</header>

<div class="container">
    <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">Kartu berhasil dibuat!</div>
    <?php endif; ?>

    <?php if (isset($_GET['bulk_success'])): ?>
        <div class="success-msg"><?= (int)$_GET['bulk_success'] ?> kartu berhasil dibuat!</div>
    <?php endif; ?>

    <a href="your_decks.php" class="btn-back">Kembali</a>

    <!-- Form buat kartu tunggal -->
    <form method="POST">
        <div class="form-flex">
            <div>
                <label for="front_text">Depan Kartu:</label>
                <textarea name="front_text" id="front_text" required placeholder="Masukkan teks untuk bagian depan kartu..."></textarea>
            </div>
            <div>
                <label for="back_text">Belakang Kartu:</label>
                <textarea name="back_text" id="back_text" required placeholder="Masukkan teks untuk bagian belakang kartu..."></textarea>
            </div>
            <div>
                <label for="hint">Petunjuk:</label>
                <textarea name="hint" id="hint" required placeholder="Masukkan teks untuk petunjuk kartu..."></textarea>
            </div>
        </div>
        <button type="submit" name="create_card">Buat Kartu</button>
    </form>

    <!-- Form buat kartu bulk -->
    <div>
        <h2 style="font-weight: 400; font-size: 32px; margin-bottom: 10px;">Buat Banyak Kartu Sekaligus</h2>
        <div class="bulk-instruction">Format: Depan kartu | Belakang kartu | Hint (satu kartu per baris)</div>
        <form method="POST">
            <textarea name="cards_data" rows="8" placeholder="Contoh:
Apa ibu kota Indonesia? | Jakarta | Monas
Siapa presiden pertama Indonesia? | Soekarno | Bapak Proklamator Indonesia
" required></textarea>
            <button type="submit" name="create_bulk" class="btn-bulk">Buat Banyak Kartu</button>
        </form>
    </div>

    <!-- Tampilkan kartu yang sudah ada -->
    <?php if (!empty($existing_cards)): ?>
        <div class="cards-container">
            <?php foreach ($existing_cards as $card): ?>
                <div class="card">
                    <div class="title">Depan:</div>
                    <div class="content"><?= nl2br(htmlspecialchars($card['front_text'])) ?></div>

                    <div class="title" style="margin-top: 15px;">Belakang:</div>
                    <div class="content"><?= nl2br(htmlspecialchars($card['back_text'])) ?></div>

                    <div class="title" style="margin-top: 15px;">Hint:</div>
                    <div class="content"><?= nl2br(htmlspecialchars($card['hint'])) ?></div>

                    <div class="date"><?= date('d/m/Y', strtotime($card['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; margin-top: 40px; font-size: 18px; color: #666;">Belum ada kartu yang dibuat.</p>
    <?php endif; ?>
</div>

</body>
</html>
