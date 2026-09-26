<?php
$filters = $filters ?? ['search' => '', 'categories' => [], 'brands' => [], 'sort' => 'relevance'];
$products = $products ?? [];
$categories = $categories ?? [];
$brands = $brands ?? [];
$vehicleBrands = $vehicleBrands ?? [];
?>

<div class="catalog-page">
    <div class="catalog-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <strong>Product Catalog</strong>
        </div>

        <!-- Hero Banner -->
        <section class="catalog-hero">
            <div class="catalog-hero__content">
                <h2>Parts for your next repair</h2>
                <p>Browse by category or brand, then check compatibility in the part details. Have an account? Sign in below.</p>
            </div>
            <?php if (auth_check()): ?>
                <a class="hero-register" href="<?= auth_dashboard_url() ?>"><?= e(auth_dashboard_label()) ?></a>
            <?php else: ?>
                <a class="hero-register" href="<?= url('login') ?>">Sign In</a>
            <?php endif; ?>
            <span class="material-symbols-outlined catalog-hero__watermark">directions_car</span>
        </section>

        <!-- Page Header -->
        <section class="catalog-heading" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h1>Product Catalog</h1>
                <p>Browse auto parts, check vehicle compatibility, and manage inventory catalog.</p>
            </div>
            <?php if (auth_check() && in_array(auth_role(), ['store_manager', 'owner'], true)): ?>
            <button type="button" class="btn-primary" id="btn-add-part" style="padding:10px 18px; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
                <span>Add Spare Part</span>
            </button>
            <?php endif; ?>
        </section>

        <!-- Main Layout: Filters Sidebar + Results Grid -->
        <form class="catalog-layout" method="get" action="<?= url('catalog') ?>" id="catalog-filter-form">
            <!-- Filter Sidebar -->
            <aside class="filter-panel">
                <div class="filter-panel__heading">
                    <h2>Filters</h2>
                    <a href="<?= url('catalog') ?>">Clear All</a>
                </div>

                <!-- Categories Filter -->
                <div class="filter-group">
                    <h3>Categories</h3>
                    <div class="filter-options">
                        <?php foreach ($categories as $category): ?>
                            <?php $name = (string) $category['name']; ?>
                            <label class="filter-option">
                                <input type="checkbox" name="category[]" value="<?= e($name) ?>" <?= in_array($name, $filters['categories'], true) ? 'checked' : '' ?>>
                                <span><?= e($name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Brands Filter -->
                <div class="filter-group filter-group--bordered">
                    <h3>Brands</h3>
                    <div class="filter-options filter-options--scroll">
                        <?php foreach ($brands as $brand): ?>
                            <?php $name = (string) $brand['name']; ?>
                            <label class="filter-option">
                                <input type="checkbox" name="brand[]" value="<?= e($name) ?>" <?= in_array($name, $filters['brands'], true) ? 'checked' : '' ?>>
                                <span><?= e($name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <input type="hidden" name="q" value="<?= e($filters['search']) ?>">
                <input type="hidden" name="sort" id="hidden-sort" value="<?= e($filters['sort']) ?>">
                <button class="apply-filter-button" type="submit">Apply Filters</button>
            </aside>

            <!-- Product Grid Area -->
            <section class="catalog-results">
                <!-- Toolbar -->
                <div class="results-toolbar">
                    <strong><?= count($products) ?> Products</strong>
                    <label class="sort-control">
                        <span>Sort by:</span>
                        <select id="sort-select">
                            <option value="relevance" <?= $filters['sort'] === 'relevance' ? 'selected' : '' ?>>Relevance</option>
                            <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                        </select>
                    </label>
                </div>

                <?php if (!$products): ?>
                    <div class="empty-products">
                        <span class="material-symbols-outlined">search_off</span>
                        <h3>No products found</h3>
                        <p>Try changing your search query or filter selections.</p>
                        <a href="<?= url('catalog') ?>">Clear all filters</a>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <?php
                                $status = $product['stock_status'];
                                $statusClass = strtolower(str_replace(' ', '-', $status));
                            ?>
                            <article class="product-card">
                                <div class="product-image-box">
                                    <?php if (!empty($product['display_image'])): ?>
                                        <img src="<?= e($product['display_image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="material-symbols-outlined product-placeholder">build</span>
                                    <?php endif; ?>
                                    <span class="stock-badge stock-badge--<?= e($statusClass) ?>">
                                        <span></span><?= e($status) ?>
                                    </span>
                                </div>

                                <div class="product-meta">
                                    <span><?= e($product['category']) ?></span>
                                    <strong><?= e($product['brand'] ?? '—') ?></strong>
                                </div>
                                <h3><?= e($product['name']) ?></h3>
                                <code><?= e($product['product_code']) ?></code>

                                <div class="product-price">
                                    <span>Retail Price</span>
                                    <strong>Rs. <?= number_format((float) $product['selling_price'], 0) ?></strong>
                                    <small>Sign in for wholesale pricing</small>
                                </div>

                                <a class="compatibility-link" href="#" data-id="<?= e((string) $product['id']) ?>" data-code="<?= e($product['product_code']) ?>" data-name="<?= e($product['name']) ?>">
                                    <span class="material-symbols-outlined">directions_car</span>
                                    Check compatibility
                                </a>

                                <div class="product-actions">
                                    <button type="button" class="details-button" data-product="<?= e($product['product_code']) ?>">Details</button>
                                    <button type="button" class="add-button" data-product="<?= e($product['product_code']) ?>" data-name="<?= e($product['name']) ?>" data-price="<?= e((string) $product['selling_price']) ?>">
                                        <span class="material-symbols-outlined">add_shopping_cart</span> Add
                                    </button>
                                </div>
                                <div class="product-admin-actions" style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; padding-top:8px; border-top:1px dashed #cbd5e1; font-size:12px;">
                                    <button type="button" class="btn-edit-part" style="background:none; border:none; color:var(--primary); font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px; padding:4px 0;"
                                        data-id="<?= e((string) $product['id']) ?>"
                                        data-code="<?= e($product['product_code']) ?>"
                                        data-name="<?= e($product['name']) ?>"
                                        data-category-id="<?= e((string) $product['category_id']) ?>"
                                        data-brand-id="<?= e((string) ($product['brand_id'] ?? '')) ?>"
                                        data-cost-price="<?= e((string) ($product['cost_price'] ?? '0')) ?>"
                                        data-selling-price="<?= e((string) $product['selling_price']) ?>"
                                        data-wholesale-price="<?= e((string) ($product['wholesale_price'] ?? '')) ?>"
                                        data-warranty="<?= e((string) ($product['warranty_months'] ?? '12')) ?>"
                                        data-reorder="<?= e((string) ($product['reorder_level'] ?? '5')) ?>"
                                        data-image="<?= e($product['image_path'] ?? '') ?>"
                                        data-desc="<?= e($product['description'] ?? '') ?>">
                                        <span class="material-symbols-outlined" style="font-size:16px;">edit</span> Edit
                                    </button>
                                    <button type="button" class="btn-delete-part" style="background:none; border:none; color:#dc2626; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px; padding:4px 0;"
                                        data-id="<?= e((string) $product['id']) ?>"
                                        data-name="<?= e($product['name']) ?>"
                                        data-code="<?= e($product['product_code']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Remove
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </form>
    </div>
</div>

<!-- Vehicle Compatibility Modal (Pure HTML/CSS/JS) -->
<div class="modal-overlay" id="compatibility-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-header__title">
                <span class="material-symbols-outlined">directions_car</span>
                <div>
                    <h3 id="compat-product-name">Vehicle Compatibility</h3>
                    <span class="modal-subtitle" id="compat-product-code"></span>
                </div>
            </div>
            <button type="button" class="modal-close" id="compat-modal-close" aria-label="Close modal">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-body" id="compat-modal-content">
            <div class="modal-loading">
                <span class="material-symbols-outlined spin">sync</span>
                <p>Loading vehicle compatibility...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="compat-modal-done">Close</button>
        </div>
    </div>
</div>

<!-- Product Details Modal (Pure HTML/CSS/JS) -->
<div class="modal-overlay" id="details-modal" aria-hidden="true">
    <div class="modal-card modal-card--lg">
        <div class="modal-header">
            <div class="modal-header__title">
                <span class="material-symbols-outlined">info</span>
                <div>
                    <h3 id="details-product-name">Product Details</h3>
                    <span class="modal-subtitle" id="details-product-code"></span>
                </div>
            </div>
            <button type="button" class="modal-close" id="details-modal-close" aria-label="Close modal">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-body" id="details-modal-content">
            <div class="modal-loading">
                <span class="material-symbols-outlined spin">sync</span>
                <p>Loading product details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="details-modal-done">Close</button>
            <button type="button" class="btn-primary" id="details-modal-add">
                <span class="material-symbols-outlined">add_shopping_cart</span> Add to Cart
            </button>
        </div>
    </div>
</div>

<!-- Add / Edit Spare Part Modal -->
<div class="modal-overlay" id="part-modal" aria-hidden="true">
    <div class="modal-card modal-card--lg" style="max-height:90vh; display:flex; flex-direction:column;">
        <div class="modal-header">
            <div class="modal-header__title">
                <span class="material-symbols-outlined">build_circle</span>
                <div>
                    <h3 id="part-modal-title">Add New Spare Part</h3>
                    <span class="modal-subtitle" id="part-modal-subtitle">Catalog & Vehicle Fitment Entry</span>
                </div>
            </div>
            <button type="button" class="modal-close" id="part-modal-close" aria-label="Close modal">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="part-modal-form" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            <input type="hidden" id="part-id" name="id" value="">
            <div class="modal-body" style="overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:16px;">
                <!-- Row 1: Name & Barcode -->
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px;">
                    <div>
                        <label for="part-name" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Part Name *</label>
                        <input type="text" id="part-name" name="name" required placeholder="e.g. Front Ceramic Brake Pad Set" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-barcode" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Barcode (UPC/EAN)</label>
                        <input type="text" id="part-barcode" name="barcode" placeholder="e.g. 78912345678" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                </div>

                <!-- Row 2: Category & Brand -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label for="part-category" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Category *</label>
                        <select id="part-category" name="category_id" required style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e((string) $cat['id']) ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="part-brand" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Brand / Manufacturer</label>
                        <select id="part-brand" name="brand_id" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                            <option value="">-- Select Brand --</option>
                            <?php foreach ($brands as $br): ?>
                                <option value="<?= e((string) $br['id']) ?>"><?= e($br['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 3: Prices -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div>
                        <label for="part-selling-price" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Retail Price (Rs.) *</label>
                        <input type="number" step="0.01" min="0" id="part-selling-price" name="selling_price" required placeholder="0.00" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-cost-price" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Cost Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" id="part-cost-price" name="cost_price" placeholder="0.00" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-wholesale-price" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Wholesale Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" id="part-wholesale-price" name="wholesale_price" placeholder="0.00" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                </div>

                <!-- Row 4: Initial Stock & Warranty -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div id="part-initial-stock-group">
                        <label for="part-initial-stock" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Initial Stock Quantity</label>
                        <input type="number" min="0" id="part-initial-stock" name="initial_stock" value="10" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-reorder-level" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Reorder Alert Level</label>
                        <input type="number" min="0" id="part-reorder-level" name="reorder_level" value="5" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-warranty" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Warranty (Months)</label>
                        <input type="number" min="0" id="part-warranty" name="warranty_months" value="12" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                </div>

                <!-- Image URL -->
                <div>
                    <label for="part-image" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Image URL (Optional)</label>
                    <input type="url" id="part-image" name="image_path" placeholder="https://example.com/images/part.jpg" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                </div>

                <!-- Vehicle Fitment Mapping Section -->
                <div id="compat-mapping-section" style="border:1px solid #cbd5e1; background:var(--surface-low); border-radius:8px; padding:14px;">
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:10px;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">directions_car</span>
                        <strong style="font-size:13px;">Vehicle Fitment Specification</strong>
                        <span style="font-size:11px; color:var(--on-surface-variant);">(Optional / Multi-Model Fitment)</span>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div>
                            <label for="part-vehicle-brand" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Vehicle Brand</label>
                            <select id="part-vehicle-brand" name="vehicle_brand_id" style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                                <option value="">-- Universal Fit / None --</option>
                                <?php foreach ($vehicleBrands as $vb): ?>
                                    <option value="<?= e((string) $vb['id']) ?>"><?= e($vb['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="part-vehicle-model" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Vehicle Model</label>
                            <select id="part-vehicle-model" name="vehicle_model_id" disabled style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                                <option value="">-- All Models --</option>
                            </select>
                        </div>
                        <div>
                            <label for="part-vehicle-engine" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Engine Code</label>
                            <select id="part-vehicle-engine" name="vehicle_engine_id" disabled style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                                <option value="">-- All Engines --</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:10px; margin-top:8px;">
                        <div>
                            <label for="part-year-from" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Year From</label>
                            <input type="number" id="part-year-from" name="year_from" placeholder="2015" min="1970" max="2035" style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label for="part-year-to" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Year To</label>
                            <input type="number" id="part-year-to" name="year_to" placeholder="2024" min="1970" max="2035" style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label for="part-compat-notes" style="display:block; font-size:11px; font-weight:600; margin-bottom:2px;">Fitment Notes</label>
                            <input type="text" id="part-compat-notes" name="compat_notes" placeholder="e.g. Front axle only / Non-turbo" style="width:100%; padding:7px 10px; border:1px solid var(--outline-variant); border-radius:6px; font-size:12px; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="part-description" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Description / Technical Specs</label>
                    <textarea id="part-description" name="description" rows="3" placeholder="Technical specifications, OEM reference, and fitment details..." style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="part-modal-cancel">Cancel</button>
                <button type="submit" class="btn-primary" id="part-modal-save">
                    <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                    <span>Save Spare Part</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast notification container -->
<div id="catalog-toast" class="toast-container" aria-live="polite"></div>
