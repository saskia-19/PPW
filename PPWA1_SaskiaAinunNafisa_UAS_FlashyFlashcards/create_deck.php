<?php
require 'config/auth_check.php';
require 'config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $tags = explode(',', $_POST['tags']);
    $is_public = $_POST['visibility'] === 'public' ? 1 : 0;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO decks (user_id, title, description, is_public) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $is_public]);
        $deck_id = $pdo->lastInsertId();

        foreach ($tags as $tag_name) {
            $tag_name = trim($tag_name);
            if (!empty($tag_name)) {
                $stmt = $pdo->prepare("SELECT tag_id FROM tags WHERE name = ?");
                $stmt->execute([$tag_name]);
                $tag = $stmt->fetch();

                if (!$tag) {
                    $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                    $stmt->execute([$tag_name]);
                    $tag_id = $pdo->lastInsertId();
                } else {
                    $tag_id = $tag['tag_id'];
                }

                $stmt = $pdo->prepare("INSERT INTO deck_tags (deck_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$deck_id, $tag_id]);
            }
        }

        $pdo->commit();
        header("Location: create_cards.php?deck_id=$deck_id");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal membuat deck: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Create Deck</h1>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="create_deck.php" class="deck-form">
        <label for="title">Deck Name</label>
        <input type="text" id="title" name="title" required>

        <label for="tags">Deck Tags <small>(pisahkan dengan koma)</small></label>
        <input type="text" id="tags" name="tags" placeholder="Tag1, Tag2, Tag3">

        <label for="description">Deck Description</label>
        <textarea id="description" name="description" rows="3"></textarea>

        <fieldset>
            <legend>Who can see your deck?</legend>
            <label>
                <input type="radio" name="visibility" value="private" checked>
                Private
            </label>
            <label>
                <input type="radio" name="visibility" value="public">
                Public
            </label>
        </fieldset>

        <button type="submit" class="btn-submit">Confirm</button>
    </form>
</div>

<style>
  /* Container pusat */
  .container {
    max-width: 700px;
    margin: 40px auto;
    font-family: 'Mitr', sans-serif;
    color: #222;
  }

  h1 {
    font-weight: 400;
    font-size: 3rem;
    margin-bottom: 1.5rem;
  }

  .error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
  }

  form.deck-form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  label {
    font-weight: 300;
    font-size: 1.25rem;
  }

  input[type="text"],
  textarea {
    font-size: 1.25rem;
    padding: 0.5rem 1rem;
    border-radius: 15px;
    border: 1px solid #ccc;
    background-color: #e5efff;
    width: 100%;
    box-sizing: border-box;
    font-family: 'Mitr', sans-serif;
  }

  textarea {
    resize: vertical;
  }

  fieldset {
    border: none;
    padding: 0;
    margin: 0;
  }

  fieldset legend {
    font-weight: 300;
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
  }

  fieldset label {
    margin-right: 1.5rem;
    font-weight: 400;
  }

  input[type="radio"] {
    margin-right: 0.3rem;
    vertical-align: middle;
  }

  .btn-submit {
    background-color: #3787ff;
    color: white;
    font-size: 1.5rem;
    padding: 0.75rem;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    width: 150px;
    justify-self: center;
    font-family: 'Mitr', sans-serif;
    transition: background-color 0.3s ease;
  }

  .btn-submit:hover {
    background-color: #266dcc;
  }
</style>

<?php include 'includes/footer.php'; ?>
