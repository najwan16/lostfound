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

        // 2. PROSES UPLOAD GAMBAR (URUTAN BENAR)
        if ($file && $file['error'] === UPLOAD_ERR_OK) {

            // CEK UKURAN DULU (SEBELUM PINDAH FILE)
            if ($file['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB'];
            }

            $uploadDir = __DIR__ . '/../../uploads/laporan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed)) {
                return ['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF'];
            }

            $newName = 'laporan_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $destination = $uploadDir . $newName;

            // BARU PINDAHKAN FILE
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $fotoPath = 'uploads/laporan/' . $newName;
                error_log("File uploaded to: $destination"); // cek di error.log
            } else {
                error_log("Failed to move file. Error: " . $file['error']);
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
}
