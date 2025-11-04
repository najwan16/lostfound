<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/header.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php?action=home">
                <img src="../images/head.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40';">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="center-nav navbar-nav">
                    <?php
                    require_once __DIR__ . '/../controllers/AuthController.php';
                    $controller = new AuthController();
                    $currentPage = isset($_GET['action']) ? $_GET['action'] : 'home';
                    ?>
                    <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="../index.php?action=home">Beranda</a>
                    <?php if ($controller->getSessionManager()->get('userId')): ?>

                        <a class="nav-link <?php echo $currentPage === 'search_page' ? 'active' : ''; ?>" href="../index.php?action=search_page">Cari</a>

                        <a class="nav-link <?php echo $currentPage === 'laporan' ? 'active' : ''; ?>" href="../index.php?action=laporan">Laporan</a>
                        <a class="nav-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" href="../index.php?action=profile">Profil</a>
                    <?php else: ?>
                        <a class="nav-link <?php echo $currentPage === 'search_page' ? 'active' : ''; ?>" href="../index.php?action=search_page">Cari</a>
                        <a class="nav-link <?php echo $currentPage === 'laporan_form' ? 'active' : ''; ?>" href="../index.php?action=laporan_form">Laporan</a>
                    <?php endif; ?>
                </div>

                <div class="nav-logout ms-auto">
                    <?php if ($controller->getSessionManager()->get('userId')): ?>
                        <a class="btn btn-outline-danger btn-logout" href="../logout.php">Keluar</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary btn-logout" href="../index.php?action=login">Masuk</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>