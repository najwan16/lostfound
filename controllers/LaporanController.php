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
        if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
            return ['success' => false, 'message' => 'Semua field wajib diisi'];
        }

        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas untuk melaporkan'];
        }

        $result = $this->model->simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
        return $result;
    }

    public function getRiwayatLaporan()
    {
        $idAkun = $this->session->get('userId');
        if (!$idAkun || $this->session->get('role') !== 'civitas') {
            return ['success' => false, 'message' => 'Anda harus login sebagai civitas untuk melihat riwayat'];
        }

        $riwayat = $this->model->getRiwayatLaporan($idAkun);
        if (empty($riwayat)) {
            return ['success' => true, 'riwayat' => [], 'message' => 'Tidak ada riwayat laporan'];
        }

        return ['success' => true, 'riwayat' => $riwayat];
    }
}