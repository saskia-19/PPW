<?php
session_start();
require_once 'config/db_connection.php';  // Pastikan koneksi ke database benar

// Cek user sudah login atau belum
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['profile_picture'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Validasi tipe file
    if (!in_array($file['type'], $allowedTypes)) {
        die('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
    }
    
    // Validasi error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Error saat meng-upload file.');
    }
    
    // Buat nama file baru agar unik
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $newFileName;
    
    // Pindahkan file ke folder uploads
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$newFileName, $_SESSION['user_id']]);
        
        // Redirect ke profile dengan sukses
        header('Location: profile.php');
        exit;
    } else {
        die('Gagal menyimpan file.');
    }
} else {
    die('Tidak ada file yang di-upload.');
}
