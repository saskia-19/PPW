<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$deck_id = $_GET['deck_id'] ?? null;
if (!$deck_id) {
    echo "Deck tidak ditemukan.";
    exit;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=u985354573_fp_flashcards;charset=utf8", "u985354573_flashcards", "LookBack@2005");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil info deck
    $stmtDeck = $conn->prepare("SELECT title FROM decks WHERE deck_id = :deck_id");
    $stmtDeck->execute(['deck_id' => $deck_id]);
    $deck = $stmtDeck->fetch(PDO::FETCH_ASSOC);
    if (!$deck) {
        echo "Deck tidak ditemukan.";
        exit;
    }

    // Ambil cards dengan status flagged dari user_progress
    $stmt = $conn->prepare("
        SELECT c.*, 
               COALESCE(up.is_flagged, 0) as is_flagged 
        FROM cards c 
        LEFT JOIN user_progress up ON c.card_id = up.card_id AND up.user_id = :user_id
        WHERE c.deck_id = :deck_id 
        ORDER BY c.card_id ASC
    ");
    $stmt->execute(['deck_id' => $deck_id, 'user_id' => $_SESSION['user_id']]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil komentar untuk cards di deck ini
    $stmtComments = $conn->prepare("
        SELECT cc.comment, u.username, cc.created_at 
        FROM card_comments cc 
        JOIN users u ON cc.user_id = u.user_id 
        JOIN cards c ON cc.card_id = c.card_id 
        WHERE c.deck_id = :deck_id 
        ORDER BY cc.created_at DESC
    ");
    $stmtComments->execute(['deck_id' => $deck_id]);
    $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Flashy Flashcards - <?= htmlspecialchars($deck['title']) ?></title>
<style>
  /* Reset & basic */
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0; font-family: 'Mitr', sans-serif; background: #f0f0f0;
    display: flex; flex-direction: column; min-height: 100vh;
  }
  header {
    background: #3787FF;
    color: white;
    padding: 15px 30px;
    font-family: 'Rubik One', sans-serif;
    font-size: 28px;
    text-align: center;
  }
  main {
    flex: 1;
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
  }
  /* Card container */
  .card-container {
    perspective: 1000px;
    position: relative;
    overflow: hidden;
  }
  /* Flashcard */
  .flashcard {
    width: 100%;
    max-width: 400px;
    height: 250px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.2);
    cursor: pointer;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s, left 0.3s ease-out, opacity 0.3s ease-out;
    left: 0;
    user-select: none;
    touch-action: pan-y;
  }
  .flashcard.flipped {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, #85FF72 0%, #16A400 100%);
    color: white;
  }
  .flashcard.swiping-right {
    box-shadow: 0 0 20px rgba(55, 135, 255, 0.6);
  }
  .flashcard.swiping-left {
    box-shadow: 0 0 20px rgba(255, 149, 0, 0.6);
  }
  .flashcard.removing {
    opacity: 0;
    transform: translateX(100%) rotate(30deg);
  }
  /* Sisi depan dan belakang */
  .front, .back {
    position: absolute;
    width: 100%;
    height: 100%;
    padding: 30px;
    backface-visibility: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.5rem;
    text-align: center;
  }
  .front {
    background: white;
    color: black;
  }
  .back {
    transform: rotateY(180deg);
    color: white;
  }
  /* Swipe indicators */
  .swipe-indicator {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 10;
    padding: 10px 20px;
    border-radius: 10px;
    color: white;
  }
  .swipe-indicator.right {
    right: 20px;
    background: rgba(55, 135, 255, 0.9);
  }
  .swipe-indicator.left {
    left: 20px;
    background: rgba(255, 149, 0, 0.9);
  }
  .flashcard.swiping-right .swipe-indicator.right {
    opacity: 1;
  }
  .flashcard.swiping-left .swipe-indicator.left {
    opacity: 1;
  }
  /* Hint button */
  .hint-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255 255 255 / 0.8);
    border: none;
    padding: 8px 15px;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    display: none;
    color: #16A400;
    z-index: 15;
  }
  .flashcard.flipped .hint-btn {
    display: block;
  }
  /* Comments section */
  .comments-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 8px rgb(0 0 0 / 0.1);
    display: flex;
    flex-direction: column;
    height: 450px;
  }
  .comments-header {
    background: #3787FF;
    color: white;
    padding: 15px;
    font-size: 24px;
    font-family: 'Mitr', sans-serif;
    border-radius: 15px 15px 0 0;
  }
  .comments-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px 20px;
    font-family: 'Merriweather Sans', sans-serif;
  }
  .comment {
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
  }
  .comment strong {
    display: block;
    margin-bottom: 5px;
    color: #3787FF;
  }
  .comment-input {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    border-radius: 0 0 15px 15px;
  }
  .comment-input textarea {
    flex: 1;
    resize: none;
    border-radius: 10px;
    border: 1px solid #ccc;
    padding: 8px;
    font-family: 'Merriweather Sans', sans-serif;
  }
  .comment-input button {
    background: #3787FF;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
  }
  .comment-input button:hover {
    background: #255ec1;
  }
