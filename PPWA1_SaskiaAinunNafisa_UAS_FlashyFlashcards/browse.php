<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

$search = $_GET['search'] ?? '';

// Query pencarian deck
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT d.* FROM decks d 
        LEFT JOIN deck_tags dt ON d.deck_id = dt.deck_id
        LEFT JOIN tags t ON dt.tag_id = t.tag_id
        WHERE (d.title LIKE ? OR t.name LIKE ?) AND (d.is_public = 1 OR d.user_id = ?)
        GROUP BY d.deck_id");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM decks WHERE is_public = 1 OR user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}

$decks = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<style>
  .browse-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 20px;
    font-family: 'Mitr', sans-serif;
  }

  .browse-title {
    text-align: center;
    font-size: 64px;
    margin-bottom: 20px;
  }

  .search-form {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-bottom: 40px;
  }

  .search-form input[type="text"] {
    font-size: 24px;
    padding: 16px 24px;
    width: 400px;
    border-radius: 30px;
    border: 2px solid #ccc;
  }

  .search-form button {
    font-size: 24px;
    padding: 16px 32px;
    background-color: #3787FF;
    color: white;
    border: none;
    border-radius: 30px;
    cursor: pointer;
  }

  .deck-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 32px;
    justify-items: center;
  }

  .deck-card {
    width: 100%;
    max-width: 291px;
    background: #f5f5f5;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .deck-banner {
    height: 354px;
    background: linear-gradient(115deg, #37B2FF 0%, #00378A 100%);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
    border-radius: 20px 20px 0 0;
  }

  .deck-title {
    font-size: 24px;
    text-align: center;
    word-break: break-word;
  }

  .deck-status {
    font-size: 16px;
    text-align: center;
    opacity: 0.9;
  }

  .deck-play {
    height: 80px;
    background-color: #3787FF;
    color: white;
    font-size: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 0 0 20px 20px;
    text-decoration: none;
  }

  .no-results {
    text-align: center;
    font-size: 24px;
    margin-top: 60px;
  }
</style>

<div class="browse-container">
  <div class="browse-title">Browse</div>

  <form method="GET" action="browse.php" class="search-form">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari deck...">
    <button type="submit">Search</button>
  </form>

  <?php if (empty($decks)): ?>
    <div class="no-results">Tidak ada deck yang ditemukan</div>
  <?php else: ?>
    <div class="deck-grid">
      <?php foreach ($decks as $deck): ?>
        <div class="deck-card">
          <div class="deck-banner">
            <div class="deck-title"><?= htmlspecialchars($deck['title']) ?></div>
            <div class="deck-status"><?= $deck['is_public'] ? 'Public' : 'Private' ?></div>
          </div>
          <a href="gameplay.php?deck_id=<?= $deck['deck_id'] ?>" class="deck-play">Play</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
