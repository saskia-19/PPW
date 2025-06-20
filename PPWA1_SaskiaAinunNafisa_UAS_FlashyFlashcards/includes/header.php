<?php include __DIR__ . '/../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Flashy Flashcards</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Mitr&family=Rubik+One&family=Merriweather+Sans&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/styles.css" />
  <style>
    .navbar {
      background-color: #3787ff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      position: relative;
    }
    .navbar .logo {
      font-family: 'Rubik One', sans-serif;
      font-size: 30px;
      color: white;
      text-decoration: none;
    }
    .navbar .nav-links {
      display: flex;
      gap: 1.5rem;
      font-family: 'Merriweather Sans', sans-serif;
      font-size: 18px;
    }
    .navbar .nav-links a {
      color: white;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .navbar .nav-links a:hover {
      text-shadow: 0 0 5px rgba(255,255,255,0.7);
      transform: translateY(-1px);
    }

    /* Responsive dropdown (basic) */
    @media (max-width: 768px) {
      .navbar {
        flex-direction: column;
        align-items: flex-start;
      }
      .navbar .nav-links {
        flex-direction: column;
        width: 100%;
        margin-top: 1rem;
      }
    }

    body {
      margin: 0;
      font-family: 'Mitr', sans-serif;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <a href="index.php" class="logo">Flashy Flashcards</a>
    <div class="nav-links">
      <a href="browse.php">Browse</a>
      <a href="your_decks.php">Decks</a>
      <a href="create_deck.php">Create</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </nav>