</style>
</head>
<body>
<header>Flashy Flashcards - <?= htmlspecialchars($deck['title']) ?></header>
<main>
  <section class="card-container">
    <?php if (!$cards): ?>
      <p>Tidak ada cards dalam deck ini.</p>
    <?php else: ?>
      <?php foreach ($cards as $index => $card): ?>
        <div class="flashcard" 
             tabindex="0" 
             data-index="<?= $index ?>" 
             data-card-id="<?= $card['card_id'] ?>"
             data-hint="<?= htmlspecialchars($card['hint'] ?? '') ?>">
          <div class="front"><?= htmlspecialchars($card['front_text']) ?></div>
          <div class="back"><?= htmlspecialchars($card['back_text']) ?></div>
          <div class="swipe-indicator right">RIGHT</div>
          <div class="swipe-indicator left">FLAG</div>
          <button class="hint-btn" type="button">Hint</button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
  
  <aside class="comments-section">
    <div class="comments-header">Comments</div>
    <div class="comments-list" id="comments-list">
      <?php if (!$comments): ?>
        <p style="padding:10px; color:#888;">Belum ada komentar. Jadilah yang pertama!</p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment">
            <strong><?= htmlspecialchars($comment['username']) ?></strong>
            <?= nl2br(htmlspecialchars($comment['comment'])) ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <form id="comment-form" class="comment-input" action="submit_comment.php" method="POST">
      <input type="hidden" name="deck_id" value="<?= $deck_id ?>" />
      <textarea name="comment_text" rows="2" placeholder="Tulis komentar..." required></textarea>
      <button type="submit">Send</button>
    </form>
  </aside>
</main>

