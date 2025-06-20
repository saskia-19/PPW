<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'config/db_connection.php';

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        header("Location: login.php?signup=success");
        exit();
    } catch (PDOException $e) {
        $error = "Registrasi gagal: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        font-family: 'Mitr', sans-serif;
        background-color: #f9f9f9;
    }

    .signup-container {
        width: 600px;
        margin: 100px auto;
        padding: 40px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .signup-title {
        font-size: 36px;
        font-weight: 400;
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .error-message {
        color: red;
        text-align: center;
        margin-bottom: 20px;
        font-size: 16px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    label {
        font-size: 18px;
        font-weight: 300;
        color: #333;
        display: block;
        margin-bottom: 8px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 16px;
        font-size: 18px;
        border: none;
        border-radius: 15px;
        background-color: #e5efff;
    }

    .signup-button {
        width: 100%;
        background-color: #3787ff;
        color: white;
        padding: 16px;
        font-size: 20px;
        font-weight: 400;
        border: none;
        border-radius: 15px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .signup-button:hover {
        background-color: #2e6fd9;
    }

    .login-link {
        text-align: center;
        margin-top: 24px;
        font-size: 16px;
        opacity: 0.6;
    }

    .login-link a {
        color: #3787ff;
        text-decoration: none;
        margin-left: 8px;
        font-weight: 400;
        opacity: 1;
    }
</style>

<div class="signup-container">
    <div class="signup-title">Sign In</div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="signin.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit" class="signup-button">Sign In</button>
    </form>

    <div class="login-link">
        Sudah punya akun?
        <a href="login.php">Login disini</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
