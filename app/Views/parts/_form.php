<div class="form-group">
    <label for="part_number">Part Number</label>
    <input type="text" id="part_number" name="part_number" value="<?= e($part['part_number'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= e($part['name'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label for="category">Category</label>
    <input type="text" id="category" name="category" value="<?= e($part['category'] ?? '') ?>" required>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($part['price'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity" min="0" value="<?= e((string) ($part['quantity'] ?? '')) ?>" required>
    </div>
</div>
