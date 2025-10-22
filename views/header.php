<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/header.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <!-- Left: Logo -->
            <a class="navbar-brand" href="#">
                <img src="../images/head.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40';">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- center -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="center-nav navbar-nav">
                    <?php
                    require_once __DIR__ . '/../controllers/AuthController.php';
                    $controller = new AuthController();
                    $currentPage = isset($_GET['action']) ? $_GET['action'] : 'home';
                    if ($controller->getSessionManager()->get('userId')): ?>
                        <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="#">Beranda</a>
                        <a class="nav-link <?php echo $currentPage === 'cari' ? 'active' : ''; ?>" href="#">Cari</a>
                        <a class="nav-link <?php echo $currentPage === 'laporan' ? 'active' : ''; ?>" href="#">Laporan</a>
                        <a class="nav-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" href="../index.php?action=profile">Profil</a>
                    <?php else: ?>

                    <?php endif; ?>
                </div>

                <!-- Right: Logout or Login -->
                <div class="nav-logout ms-auto">
                    <?php if ($controller->getSessionManager()->get('userId')): ?>
                        <a class="btn-logout" href="../logout.php">Logout</a>
                    <?php else: ?>
                        <a class="btn-logout" href="../index.php?action=login">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>