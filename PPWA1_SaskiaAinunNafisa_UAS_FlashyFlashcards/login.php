<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'config/db_connection.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Email atau password salah";
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        font-family: 'Mitr', sans-serif;
        background-color: #f9f9f9;
    }

    .login-container {
        width: 600px;
        max-width: 90%;
        margin: 100px auto;
        padding: 40px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .login-title {
        font-size: 36px;
        font-weight: 400;
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .error-message,
    .success-message {
        text-align: center;
        margin-bottom: 20px;
        font-size: 16px;
        padding: 12px;
        border-radius: 10px;
    }

    .error-message {
        background-color: #ffe5e5;
        color: #cc0000;
    }

    .success-message {
        background-color: #e6ffe5;
        color: #2d7a1f;
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

    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 16px;
        font-size: 18px;
        border: none;
        border-radius: 15px;
        background-color: #e5efff;
    }

    .login-button {
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

    .login-button:hover {
        background-color: #2e6fd9;
    }

    .signup-link {
        text-align: center;
        margin-top: 24px;
        font-size: 16px;
        opacity: 0.6;
    }

    .signup-link a {
        color: #3787ff;
        text-decoration: none;
        margin-left: 8px;
        font-weight: 400;
        opacity: 1;
    }
</style>

<div class="login-container">
    <div class="login-title">Login</div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'makasih'): ?>
        <div class="success-message">Terima kasih, sampai jumpa lagi! 👋</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit" class="login-button">Login</button>
    </form>

    <div class="signup-link">
        Belum punya akun?
        <a href="signin.php">Daftar di sini</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
