<div class="form-group">
    <label for="product_code">Product Code</label>
    <input type="text" id="product_code" name="product_code" value="<?= e($part['product_code'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= e($part['name'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
        <option value="">Select a category</option>
        <?php foreach ($categories as $category): ?>
            <option
                value="<?= (int) $category['id'] ?>"
                <?= ((int) ($part['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : '' ?>
            >
                <?= e($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="selling_price">Selling Price (Rs.)</label>
        <input
            type="number"
            id="selling_price"
            name="selling_price"
            step="0.01"
            min="0"
            value="<?= e($part['selling_price'] ?? '') ?>"
            required
        >
    </div>

    <div class="form-group">
        <label for="quantity_on_hand">Quantity</label>
        <input
            type="number"
            id="quantity_on_hand"
            name="quantity_on_hand"
            min="0"
            value="<?= e((string) ($part['quantity_on_hand'] ?? '0')) ?>"
            required
        >
    </div>
</div>