<script>
  const cards = document.querySelectorAll('.flashcard');
  let currentCardIndex = 0;

  // Touch/Mouse event handlers for swipe
  let startX = 0;
  let startY = 0;
  let currentX = 0;
  let currentY = 0;
  let isDragging = false;
  let isCardFlipped = false;

  cards.forEach((card, index) => {
    const hintBtn = card.querySelector('.hint-btn');
    const hintText = card.getAttribute('data-hint');

    // Show only current card
    if (index !== currentCardIndex) {
      card.style.display = 'none';
    }

    // Click to flip
    card.addEventListener('click', (e) => {
      if (!isDragging && e.target !== hintBtn) {
        card.classList.toggle('flipped');
        isCardFlipped = card.classList.contains('flipped');
        if (isCardFlipped && hintText) {
          setTimeout(() => alert('Hint: ' + hintText), 100);
        }
      }
    });

    // Hint button
    hintBtn.addEventListener('click', e => {
      e.stopPropagation();
      alert('Hint: ' + hintText);
    });

    // Touch events
    card.addEventListener('touchstart', handleStart, { passive: false });
    card.addEventListener('touchmove', handleMove, { passive: false });
    card.addEventListener('touchend', handleEnd, { passive: false });

    // Mouse events
    card.addEventListener('mousedown', handleStart);
    card.addEventListener('mousemove', handleMove);
    card.addEventListener('mouseup', handleEnd);
    card.addEventListener('mouseleave', handleEnd);
  });

  function handleStart(e) {
    const touch = e.touches ? e.touches[0] : e;
    startX = touch.clientX;
    startY = touch.clientY;
    isDragging = true;
    
    if (e.type === 'mousedown') {
      e.preventDefault();
    }
  }

  function handleMove(e) {
    if (!isDragging) return;
    
    e.preventDefault();
    const touch = e.touches ? e.touches[0] : e;
    currentX = touch.clientX;
    currentY = touch.clientY;
    
    const deltaX = currentX - startX;
    const deltaY = Math.abs(currentY - startY);
    
    // Only allow horizontal swipe if it's more horizontal than vertical
    if (Math.abs(deltaX) > deltaY && Math.abs(deltaX) > 20) {
      const currentCard = cards[currentCardIndex];
      
      // Move card
      currentCard.style.left = deltaX + 'px';
      currentCard.style.transform = `rotate(${deltaX * 0.1}deg)`;
      
      // Show appropriate indicator
      if (deltaX > 50) {
        currentCard.classList.add('swiping-right');
        currentCard.classList.remove('swiping-left');
      } else if (deltaX < -50) {
        currentCard.classList.add('swiping-left');
        currentCard.classList.remove('swiping-right');
      } else {
        currentCard.classList.remove('swiping-right', 'swiping-left');
      }
    }
  }

  function handleEnd(e) {
    if (!isDragging) return;
    
    isDragging = false;
    const deltaX = currentX - startX;
    const currentCard = cards[currentCardIndex];
    
    // Reset if not swiped enough
    if (Math.abs(deltaX) < 100) {
      currentCard.style.left = '0px';
      currentCard.style.transform = '';
      currentCard.classList.remove('swiping-right', 'swiping-left');
      return;
    }
    
    // Handle swipe actions
    if (deltaX > 100) {
      // Swiped right - mark as correct
      handleRightSwipe(currentCard);
    } else if (deltaX < -100) {
      // Swiped left - flag card
      handleLeftSwipe(currentCard);
    }
  }

  function handleRightSwipe(card) {
    // Animate card out
    card.classList.add('removing');
    card.style.left = '100%';
    card.style.transform = 'rotate(30deg)';
    
    setTimeout(() => {
      moveToNextCard();
    }, 300);
  }

  function handleLeftSwipe(card) {
    const cardId = card.getAttribute('data-card-id');
    
    // Send flag update to server
    fetch('flag_card.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        card_id: cardId,
        is_flagged: 1
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Animate card out
        card.classList.add('removing');
        card.style.left = '-100%';
        card.style.transform = 'rotate(-30deg)';
        
        setTimeout(() => {
          moveToNextCard();
        }, 300);
      } else {
        // Reset card position if flagging failed
        resetCard(card);
        alert('Gagal memflag kartu');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      resetCard(card);
      alert('Terjadi kesalahan');
    });
  }

  function resetCard(card) {
    card.style.left = '0px';
    card.style.transform = '';
    card.classList.remove('swiping-right', 'swiping-left');
  }

  function moveToNextCard() {
    const currentCard = cards[currentCardIndex];
    currentCard.style.display = 'none';
    
    currentCardIndex++;
    
    if (currentCardIndex < cards.length) {
      const nextCard = cards[currentCardIndex];
      nextCard.style.display = 'block';
      nextCard.style.left = '0px';
      nextCard.style.transform = '';
      nextCard.classList.remove('removing', 'flipped', 'swiping-right', 'swiping-left');
    } else {
      // All cards completed
      document.querySelector('.card-container').innerHTML = 
        '<div style="text-align: center; padding: 50px; font-size: 1.5rem; color: #3787FF;">🎉 Semua kartu selesai! 🎉</div>';
    }
  }

  // Comment form submission
  document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData,
    }).then(response => response.json())
    .then(data => {
      if(data.success) {
        const commentList = document.getElementById('comments-list');
        const newComment = document.createElement('div');
        newComment.classList.add('comment');
        newComment.innerHTML = `<strong>${data.username}</strong>${data.comment_html}`;
        commentList.prepend(newComment);
        form.reset();
      } else {
        alert('Gagal mengirim komentar.');
      }
    }).catch(() => alert('Gagal mengirim komentar.'));
  });
</script>
</body>
</html>