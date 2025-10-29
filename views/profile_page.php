<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profil - Lost and Found FILKOM UB">
    <title>Profil - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/profile.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="profile-container">
        <h2 class="text-center">Profil</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="../index.php?action=update_profile">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($profil['email']); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($profil['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nomorKontak" class="form-label">Nomor Kontak</label>
                <input type="text" class="form-control" id="nomorKontak" name="nomorKontak" value="<?php echo htmlspecialchars($profil['nomor_kontak']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nomorInduk" class="form-label">Nomor Induk (NIM)</label>
                <input type="text" class="form-control" id="nomorInduk" value="<?php echo htmlspecialchars($profil['nomor_induk']); ?>" disabled>
            </div>
            <button type="submit" class="btn btn-primary w-100" aria-label="Simpan Perubahan">Simpan Perubahan</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>