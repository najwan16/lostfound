<?php
// app/Controllers/ClaimController.php

require_once __DIR__ . '/../Models/ClaimModel.php';

use Models\ClaimModel;

class ClaimController
{
    private $model;
    private $session;

    public function __construct($sessionManager)
    {
        $this->session = $sessionManager;
        $this->model = new ClaimModel();
    }

    public function showForm()
    {
        if ($this->session->get('role') !== 'civitas') {
            $this->redirect('home');
        }

        $id_laporan = (int)($_GET['id'] ?? 0);  // ubah nama variabel jadi konsisten
        if ($id_laporan <= 0) {
            $this->redirect('laporan');
        }

        // Ambil data laporan
        $laporan = $this->model->getLaporan($id_laporan);
        if (!$laporan || $laporan['status'] !== 'sudah_diambil') {
            $this->redirect('laporan');
        }

        $userId = $this->session->get('userId');

        // Cek bukan punya sendiri
        if ($laporan['id_akun'] == $userId) {
            $this->redirect('laporan-detail', ['id' => $id_laporan, 'error' => 'Tidak bisa claim laporan sendiri']);
        }

        // Cek sudah claim belum
        if ($this->model->hasClaim($id_laporan, $userId)) {
            $this->redirect('laporan-detail', ['id' => $id_laporan, 'error' => 'Anda sudah mengajukan claim']);
        }

        // Ambil data user
        $user = $this->model->getUser($userId);
        if (!$user) {
            die('Data pengguna tidak ditemukan.');
        }

        // KIRIM SEMUA VARIABEL YANG DIBUTUHKAN VIEW!   
        require 'app/Views/civitas/claim_form.php';
    }

    public function submitClaim()
    {
        if ($this->session->get('role') !== 'civitas' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('home');
        }

        $userId = $this->session->get('userId');
        $idLaporan = (int)($_POST['id_laporan'] ?? 0);
        $deskripsiCiri = trim($_POST['deskripsi_ciri'] ?? '');
        $buktiFile = $_FILES['bukti_kepemilikan'] ?? null;

        if ($idLaporan <= 0 || empty($deskripsiCiri) || !$buktiFile || $buktiFile['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('claim', ['id' => $idLaporan, 'error' => 'Semua field wajib diisi']);
        }

        $result = $this->model->submitClaim($userId, $idLaporan, $deskripsiCiri, $buktiFile);

        $params = $result['success']
            ? ['id' => $idLaporan, 'success' => 'claim_diajukan']
            : ['id' => $idLaporan, 'error' => $result['message']];

        $this->redirect('laporan-detail', $params);
    }

    public function showMyClaims()
    {
        if ($this->session->get('role') !== 'civitas') {
            $this->redirect('home');
        }

        $idAkun = $this->session->get('userId');

        // === PERBAIKAN DI SINI ===
        $tab = $_GET['tab'] ?? 'semua';                // default semua
        $allowedTabs = ['semua', 'diajukan', 'diverifikasi', 'ditolak'];
        $tab = in_array($tab, $allowedTabs) ? $tab : 'semua';
        // =========================

        // Kirim semua claim (nanti di-filter di model atau di view)
        $claimList = $this->model->getMyClaimsAll($idAkun); // fungsi baru, lihat di bawah

        // Hitung jumlah per status (untuk badge kalau mau)
        $counts = $this->model->countMyClaims($idAkun);

        // Kirim variabel ke view
        extract([
            'claimList' => $claimList,
            'counts'    => $counts,
            'tab'       => $tab,           // penting dikirim supaya sidebar active benar
            'current_page' => 'claim_saya'
        ]);

        require 'app/Views/civitas/claim_saya.php';
    }

    public function showDashboard()
    {
        if ($this->session->get('role') !== 'satpam') {
            $this->redirect('home');
            exit;
        }

        $tab = $_GET['tab'] ?? 'masuk';
        $valid = ['masuk', 'diverifikasi', 'ditolak'];
        $tab = in_array($tab, $valid) ? $tab : 'masuk';

        $statusMap = ['masuk' => 'diajukan', 'diverifikasi' => 'diverifikasi', 'ditolak' => 'ditolak'];
        $status = $statusMap[$tab];

        $claimList = $this->model->getAllClaimsByStatus($status);
        $counts = $this->model->countAllClaims();

        // KIRIM $sessionManager KE VIEW
        extract([
            'claimList'      => $claimList,
            'counts'         => $counts,
            'tab'            => $tab,
            'sessionManager' => $this->session,  // <--- TAMBAHKAN INI
            'current_page'   => 'dashboard_claim'
        ]);

        require 'app/Views/admin/dashboard_claim.php';
    }

    public function verifikasiClaim()
    {
        if ($this->session->get('role') !== 'satpam' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard_claim');
        }

        $idClaim = (int)($_POST['id_claim'] ?? 0);
        $idLaporan = (int)($_POST['id_laporan'] ?? 0);
        $status = $_POST['status'] ?? '';

        error_log("VERIFIKASI: idClaim=$idClaim, idLaporan=$idLaporan, status=$status");

        if ($idClaim <= 0 || !in_array($status, ['diverifikasi', 'ditolak'])) {
            error_log("VERIFIKASI GAGAL: validasi tidak lolos");
            $this->redirect('dashboard_claim');
        }

        $this->model->updateClaimStatus($idClaim, $status);
        if ($status === 'diverifikasi') {
            $this->model->updateLaporanStatus($idLaporan, 'sudah_diambil');
        }

        $tab = $status === 'diverifikasi' ? 'diverifikasi' : 'ditolak';
        error_log("VERIFIKASI SUKSES → redirect ke tab: $tab");

        $this->redirect('dashboard_claim', ['tab' => $tab]);
    }

    private function redirect(string $action, array $params = [])
    {
        $query = $params ? '&' . http_build_query($params) : '';
        $url = "index.php?action={$action}{$query}";

        error_log("REDIRECT KE: " . $url); // DEBUG

        header("Location: {$url}");
        exit; // PENTING: tambahkan exit!
    }
}
