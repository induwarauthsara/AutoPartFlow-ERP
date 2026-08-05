<section class="page-header">
    <h1>Add New Part</h1>
    <p>Enter part details below.</p>
</section>

<section class="card card-narrow">
    <form method="POST" action="<?= url('parts/store') ?>" class="form">
        <?= csrf_field() ?>
        <?php require APP_PATH . '/Views/parts/_form.php'; ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Part</button>
            <a href="<?= url('parts') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>
