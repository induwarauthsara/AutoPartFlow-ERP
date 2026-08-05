<section class="page-header page-header-row">
    <div>
        <h1>Parts Inventory</h1>
        <p>Manage auto parts stock and pricing.</p>
    </div>
    <a href="<?= url('parts/create') ?>" class="btn btn-primary">Add Part</a>
</section>

<section class="card">
    <?php if (empty($parts)): ?>
        <p class="empty-state">No parts found. <a href="<?= url('parts/create') ?>">Add your first part</a>.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parts as $part): ?>
                        <tr>
                            <td><?= e($part['product_code']) ?></td>
                            <td><?= e($part['name']) ?></td>
                            <td><?= e($part['category_name']) ?></td>
                            <td>Rs. <?= e(number_format((float) $part['selling_price'], 2)) ?></td>
                            <td><?= (int) $part['quantity_on_hand'] ?></td>
                            <td class="actions-cell">
                                <a href="<?= url('parts/edit/' . $part['id']) ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="<?= url('parts/delete/' . $part['id']) ?>" class="inline-form" data-confirm="Delete this part?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
