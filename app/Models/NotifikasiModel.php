<?php

namespace Models;

require_once __DIR__ . '/../../config/db.php';

class NotifikasiModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    /**
     * Ambil notifikasi untuk satpam berdasarkan rentang waktu
     */
    public function getNotifikasiByPeriode(int $idAkun, string $periode = 'hari_ini'): array
    {
        $sql = "
            SELECT 
                p.id_pemberitahuan,
                p.id_laporan,
                p.pesan,
                p.waktu_kirim,
                p.dibaca,
                l.nama_barang,
                l.lokasi,
                l.kategori,
                l.tipe_laporan
            FROM pemberitahuan_sistem p
            JOIN laporan l ON p.id_laporan = l.id_laporan
            WHERE p.id_akun = ? AND p.tipe_notif = 'dashboard'
        ";

        if ($periode === 'hari_ini') {
            $sql .= " AND DATE(p.waktu_kirim) = CURDATE()";
        } elseif ($periode === 'minggu_ini') {
            $sql .= " AND p.waktu_kirim >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $sql .= " ORDER BY p.waktu_kirim DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAkun]);
        return $stmt->fetchAll();
    }

    /**
     * Tandai notifikasi sebagai dibaca
     */
    public function tandaiDibaca(int $idPemberitahuan): bool
    {
        $stmt = $this->pdo->prepare("UPDATE pemberitahuan_sistem SET dibaca = 1 WHERE id_pemberitahuan = ?");
        return $stmt->execute([$idPemberitahuan]);
    }
}
