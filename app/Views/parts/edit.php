<section class="page-header">
    <h1>Edit Part</h1>
    <p>Update part details below.</p>
</section>

<section class="card card-narrow">
    <form method="POST" action="<?= url('parts/update/' . $part['id']) ?>" class="form">
        <?= csrf_field() ?>
        <?php require APP_PATH . '/Views/parts/_form.php'; ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Part</button>
            <a href="<?= url('parts') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>
