<!-- views/register.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Lost & Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/login.css" rel="stylesheet">
    <style>
        .form-section {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .required { color: #dc3545; }
        .text-muted { font-size: 0.9em; }
        .email-hint { font-size: 0.85em; color: #6c757d; margin-top: 0.25rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-section">
            <div class="text-center mb-4">
                <img src="../images/logo.png" alt="Logo" width="80" onerror="this.src='https://via.placeholder.com/80';">
                <h4 class="mt-3">Buat Akun Baru</h4>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="../index.php?action=register" id="registerForm">
                <!-- Role -->
                <div class="mb-3">
                    <label class="form-label">Pilih Role <span class="required">*</span></label>
                    <select class="form-select" name="role" id="role" required onchange="updateEmailHint(); toggleNimField();">
                        <option value="">-- Pilih Role --</option>
                        <option value="civitas">Civitas (Mahasiswa/Dosen)</option>
                        <option value="satpam">Satpam</option>
                    </select>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label" id="emailLabel">Email <span class="required">*</span></label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="contoh: nama@student.ub.ac.id" required>
                    <div class="email-hint" id="emailHint">
                        Gunakan email UB (@ub.ac.id atau @student.ub.ac.id)
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input type="password" class="form-control" name="password" required minlength="6">
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>

                <!-- Nama -->
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" class="form-control" name="nama" required>
                </div>

                <!-- Nomor Kontak -->
                <div class="mb-3">
                    <label class="form-label">Nomor Kontak <span class="required">*</span></label>
                    <input type="text" class="form-control" name="nomor_kontak" placeholder="081234567890" required>
                </div>

                <!-- NIM/NIP (Hanya Civitas) -->
                <div class="mb-3" id="nim_field" style="display: none;">
                    <label class="form-label">NIM / NIP <span class="required">*</span></label>
                    <input type="text" class="form-control" name="nomor_induk" placeholder="205150401111005">
                </div>

                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>

            <p class="text-center mt-3 text-muted">
                Sudah punya akun? <a href="../index.php?action=login">Login di sini</a>
            </p>
        </div>
    </div>

    <script>
        function updateEmailHint() {
            const role = document.getElementById('role').value;
            const hint = document.getElementById('emailHint');
            const label = document.getElementById('emailLabel');

            if (role === 'civitas') {
                hint.innerHTML = '<strong>Wajib</strong> gunakan email UB (@ub.ac.id atau @student.ub.ac.id)';
                label.innerHTML = 'Email UB <span class="required">*</span>';
            } else if (role === 'satpam') {
                hint.innerHTML = 'Email bebas (contoh: satpam@gmail.com)';
                label.innerHTML = 'Email <span class="required">*</span>';
            } else {
                hint.innerHTML = 'Pilih role terlebih dahulu';
                label.innerHTML = 'Email <span class="required">*</span>';
            }
        }

        function toggleNimField() {
            const role = document.getElementById('role').value;
            const field = document.getElementById('nim_field');
            const input = field.querySelector('input');
            if (role === 'civitas') {
                field.style.display = 'block';
                input.setAttribute('required', 'required');
            } else {
                field.style.display = 'none';
                input.removeAttribute('required');
            }
        }

        // Jalankan saat halaman dimuat
        window.onload = function() {
            updateEmailHint();
            toggleNimField();
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>