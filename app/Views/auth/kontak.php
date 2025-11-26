<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Nomor Telepon - SIPAN SMAPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="public/assets/css/login.css" rel="stylesheet">

</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="public/assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/200';">
        </div>
        <div class="form-section">
            <h2 class="text-center">Isi nomor telepon</h2>
            <p class="text-center text-muted mb-4">
                Akun kamu belum ada nomor teleponnya
            </p>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=kontak">
                <div class="mb-3">
                    <label for="nomor_kontak" class="form-label">Nomor Telepon (WhatsApp)</label>
                    <div class="input-group">
                        <span class="input-group-text">+62</span>
                        <input type="text" class="form-control" id="nomor_kontak" name="nomor_kontak"
                            placeholder="81234567890" inputmode="numeric" required
                            value="<?= htmlspecialchars($_POST['nomor_kontak'] ?? '') ?>">
                    </div>

                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('nomor_kontak').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // hanya angka
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            if (value && !value.startsWith('62')) {
                value = value;
            }
            e.target.value = value;
        });
    </script>
</body>

</html>