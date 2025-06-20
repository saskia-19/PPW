<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

// Ambil data deck terpopuler
$stmt = $pdo->query("SELECT * FROM decks WHERE is_public = 1 ORDER BY created_at DESC LIMIT 3");
$popular_decks = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<!-- Hero Section -->
<div class="hero-container">
  <h1 class="hero-title">Welcome To Flashy Flashcards</h1>

  <div class="button-container">
    <!-- Answer Button -->
    <button class="tilted-button" onclick="handleButtonClick('answer')">
      <svg class="button-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2L13.09 8.26L19 9L13.09 9.74L12 16L10.91 9.74L5 9L10.91 8.26L12 2Z" fill="currentColor"/>
        <path d="M19 15L20.09 18.26L23 19L20.09 19.74L19 23L17.91 19.74L15 19L17.91 18.26L19 15Z" fill="currentColor"/>
        <path d="M5 15L6.09 18.26L9 19L6.09 19.74L5 23L3.91 19.74L1 19L3.91 18.26L5 15Z" fill="currentColor"/>
      </svg>
      <p class="button-text">Answer</p>
    </button>

    <!-- Question Button -->
    <button class="tilted-button" onclick="handleButtonClick('question')">
      <svg class="button-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z" fill="currentColor"/>
      </svg>
      <p class="button-text">Question</p>
    </button>

    <!-- Create Button -->
    <button class="tilted-button" onclick="handleButtonClick('create')">
      <svg class="button-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" fill="currentColor"/>
      </svg>
      <p class="button-text">Create</p>
    </button>
  </div>

  <div class="login-container">
    <a href="login.php" class="login-button">Login</a>
  </div>
</div>

<!-- CSS -->
<style>
  body {
    margin: 0;
    font-family: 'Mitr', sans-serif;
    background-color: white;
  }

  .hero-container {
    padding-top: 120px; /* ruang buat header */
    padding-bottom: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 60px;
    min-height: 100vh;
    box-sizing: border-box;
  }

  .hero-title {
    font-size: 64px;
    font-weight: 400;
    color: black;
    text-align: center;
    margin: 0;
  }

  .button-container {
    display: flex;
    gap: 60px;
    flex-wrap: wrap;
    justify-content: center;
    padding: 20px;
  }

  .tilted-button {
    width: 291px;
    height: 354px;
    background: linear-gradient(115deg, #37b2ff 0%, #00378a 100%);
    border-radius: 30px;
    transform: rotate(-14deg);
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 20px;
    color: white;
    text-decoration: none;
  }

  .tilted-button:hover {
    transform: rotate(-14deg) scale(1.05);
    background: linear-gradient(115deg, #4ac3ff 0%, #1a4a9a 100%);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
  }

  .tilted-button:active {
    transform: rotate(-14deg) scale(0.95);
    transition: all 0.1s ease;
  }

  .button-icon {
    width: 90px;
    height: 90px;
    filter: brightness(0) invert(1);
    transition: all 0.3s ease;
  }

  .tilted-button:hover .button-icon {
    transform: scale(1.1);
    filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
  }

  .button-text {
    font-size: 32px;
    font-weight: 500;
    text-align: center;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .tilted-button:hover .button-text {
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
  }

  .tilted-button::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }

  .tilted-button:active::before {
    width: 300px;
    height: 300px;
  }

  .login-container {
    margin-top: 40px;
  }

  .login-button {
    display: inline-block;
    width: 298px;
    height: 80px;
    background: #3787ff;
    border-radius: 30px;
    text-decoration: none;
    color: white;
    font-size: 40px;
    line-height: 80px;
    text-align: center;
    font-weight: 400;
    transition: all 0.3s ease;
  }

  .login-button:hover {
    background: #2a6fd1;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  }

  .login-button:active {
    transform: translateY(-1px);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
  }

  @media (max-width: 1000px) {
    .button-container {
      flex-direction: column;
      gap: 40px;
    }

    .tilted-button {
      transform: rotate(0deg);
    }

    .tilted-button:hover {
      transform: scale(1.05);
    }

    .tilted-button:active {
      transform: scale(0.95);
    }
  }

  @media (max-width: 400px) {
    .tilted-button {
      width: 250px;
      height: 300px;
    }

    .button-icon {
      width: 70px;
      height: 70px;
    }

    .button-text {
      font-size: 28px;
    }

    .login-button {
      width: 250px;
      font-size: 32px;
    }
  }
</style>

<!-- JavaScript -->
<script>
  function handleButtonClick(action) {
    const button = event.target.closest('.tilted-button');
    button.style.transform += ' scale(0.9)';

    setTimeout(() => {
      button.style.transform = button.style.transform.replace(' scale(0.9)', '');
    }, 150);

    switch(action) {
      case 'answer':
        alert('Answer functionality triggered!');
        break;
      case 'question':
        alert('Question functionality triggered!');
        break;
      case 'create':
        alert('Create functionality triggered!');
        break;
    }
  }

  // Keyboard nav
  document.addEventListener('keydown', function(e) {
    const buttons = document.querySelectorAll('.tilted-button');
    const focused = document.activeElement;
    const current = Array.from(buttons).indexOf(focused);

    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
      e.preventDefault();
      let next = (e.key === 'ArrowLeft')
        ? (current > 0 ? current - 1 : buttons.length - 1)
        : (current < buttons.length - 1 ? current + 1 : 0);
      buttons[next].focus();
    }

    if (e.key === 'Enter' || e.key === ' ') {
      if (focused.classList.contains('tilted-button')) {
        e.preventDefault();
        focused.click();
      }
    }
  });

  // Tab-able
  document.querySelectorAll('.tilted-button').forEach(button => {
    button.setAttribute('tabindex', '0');
  });
</script>

<?php include 'includes/footer.php'; ?>
