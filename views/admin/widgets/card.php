<!-- admin/widgets/card.php -->
<div class="card shadow-sm border-0 mb-4">
    <?php if (!empty($card_header)): ?>
        <div class="card-header <?= $header_class ?? 'bg-gradient-primary' ?> text-white">
            <h5 class="mb-0"><?= $card_header ?></h5>
        </div>
    <?php endif; ?>
    <div class="card-body <?= $body_class ?? '' ?>">
        <?= $card_content ?? '' ?>
    </div>
</div>