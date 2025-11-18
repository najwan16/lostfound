<?php
// app/Models/ClaimModel.php

namespace Models;

require_once __DIR__ . '/../../config/db.php';

use PDO;
use PDOException;

class ClaimModel
{
    private $database;

    public function __construct()
    {
        $this->database = getDB();
    }

    public function getLaporan($idLaporan)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT l.*, a.nama AS nama_pembuat
                FROM laporan l
                JOIN akun a ON l.id_akun = a.id_akun
                WHERE l.id_laporan = ?
            ");
            $stmt->execute([$idLaporan]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ClaimModel::getLaporan error: " . $e->getMessage());
            return false;
        }
    }

    public function hasClaim($idLaporan, $idAkun)
    {
        try {
            $stmt = $this->database->prepare("SELECT 1 FROM claim WHERE id_laporan = ? AND id_akun = ?");
            $stmt->execute([$idLaporan, $idAkun]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUser($idAkun)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT a.nama, c.nomor_induk
                FROM akun a
                JOIN civitas c ON a.id_akun = c.id_akun
                WHERE a.id_akun = ?
            ");
            $stmt->execute([$idAkun]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function submitClaim($userId, $idLaporan, $deskripsiCiri, $buktiFile)
    {
        try {
            $ext = strtolower(pathinfo($buktiFile['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed) || $buktiFile['size'] > 3 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File harus JPG/PNG dan <3MB'];
            }

            $uploadDir = __DIR__ . '/../../public/uploads/bukti_claim/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $newName = "bukti_claim_{$idLaporan}_{$userId}_" . time() . ".{$ext}";
            $dest = $uploadDir . $newName;

            if (!move_uploaded_file($buktiFile['tmp_name'], $dest)) {
                return ['success' => false, 'message' => 'Gagal upload bukti'];
            }

            $buktiPath = "uploads/bukti_claim/{$newName}";

            $stmt = $this->database->prepare("
                INSERT INTO claim (id_laporan, id_akun, bukti_kepemilikan, deskripsi_ciri, status_claim)
                VALUES (?, ?, ?, ?, 'diajukan')
            ");
            $stmt->execute([$idLaporan, $userId, $buktiPath, $deskripsiCiri]);

            return ['success' => true, 'message' => 'Claim berhasil diajukan'];
        } catch (PDOException $e) {
            error_log("ClaimModel::submitClaim error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan claim'];
        }
    }

    public function getMyClaims($idAkun, $status)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT c.*, l.nama_barang, l.foto, l.lokasi, l.kategori
                FROM claim c
                LEFT JOIN laporan l ON c.id_laporan = l.id_laporan
                WHERE c.id_akun = ? AND c.status_claim = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$idAkun, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ClaimModel::getMyClaims error: " . $e->getMessage());
            return [];
        }
    }

    public function countMyClaims($idAkun)
    {
        try {
            $counts = [];
            foreach (['diajukan', 'diverifikasi', 'ditolak'] as $status) {
                $stmt = $this->database->prepare("SELECT COUNT(*) FROM claim WHERE id_akun = ? AND status_claim = ?");
                $stmt->execute([$idAkun, $status]);
                $counts[$status] = $stmt->fetchColumn();
            }
            return $counts;
        } catch (PDOException $e) {
            return ['diajukan' => 0, 'diverifikasi' => 0, 'ditolak' => 0];
        }
    }

    public function getAllClaimsByStatus($status)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT c.*, l.nama_barang, l.lokasi, l.kategori, l.foto,
                       a.nama AS nama_pengaju, ci.nomor_induk AS nim_pengaju
                FROM claim c
                LEFT JOIN laporan l ON c.id_laporan = l.id_laporan
                LEFT JOIN akun a ON c.id_akun = a.id_akun
                LEFT JOIN civitas ci ON a.id_akun = ci.id_akun
                WHERE c.status_claim = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ClaimModel::getAllClaimsByStatus error: " . $e->getMessage());
            return [];
        }
    }

    public function countAllClaims()
    {
        try {
            $counts = [];
            foreach (['diajukan', 'diverifikasi', 'ditolak'] as $status) {
                $stmt = $this->database->prepare("SELECT COUNT(*) FROM claim WHERE status_claim = ?");
                $stmt->execute([$status]);
                $counts[$status] = $stmt->fetchColumn();
            }
            return $counts;
        } catch (PDOException $e) {
            return ['diajukan' => 0, 'diverifikasi' => 0, 'ditolak' => 0];
        }
    }

    public function updateClaimStatus($idClaim, $status)
    {
        try {
            $stmt = $this->database->prepare("UPDATE claim SET status_claim = ?, updated_at = NOW() WHERE id_claim = ?");
            $stmt->execute([$status, $idClaim]);
        } catch (PDOException $e) {
            error_log("ClaimModel::updateClaimStatus error: " . $e->getMessage());
        }
    }

    public function updateLaporanStatus($idLaporan, $status)
    {
        try {
            $stmt = $this->database->prepare("UPDATE laporan SET status = ? WHERE id_laporan = ?");
            $stmt->execute([$status, $idLaporan]);
        } catch (PDOException $e) {
            error_log("ClaimModel::updateLaporanStatus error: " . $e->getMessage());
        }
    }

    public function getMyClaimsAll($idAkun)
    {
        try {
            $stmt = $this->database->prepare("
            SELECT c.*, l.nama_barang, l.foto AS foto_laporan, l.lokasi, l.kategori
            FROM claim c
            LEFT JOIN laporan l ON c.id_laporan = l.id_laporan
            WHERE c.id_akun = ? 
            ORDER BY c.created_at DESC
        ");
            $stmt->execute([$idAkun]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ClaimModel::getMyClaimsAll error: " . $e->getMessage());
            return [];
        }
    }
}
