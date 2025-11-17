<?php
// app/Controllers/NotifikasiController.php

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
            header('Location: index.php?action=home');
            exit;
        }

        $idAkun = $this->session->get('userId');
        $hariIni = $this->model->getNotifikasiByPeriode($idAkun, 'hari_ini');
        $mingguIni = $this->model->getNotifikasiByPeriode($idAkun, 'minggu_ini');
        $totalUnread = count(array_filter($hariIni, fn($n) => $n['dibaca'] == 0));

        // KIRIM KE SIDEBAR
        $GLOBALS['sessionManager'] = $this->session;
        $GLOBALS['current_page'] = 'mail';

        $page_title = 'Kotak Masuk - Lost & Found FILKOM';
        require 'app/Views/admin/mail.php';
    }

    public function markAsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            exit;
        }

        $idPemberitahuan = (int)($_POST['id_pemberitahuan'] ?? 0);
        if ($idPemberitahuan <= 0) {
            echo json_encode(['success' => false]);
            exit;
        }

        $success = $this->model->tandaiDibaca($idPemberitahuan);
        echo json_encode(['success' => $success]);
        exit;
    }
}
