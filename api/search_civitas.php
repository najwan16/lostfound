<?php
// api/search_civitas.php
require_once '../config/db.php';
header('Content-Type: application/json');

$nim = trim($_GET['nim'] ?? '');
if (strlen($nim) < 3) {
    echo json_encode([]);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT a.nama, c.nomor_induk 
    FROM akun a
    JOIN civitas c ON a.id_akun = c.id_akun
    WHERE a.role = 'civitas' AND c.nomor_induk LIKE ?
    ORDER BY c.nomor_induk
    LIMIT 10
");
$stmt->execute(["%$nim%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
