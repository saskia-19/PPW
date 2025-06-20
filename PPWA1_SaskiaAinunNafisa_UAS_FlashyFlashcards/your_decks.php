<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

// Ambil deck milik user
$stmt = $pdo->prepare("SELECT * FROM decks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$decks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Decks - Flashy Flashcards</title>
  <link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: 'Mitr', sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .title {
      font-size: 48px;
      text-align: center;
      margin-bottom: 30px;
    }

    .search-section {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-bottom: 40px;
    }

    #searchInput {
      font-size: 20px;
      padding: 10px 20px;
      border-radius: 20px;
      border: 2px solid #ccc;
      width: 400px;
      transition: border 0.2s;
    }

    #searchInput:focus {
      border-color: #3787FF;
      outline: none;
    }

    .btn-search {
      background-color: #3787FF;
      color: white;
      font-size: 20px;
      border: none;
      border-radius: 20px;
      padding: 10px 30px;
      cursor: pointer;
    }

    .decks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      justify-items: center;
    }

    .deck-card {
      width: 100%;
      max-width: 280px;
      height: 360px;
      background: linear-gradient(115deg, #FF1919 0%, #0325FF 100%);
      border-radius: 20px;
      padding: 20px;
      color: white;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.2s;
      cursor: pointer;
      text-decoration: none;
    }

    .deck-card:hover {
      transform: translateY(-5px);
    }

    .deck-image {
      width: 100px;
      height: 100px;
      margin: 0 auto;
    }

    .deck-title {
      font-size: 22px;
      font-weight: 500;
      margin-top: 10px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .deck-info {
      font-size: 16px;
      font-weight: 300;
      opacity: 0.9;
    }

    .deck-tags {
      font-size: 14px;
      opacity: 0.7;
    }

    .deck-card.create {
      background: linear-gradient(115deg, #37B2FF 0%, #00378A 100%);
    }

    .btn-create {
      margin-top: 40px;
      text-align: center;
    }

    .btn-create a {
      background-color: #3787FF;
      padding: 14px 32px;
      font-size: 24px;
      color: white;
      border-radius: 20px;
      text-decoration: none;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <div class="title">Your Decks</div>

  <div class="search-section">
    <input type="text" id="searchInput" placeholder="Your deck name">
    <button class="btn-search" onclick="searchDecks()">Search</button>
  </div>

  <div class="decks-grid" id="decksContainer">
    <?php if (empty($decks)): ?>
      <!-- Empty State -->
      <a href="create_deck.php" class="deck-card create">
        <img src="images/Add.png" class="deck-image" alt="Add Deck">
        <div class="deck-title">Buat Deck Pertama</div>
      </a>
    <?php else: ?>
      <?php foreach ($decks as $deck): ?>
        <?php
        $cardStmt = $pdo->prepare("SELECT COUNT(*) as card_count FROM cards WHERE deck_id = ?");
        $cardStmt->execute([$deck['deck_id']]);
        $cardCount = $cardStmt->fetch()['card_count'];
        ?>
        <a href="gameplay.php?deck_id=<?= $deck['deck_id'] ?>" class="deck-card deck-item" data-title="<?= strtolower(htmlspecialchars($deck['title'])) ?>">
          <img src="images/RedCard.png" class="deck-image" alt="Deck Icon">
          <div class="deck-title"><?= htmlspecialchars($deck['title']) ?></div>
          <div class="deck-info"><?= $cardCount ?> kartu</div>
          <?php if (!empty($deck['tags'])): ?>
            <div class="deck-tags"><?= htmlspecialchars($deck['tags']) ?></div>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <?php if (count($decks) < 6): ?>
        <a href="create_deck.php" class="deck-card create">
          <img src="images/Add.png" class="deck-image" alt="Add Deck">
          <div class="deck-title">Buat Deck Baru</div>
        </a>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</div>

<?php include 'includes/footer.php'; ?>

<script>
function searchDecks() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const deckItems = document.querySelectorAll('.deck-item');

  deckItems.forEach(item => {
    const deckTitle = item.getAttribute('data-title');
    item.style.display = deckTitle.includes(searchTerm) ? 'flex' : 'none';
  });
}
</script>

</body>
</html>
