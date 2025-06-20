<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=u985354573_fp_flashcards', 'u985354573_flashcards', 'LookBack@2005');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>