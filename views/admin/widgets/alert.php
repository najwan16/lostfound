<!-- admin/widgets/alert.php -->
<?php if (!empty($alert_message)): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
        <?= htmlspecialchars($alert_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>