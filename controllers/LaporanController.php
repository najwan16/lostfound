<?php
require_once __DIR__ . '/../models/LaporanModel.php';

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

    public function submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $file = null)
    {
        // 1. Validasi input wajib
        if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
            return ['success' => false, 'message' => 'Semua field wajib diisi'];
        }

        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas'];
        }

        $fotoPath = null;

        // 2. PROSES UPLOAD GAMBAR
        if ($file && $file['error'] === UPLOAD_ERR_OK) {

            // Validasi ukuran (max 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB'];
            }

            // Validasi ekstensi
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed)) {
                return ['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF'];
            }

            // Direktori upload
            $uploadDir = __DIR__ . '/../uploads/laporan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // --- BERSIHKAN NAMA + TAMBAHKAN ID USER ---
            $cleanName = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $namaBarang);
            $cleanName = preg_replace('/\s+/', '-', trim($cleanName));
            $cleanName = strtolower($cleanName);
            $cleanName = substr($cleanName, 0, 30);
            $cleanName = rtrim($cleanName, '-');
            if (empty($cleanName)) $cleanName = 'barang';

            $date = date('Ymd');
            $random = rand(1000, 9999);

            $newName = "laporan_{$idAkun}_{$cleanName}_{$date}_{$random}.{$ext}";
            // --- AKHIR ---

            $destination = $uploadDir . $newName;

            // Upload file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $fotoPath = 'uploads/laporan/' . $newName;
            } else {
                return ['success' => false, 'message' => 'Gagal mengunggah gambar ke server'];
            }
        }

        // 3. SIMPAN KE DATABASE
        $result = $this->model->simpanLaporanHilang(
            $idAkun,
            $namaBarang,
            $deskripsiFisik,
            $kategori,
            $lokasi,
            $waktu,
            $fotoPath
        );

        return $result;
    }

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

    public function submitLaporanDitemukan($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
            return ['success' => false, 'message' => 'Semua field wajib diisi'];
        }

        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'satpam') {
            return ['success' => false, 'message' => 'Akses ditolak'];
        }

        $result = $this->model->simpanLaporanDitemukan(
            $idAkun,
            $namaBarang,
            $deskripsiFisik,
            $kategori,
            $lokasi,
            $waktu
        );

        return $result;
    }
}
