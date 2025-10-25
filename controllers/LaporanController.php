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

    public function submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        // Validasi data (sesuai pseudo-code: reportValid)
        if (empty($namaBarang) || empty($kategori) || empty($waktu)) {
            return ['success' => false, 'message' => 'Formulir tidak lengkap, lengkapi nama barang, kategori, dan waktu'];
        }

        // Ambil id_akun dari session (civitas harus login)
        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas untuk melaporkan'];
        }

        // Simpan via model
        $result = $this->model->simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
        return $result;
    }

    public function getRiwayatLaporan()
    {
        // Check session dan role
        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas untuk melihat riwayat'];
        }

        // Ambil data dari model
        $riwayat = $this->model->getRiwayatLaporan($idAkun);
        if (empty($riwayat)) {
            return ['success' => true, 'riwayat' => [], 'message' => 'Tidak ada riwayat laporan'];
        }

        return ['success' => true, 'riwayat' => $riwayat];
    }
}