<?php
require_once __DIR__ . '/../Models/NotifikasiModel.php';

use Models\NotifikasiModel;

class NotifikasiController
{
    private $model;
    private $session;

    public function __construct($sessionManager)
    {
        $this->model = new NotifikasiModel();
        $this->session = $sessionManager;
    }

    public function index()
    {
        if ($this->session->get('role') !== 'satpam') {
            header('Location: index.php?action=login');
            exit;
        }

        $idAkun = $this->session->get('userId');

        $hariIni   = $this->model->getNotifikasiByPeriode($idAkun, 'hari_ini');
        $mingguIni = $this->model->getNotifikasiByPeriode($idAkun, 'minggu_ini');

        $totalUnread = 0;
        foreach (array_merge($hariIni, $mingguIni) as $n) {
            if ($n['dibaca'] == 0) $totalUnread++;
        }

        // Kirim data ke view
        $data = compact('hariIni', 'mingguIni', 'totalUnread');
        $GLOBALS['current_page'] = 'mail';

        // Load view melalui layout
        require 'app/Views/admin/mail.php';
    }

    public function markAsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            exit;
        }

        $id = (int)($_POST['id_pemberitahuan'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false]);
            exit;
        }

        $success = $this->model->tandaiDibaca($id);
        echo json_encode(['success' => $success]);
    }
}
