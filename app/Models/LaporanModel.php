<?php
// app/Models/LaporanModel.php

namespace Models;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // PHPMailer
require_once __DIR__ . '/../../config/Mail.php'; // Class Mail

use Mail;

use PHPMailer\PHPMailer\PHPMailer;

class LaporanModel
{
    private $database;

    public function __construct()
    {
        $this->database = getDB();
    }

    // ==================================================================
    //  LAPORAN HILANG
    // ==================================================================
    public function simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath = null)
    {
        try {
            $validLokasi = [
                'Smart Class Gedung F',
                'Junction',
                'Gedung Kreativitas Mahasiswa (GKM)',
                'kantin',
                'Ruang Baca',
                'Laboratorium Pembelajaran',
                'Ruang Ujian',
                'ruang tunggu',
                'Gazebo lantai 4',
                'Area Parkir',
                'EduTech',
                'Mushola Ulul Al-Baab',
                'auditorium algoritma'
            ];
            $lokasi = in_array($lokasi, $validLokasi) ? $lokasi : null;

            $stmt = $this->database->prepare("
                INSERT INTO laporan 
                (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status, foto)
                VALUES (?, 'hilang', ?, ?, ?, ?, ?, 'belum_ditemukan', ?)
            ");
            $stmt->execute([$idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath]);
            return ['success' => true, 'message' => 'Laporan berhasil disimpan'];
        } catch (\PDOException $e) {
            error_log("Error simpan laporan hilang: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan laporan'];
        }
    }

    public function getRiwayatLaporan($idAkun)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT id_laporan, nama_barang, kategori, lokasi, waktu, status, foto, created_at
                FROM laporan
                WHERE id_akun = ? AND tipe_laporan = 'hilang'
                ORDER BY created_at DESC
            ");
            $stmt->execute([$idAkun]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error get riwayat: " . $e->getMessage());
            return [];
        }
    }

    public function getAllLaporanHilang()
    {
        try {
            $stmt = $this->database->prepare("
                SELECT id_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status, foto
                FROM laporan
                WHERE tipe_laporan = 'hilang'
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error get all hilang: " . $e->getMessage());
            return [];
        }
    }

    // ==================================================================
    //  LAPORAN DITEMUKAN
    // ==================================================================
    public function simpanLaporanDitemukan($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO laporan 
                (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status)
                VALUES (?, 'ditemukan', ?, ?, ?, ?, ?, 'ditemukan')
            ");
            $stmt->execute([$idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu]);
            return ['success' => true, 'message' => 'Laporan ditemukan berhasil disimpan'];
        } catch (\PDOException $e) {
            error_log("Error simpan ditemukan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan laporan'];
        }
    }

    // ==================================================================
    //  NOTIFIKASI OTOMATIS: EMAIL (CIVITAS) + DASHBOARD (SATPAM)
    // ==================================================================
    public function kirimNotifikasiPencocokan(int $idLaporanDitemukan): void
    {
        $pdo = getDB();

        // Ambil data laporan ditemukan
        $stmt = $pdo->prepare("
            SELECT l.nama_barang, l.deskripsi_fisik, l.kategori, l.lokasi, l.created_at AS waktu_ditemukan
            FROM laporan l
            WHERE l.id_laporan = ? AND l.tipe_laporan = 'ditemukan'
        ");
        $stmt->execute([$idLaporanDitemukan]);
        $ditemukan = $stmt->fetch();

        if (!$ditemukan) return;

        // Cari laporan hilang yang cocok
        $stmt = $pdo->prepare("
            SELECT 
                l.id_laporan, 
                l.id_akun, 
                a.email, 
                a.nama, 
                l.nama_barang AS nama_hilang, 
                l.deskripsi_fisik AS deskripsi_hilang,
                l.kategori,
                l.lokasi,
                l.waktu AS waktu_hilang,
                l.created_at AS waktu_laporan
            FROM laporan l
            JOIN akun a ON l.id_akun = a.id_akun
            WHERE l.tipe_laporan = 'hilang' AND l.status = 'belum_ditemukan'
        ");
        $stmt->execute();
        $laporanHilang = $stmt->fetchAll();

        foreach ($laporanHilang as $hilang) {
            $similarNama = similar_text(strtolower($ditemukan['nama_barang']), strtolower($hilang['nama_hilang']), $persenNama);
            $similarDeskripsi = similar_text(strtolower($ditemukan['deskripsi_fisik']), strtolower($hilang['deskripsi_hilang']), $persenDeskripsi);
            $persenKesamaan = max($persenNama, $persenDeskripsi);

            if (
                $persenKesamaan >= 70 &&
                $ditemukan['kategori'] === $hilang['kategori'] &&
                $ditemukan['lokasi'] === $hilang['lokasi']
            ) {
                // === EMAIL KE CIVITAS (SUNGGUHAN + HTML) ===
                if (preg_match('/@(student\.)?ub\.ac\.id$/i', $hilang['email'])) {
                    $html = $this->getEmailTemplate($hilang, $ditemukan, $hilang['id_laporan']);
                    $text = strip_tags($html);

                    // Kirim email sungguhan
                    $sent = Mail::send($hilang['email'], 'Barang Anda Mungkin Ditemukan!', $html, $text);

                    // Simpan ke DB untuk log
                    $status = $sent ? 'Terkirim' : 'Gagal kirim';
                    $this->simpanNotifikasi($hilang['id_akun'], $idLaporanDitemukan, "Email: $status", 'email');
                }

                // === DASHBOARD KE SEMUA SATPAM ===
                $pesanSatpam = "Barang \"{$ditemukan['nama_barang']}\" cocok dengan laporan hilang ID #{$hilang['id_laporan']}";
                $stmtSatpam = $pdo->prepare("SELECT id_akun FROM akun WHERE role = 'satpam'");
                $stmtSatpam->execute();
                foreach ($stmtSatpam->fetchAll() as $satpam) {
                    $this->simpanNotifikasi($satpam['id_akun'], $idLaporanDitemukan, $pesanSatpam, 'dashboard');
                }
            }
        }
    }

    // ==================================================================
    //  TEMPLATE EMAIL HTML (DIPERBAIKI: LEBIH KOMUNIKATIF, RAPI, DAN INFORMATIF)
    // ==================================================================
    private function getEmailTemplate($hilang, $ditemukan, $idLaporan): string
    {
        $urlDetail = "https://sipan.filkom.ub.ac.id/index.php?action=laporan-detail&id={$idLaporan}";
        $urlSitus = "https://sipan.filkom.ub.ac.id";

        // Format waktu untuk tampilan ramah
        $waktuHilangFormat = date('d M Y, H:i', strtotime($hilang['waktu_hilang']));
        $waktuDitemukanFormat = date('d M Y, H:i', strtotime($ditemukan['waktu_ditemukan']));
        $waktuLaporanFormat = date('d M Y, H:i', strtotime($hilang['waktu_laporan']));

        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Barang Anda Ditemukan! - Lost & Found FILKOM UB</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
                    margin: 0; 
                    padding: 20px; 
                    line-height: 1.6;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white; 
                    border-radius: 16px; 
                    overflow: hidden; 
                    box-shadow: 0 8px 32px rgba(0,0,0,0.1); 
                }
                .header { 
                    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center; 
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 28px; 
                    font-weight: 700; 
                }
                .header p { 
                    margin: 10px 0 0; 
                    font-size: 16px; 
                    opacity: 0.9; 
                }
                .content { 
                    padding: 40px 30px; 
                    color: #333; 
                }
                .content h2 { 
                    color: #0d6efd; 
                    margin-top: 0; 
                    font-size: 24px; 
                    font-weight: 600; 
                }
                .greeting { 
                    font-size: 18px; 
                    color: #0d6efd; 
                    margin-bottom: 20px; 
                }
                .item { 
                    background: #f8f9ff; 
                    padding: 20px; 
                    border-radius: 12px; 
                    margin: 20px 0; 
                    border-left: 5px solid #0d6efd; 
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
                }
                .item h3 { 
                    margin: 0 0 10px; 
                    color: #0d6efd; 
                    font-size: 18px; 
                }
                .item p { 
                    margin: 5px 0; 
                    font-size: 14px; 
                }
                .item strong { 
                    color: #333; 
                }
                .instructions { 
                    background: #e7f3ff; 
                    padding: 20px; 
                    border-radius: 12px; 
                    margin: 25px 0; 
                    border-left: 5px solid #0d6efd; 
                }
                .instructions h3 { 
                    margin-top: 0; 
                    color: #0d6efd; 
                }
                .instructions ul { 
                    margin: 10px 0; 
                    padding-left: 20px; 
                }
                .instructions li { 
                    margin: 5px 0; 
                    font-size: 14px; 
                }
                .btn { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
                    color: white; 
                    padding: 14px 32px; 
                    text-decoration: none; 
                    border-radius: 10px; 
                    font-weight: 600; 
                    margin: 20px 0; 
                    transition: transform 0.2s; 
                }
                .btn:hover { 
                    background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%); 
                    transform: translateY(-2px); 
                }
                .disclaimer { 
                    font-size: 13px; 
                    color: #777; 
                    margin-top: 30px; 
                    text-align: center; 
                    padding-top: 20px; 
                    border-top: 1px solid #eee; 
                }
                .footer { 
                    background: #f8f9fa; 
                    padding: 25px 30px; 
                    text-align: center; 
                    color: #666; 
                    font-size: 13px; 
                }
                .footer a { 
                    color: #0d6efd; 
                    text-decoration: none; 
                }
                @media (max-width: 600px) {
                    .container { margin: 10px; border-radius: 12px; }
                    .header, .content, .footer { padding: 20px; }
                    .header h1 { font-size: 24px; }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛡️ Lost & Found FILKOM UB</h1>
                    <p>Senang berbagi kabar baik untuk Anda!</p>
                </div>
                <div class='content'>
                    <h2 class='greeting'>Halo {$hilang['nama']},</h2>
                    <p>Kami punya kabar gembira! Barang yang Anda laporkan hilang kemungkinan besar telah <strong>ditemukan</strong> oleh rekan civitas atau satpam. Ini bisa jadi hari yang lebih cerah bagi Anda! 😊</p>
                    
                    <div class='item'>
                        <h3>📦 Detail Barang Anda yang Hilang</h3>
                        <p><strong>Nama Barang:</strong> {$hilang['nama_hilang']}</p>
                        <p><strong>Kategori:</strong> " . ucfirst($hilang['kategori']) . "</p>
                        <p><strong>Lokasi Terakhir Dilihat:</strong> {$hilang['lokasi']}</p>
                        <p><strong>Waktu Hilang:</strong> {$waktuHilangFormat}</p>
                        <p><strong>Laporan Dibuat:</strong> {$waktuLaporanFormat}</p>
                        " . (!empty($hilang['deskripsi_hilang']) ? "<p><strong>Deskripsi Fisik:</strong> {$hilang['deskripsi_hilang']}</p>" : '') . "
                    </div>
                    
                    <div class='item'>
                        <h3>🔍 Detail Barang yang Ditemukan</h3>
                        <p><strong>Nama Barang:</strong> {$ditemukan['nama_barang']}</p>
                        <p><strong>Kategori:</strong> " . ucfirst($ditemukan['kategori']) . "</p>
                        <p><strong>Lokasi Ditemukan:</strong> {$ditemukan['lokasi']}</p>
                        <p><strong>Waktu Ditemukan:</strong> {$waktuDitemukanFormat}</p>
                        " . (!empty($ditemukan['deskripsi_fisik']) ? "<p><strong>Deskripsi Fisik:</strong> {$ditemukan['deskripsi_fisik']}</p>" : '') . "
                    </div>
                    
                    <div class='instructions'>
                        <h3>🚀 Langkah Selanjutnya</h3>
                        <p>Silakan kunjungi <strong>Pos Satpam FILKOM UB</strong> secepatnya untuk verifikasi dan klaim barang Anda. Proses ini gratis dan cepat!</p>
                        <ul>
                            <li>Bawa <strong>Kartu Tanda Mahasiswa (KTM)</strong> atau identitas resmi UB Anda.</li>
                            <li>Siapkan <strong>deskripsi ciri khusus</strong> barang untuk verifikasi (misalnya: warna, merek, atau isi).</li>
                            <li>Kontak Satpam: <strong>0891-2312-313 (Ahmad Satpam)</strong> untuk konfirmasi kedatangan.</li>
                            <li>Jam Operasional: Senin-Jumat, 08:00 - 17:00 WIB.</li>
                        </ul>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='{$urlDetail}' class='btn'>Lihat Detail Laporan & Klaim Sekarang</a>
                    </div>
                    
                    <div class='disclaimer'>
                        <p>Email ini dikirim secara otomatis oleh sistem Lost & Found. Jika ini bukan barang Anda, abaikan saja atau hubungi kami melalui situs. Kami di sini untuk membantu!</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Lost & Found - Fakultas Ilmu Komputer Universitas Brawijaya</p>
                    <p><a href='{$urlSitus}'>sipan.filkom.ub.ac.id</a> | <a href='mailto:support@filkom.ub.ac.id'>support@filkom.ub.ac.id</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    // ==================================================================
    //  SIMPAN NOTIFIKASI KE DB (ANTI-DUPLIKAT)
    // ==================================================================
    private function simpanNotifikasi(int $idAkun, int $idLaporan, string $pesan, string $tipe): void
    {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO pemberitahuan_sistem 
            (id_akun, id_laporan, pesan, tipe_notif, waktu_kirim, dibaca)
            VALUES (?, ?, ?, ?, NOW(), 0)
            ON DUPLICATE KEY UPDATE 
                pesan = VALUES(pesan), 
                waktu_kirim = NOW()
        ");
        $stmt->execute([$idAkun, $idLaporan, $pesan, $tipe]);
    }
}
