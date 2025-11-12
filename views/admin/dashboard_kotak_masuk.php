<?php
// dashboard_kotak_masuk.php
$current_page = 'dashboard_kotak_masuk';
$page_title = 'Kotak Masuk - Lost & Found FILKOM';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- CSS Eksternal -->
    <link href="../../css/admin.css" rel="stylesheet">

    <link href="../../css/kotak_masuk.css" rel="stylesheet">

</head>

<body>

    <div class="d-flex">
        <!-- Sidebar akan otomatis muncul dari sidebar.php --> <?php include 'widgets/sidebar.php'; ?>


        <!-- Main Content -->
        <main class="main-content">
            <div class="container-fluid">

                <!-- Hari Ini -->
                <section class="mb-5">
                    <h2 class="section-title">Hari ini</h2>
                    <div class="laporan-list">

                        <div class="laporan-card">
                            <div class="laporan-info">
                                <h6>Casan Laptop Asus</h6>
                                <div class="laporan-meta">
                                    <span class="meta-item"><span class="material-symbols-outlined">location_on</span> GKM</span>
                                    <span class="meta-item"><span class="material-symbols-outlined">category</span> Elektronik</span>
                                </div>
                            </div>
                            <div class="laporan-time">21 Oktober 2025 - 19:27 WIB</div>
                            <div class="laporan-action">
                                <a href="#" class="btn-cocok">Laporan Cocok!</a>
                            </div>
                        </div>

                        <div class="laporan-card">
                            <div class="laporan-info">
                                <h6>Casan Laptop Asus</h6>
                                <div class="laporan-meta">
                                    <span class="meta-item"><span class="material-symbols-outlined">location_on</span> GKM</span>
                                    <span class="meta-item"><span class="material-symbols-outlined">category</span> Elektronik</span>
                                </div>
                            </div>
                            <div class="laporan-time">21 Oktober 2025 - 19:27 WIB</div>
                            <div class="laporan-action">
                                <a href="#" class="btn-cocok">Laporan Cocok!</a>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- Minggu Ini -->
                <section>
                    <h2 class="section-title">Minggu ini</h2>
                    <div class="laporan-list">

                        <div class="laporan-card">
                            <div class="laporan-info">
                                <h6>Casan Laptop Asus</h6>
                                <div class="laporan-meta">
                                    <span class="meta-item"><span class="material-symbols-outlined">location_on</span> GKM</span>
                                    <span class="meta-item"><span class="material-symbols-outlined">category</span> Elektronik</span>
                                </div>
                            </div>
                            <div class="laporan-time">21 Oktober 2025 - 19:27 WIB</div>
                            <div class="laporan-action">
                                <a href="#" class="btn-cocok">Laporan Cocok!</a>
                            </div>
                        </div>

                        <div class="laporan-card">
                            <div class="laporan-info">
                                <h6>Casan Laptop Asus</h6>
                                <div class="laporan-meta">
                                    <span class="meta-item"><span class="material-symbols-outlined">location_on</span> GKM</span>
                                    <span class="meta-item"><span class="material-symbols-outlined">category</span> Elektronik</span>
                                </div>
                            </div>
                            <div class="laporan-time">21 Oktober 2025 - 19:27 WIB</div>
                            <div class="laporan-action">
                                <a href="#" class="btn-cocok">Laporan Cocok!</a>
                            </div>
                        </div>

                        <div class="laporan-card">
                            <div class="laporan-info">
                                <h6>Casan Laptop Asus</h6>
                                <div class="laporan-meta">
                                    <span class="meta-item"><span class="material-symbols-outlined">location_on</span> GKM</span>
                                    <span class="meta-item"><span class="material-symbols-outlined">category</span> Elektronik</span>
                                </div>
                            </div>
                            <div class="laporan-time">21 Oktober 2025 - 19:27 WIB</div>
                            <div class="laporan-action">
                                <a href="#" class="btn-cocok">Laporan Cocok!</a>
                            </div>
                        </div>

                    </div>
                </section>

            </div>
        </main>
    </div>

</body>

</html>