<?php

// Konfigurasi DB (ganti kalau beda)
$host = 'localhost';
$dbname = 'lostfound';
$username = 'root';
$password = '';  // Kosong kalau default XAMPP

try {
    // Koneksi PDO dengan error handling
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi DB gagal: " . $e->getMessage());
}

// Fungsi untuk ambil PDO (dipanggil di model)
function getDB() {
    global $pdo;
    return $pdo;
}