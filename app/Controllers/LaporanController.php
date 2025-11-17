<?php
// app/Controllers/LaporanController.php

require_once __DIR__ . '/../Models/LaporanModel.php';

use Models\LaporanModel;

class LaporanController
{
    private $model;
    private $session;

    public function __construct($sessionManager)
    {
        $this->model = new LaporanModel();
        $this->session = $sessionManager;
    }

    // ==================================================================
    //  LAPORAN HILANG - CIVITAS
    // ==================================================================
    public function submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $file = null)
    {
        if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
            return ['success' => false, 'message' => 'Semua field wajib diisi'];
        }

        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas'];
        }

        $fotoPath = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            if ($file['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB'];
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed)) {
                return ['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF'];
            }

            $uploadDir = __DIR__ . '/../../public/uploads/laporan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $cleanName = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $namaBarang);
            $cleanName = preg_replace('/\s+/', '-', trim($cleanName));
            $cleanName = strtolower($cleanName);
            $cleanName = substr($cleanName, 0, 30);
            $cleanName = rtrim($cleanName, '-');
            if (empty($cleanName)) $cleanName = 'barang';

            $date = date('Ymd');
            $random = rand(1000, 9999);
            $newName = "laporan_{$idAkun}_{$cleanName}_{$date}_{$random}.{$ext}";
            $destination = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $fotoPath = 'uploads/laporan/' . $newName;
            } else {
                return ['success' => false, 'message' => 'Gagal mengunggah gambar ke server'];
            }
        }

        $result = $this->model->simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath);
        return $result;
    }

    // ==================================================================
    //  LAPORAN DITEMUKAN - SATPAM (TRIGGER NOTIFIKASI)
    // ==================================================================
    public function submitLaporanDitemukan($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
            return ['success' => false, 'message' => 'Semua field wajib diisi'];
        }

        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'satpam') {
            return ['success' => false, 'message' => 'Akses ditolak'];
        }

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id_satpam FROM satpam WHERE id_akun = ?");
        $stmt->execute([$idAkun]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan satpam resmi'];
        }

        $result = $this->model->simpanLaporanDitemukan($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);

        // KIRIM NOTIFIKASI JIKA SUKSES
        if ($result['success']) {
            $idLaporan = $pdo->lastInsertId();
            $this->model->kirimNotifikasiPencocokan($idLaporan);
        }

        return $result;
    }

    // ==================================================================
    //  RIWAYAT & HALAMAN
    // ==================================================================
    public function getRiwayatLaporan()
    {
        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Login diperlukan'];
        }

        $riwayat = $this->model->getRiwayatLaporan($idAkun);
        return ['success' => true, 'riwayat' => $riwayat];
    }

    public function getLaporanUser()
    {
        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Login diperlukan'];
        }

        $laporan = $this->model->getRiwayatLaporan($idAkun);
        return ['success' => true, 'laporan' => $laporan];
    }

    public function showLaporanPage()
    {
        $result = $this->getLaporanUser();
        $laporanList = $result['success'] ? $result['laporan'] : [];
        $filter = $_GET['filter'] ?? 'semua';
        $validFilters = ['semua', 'belum_ditemukan', 'sudah_diambil'];
        if (!in_array($filter, $validFilters)) $filter = 'semua';

        require_once 'app/Views/civitas/laporan.php';
    }

    public function searchCivitas()
    {
        if ($this->session->get('role') !== 'satpam') {
            http_response_code(403);
            echo json_encode([]);
            exit;
        }

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
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
