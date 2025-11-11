<!-- admin/widgets/header.php -->
<div class="main-content">
    <div class="container-fluid p-4">
        <div class="page-header mb-4">
            <h3 class="mb-1"><?= $page_title ?? 'Dashboard Satpam' ?></h3>
            <p class="text-muted mb-0">
                Selamat datang, <strong><?= htmlspecialchars($sessionManager->get('nama')) ?></strong>
            </p>
        </div>