<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke Lost and Found FILKOM UB">
    <title>Login - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet"> <!-- Tambah ini -->
    <link href="../css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="../images/logo.png" alt="FILKOM UB Logo" onerror="this.src='https://via.placeholder.com/200';">
        </div>
        <div class="form-section">
            <h2 class="text-center">Masuk</h2>
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="index.php?action=login">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3 form-check">
                    <!-- <input type="checkbox" class="form-check-input" id="remember" name="remember"> -->
                    <!-- <label class="form-check-label" for="remember">Ingat Aku</label> -->
                </div>
                <button type="submit" class="btn btn-primary w-100" aria-label="Login">Login</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>