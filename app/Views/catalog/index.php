<?php
$filters = $filters ?? ['search' => '', 'categories' => [], 'brands' => [], 'sort' => 'relevance'];
$products = $products ?? [];
$categories = $categories ?? [];
$brands = $brands ?? [];
$vehicleBrands = $vehicleBrands ?? [];
$vehicleModels = $vehicleModels ?? [];
$vehicleEngines = $vehicleEngines ?? [];
$vehicleFilter = $vehicleFilter ?? ['brand_id' => 0, 'model_id' => 0, 'engine_id' => 0, 'year' => 0];
$vehicleSelectionTitle = $vehicleSelectionTitle ?? '';
$hasVehicleFilter = (bool) ($hasVehicleFilter ?? false);
$canManageCatalog = (bool) ($canManageCatalog ?? false);

// Base clear vehicle URL preserves category/brand/search/sort filters
$clearVehicleParams = [];
if (!empty($filters['search'])) $clearVehicleParams['q'] = $filters['search'];
if (!empty($filters['categories'])) $clearVehicleParams['category'] = $filters['categories'];
if (!empty($filters['brands'])) $clearVehicleParams['brand'] = $filters['brands'];
if (!empty($filters['sort']) && $filters['sort'] !== 'relevance') $clearVehicleParams['sort'] = $filters['sort'];
$clearVehicleUrl = url('catalog') . (!empty($clearVehicleParams) ? '?' . http_build_query($clearVehicleParams) : '');

// Reset filters URL preserves vehicle selection
$resetFiltersParams = [];
if (!empty($vehicleFilter['brand_id'])) $resetFiltersParams['vehicle_brand_id'] = $vehicleFilter['brand_id'];
if (!empty($vehicleFilter['model_id'])) $resetFiltersParams['vehicle_model_id'] = $vehicleFilter['model_id'];
if (!empty($vehicleFilter['engine_id'])) $resetFiltersParams['vehicle_engine_id'] = $vehicleFilter['engine_id'];
if (!empty($vehicleFilter['year'])) $resetFiltersParams['year'] = $vehicleFilter['year'];
$resetFiltersUrl = url('catalog') . (!empty($resetFiltersParams) ? '?' . http_build_query($resetFiltersParams) : '');
?>

<div class="catalog-page">
    <div class="catalog-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <strong>Parts Catalog & Spare Part Finder</strong>
        </div>

        <!-- Hero Banner -->
        <section class="catalog-hero">
            <div class="catalog-hero__content">
                <span class="catalog-hero-eyebrow" style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:800; letter-spacing:0.08em; background:rgba(255,255,255,0.15); padding:4px 10px; border-radius:6px; margin-bottom:10px;">
                    <span class="material-symbols-outlined" style="font-size:16px;">directions_car</span>
                    ALL-IN-ONE SPARE PART FINDER & CATALOG
                </span>
                <h2>Find the right spare parts for your vehicle</h2>
                <p>Browse our complete catalog or select your vehicle make, model, engine, and year for verified compatible parts.</p>
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
                <h1>Parts Catalog & Vehicle Finder</h1>
                <p>Search spare parts, check guaranteed vehicle fitment, and place orders directly in one place.</p>
            </div>
            <?php if ($canManageCatalog): ?>
            <button type="button" class="btn-primary" id="btn-add-part" style="padding:10px 18px; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
                <span>Add Spare Part</span>
            </button>
            <?php endif; ?>
        </section>

        <!-- Main Form Wrapping Vehicle Finder & Catalog Layout -->
        <form method="get" action="<?= url('catalog') ?>" id="catalog-filter-form">
            <!-- Vehicle Fitment Selector Card -->
            <section class="catalog-vehicle-card" id="vehicleFinderCard">
                <div class="vfinder-card-header">
                    <div class="vfinder-title-group">
                        <div class="vfinder-icon-wrap">
                            <span class="material-symbols-outlined">directions_car</span>
                        </div>
                        <div>
                            <h3>Select Your Vehicle</h3>
                            <p>Filter catalog items engineered for your car. Fill any details you know.</p>
                        </div>
                    </div>
                    <?php if ($hasVehicleFilter): ?>
                        <a href="<?= e($clearVehicleUrl) ?>" class="btn-clear-vehicle" title="Clear vehicle filter and view all parts">
                            <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                            <span>Clear Vehicle Filter</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="vfinder-grid">
                    <!-- Make / Vehicle Brand -->
                    <div class="vfinder-field">
                        <label for="v_brand_id">Vehicle Make</label>
                        <select id="v_brand_id" name="vehicle_brand_id" class="vfinder-select">
                            <option value="">All Makes / Any</option>
                            <?php foreach ($vehicleBrands as $vb): ?>
                                <option value="<?= (int) $vb['id'] ?>" <?= ($vehicleFilter['brand_id'] ?? 0) === (int) $vb['id'] ? 'selected' : '' ?>>
                                    <?= e($vb['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Model -->
                    <div class="vfinder-field">
                        <label for="v_model_id">Model</label>
                        <select id="v_model_id" name="vehicle_model_id" class="vfinder-select" <?= !empty($vehicleFilter['brand_id']) ? '' : 'disabled' ?>>
                            <option value="">All Models</option>
                            <?php foreach ($vehicleModels as $vm): ?>
                                <option value="<?= (int) $vm['id'] ?>" <?= ($vehicleFilter['model_id'] ?? 0) === (int) $vm['id'] ? 'selected' : '' ?>>
                                    <?= e($vm['name']) ?><?= !empty($vm['body_type']) ? ' (' . e($vm['body_type']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Engine -->
                    <div class="vfinder-field">
                        <label for="v_engine_id">Engine</label>
                        <select id="v_engine_id" name="vehicle_engine_id" class="vfinder-select" <?= !empty($vehicleFilter['model_id']) ? '' : 'disabled' ?>>
                            <option value="">All Engines</option>
                            <?php foreach ($vehicleEngines as $ve): ?>
                                <?php
                                    $engineMeta = array_filter([
                                        !empty($ve['displacement_cc']) ? $ve['displacement_cc'] . 'cc' : '',
                                        $ve['fuel_type'] ?? ''
                                    ]);
                                    $engineLabel = $ve['engine_code'] . (!empty($engineMeta) ? ' (' . implode(' · ', $engineMeta) . ')' : '');
                                ?>
                                <option value="<?= (int) $ve['id'] ?>" <?= ($vehicleFilter['engine_id'] ?? 0) === (int) $ve['id'] ? 'selected' : '' ?>>
                                    <?= e($engineLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Year -->
                    <div class="vfinder-field">
                        <label for="v_year">Year</label>
                        <input id="v_year" name="year" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>"
                            value="<?= !empty($vehicleFilter['year']) ? (int) $vehicleFilter['year'] : '' ?>"
                            placeholder="e.g. 2018" class="vfinder-input">
                    </div>

                    <!-- Action Button -->
                    <div class="vfinder-actions">
                        <button type="submit" class="btn-find-parts">
                            <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                            <span>Find Parts</span>
                        </button>
                    </div>
                </div>

                <?php if ($hasVehicleFilter && !empty($vehicleSelectionTitle)): ?>
                    <div class="vfinder-active-badge">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--success);">verified</span>
                        <span>Showing parts verified for: <strong><?= e($vehicleSelectionTitle) ?></strong></span>
                        <a href="<?= e($clearVehicleUrl) ?>" class="vfinder-badge-clear">Remove filter</a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Main Layout: Filters Sidebar + Results Grid -->
            <div class="catalog-layout">
                <!-- Filter Sidebar -->
                <aside class="filter-panel">
                    <div class="filter-panel__heading">
                        <h2>Filters</h2>
                        <a href="<?= e($resetFiltersUrl) ?>">Reset Filters</a>
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
                        <h3>Part Brands</h3>
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
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <strong><?= count($products) ?> Product<?= count($products) === 1 ? '' : 's' ?></strong>
                            <?php if ($hasVehicleFilter && !empty($vehicleSelectionTitle)): ?>
                                <span class="toolbar-vehicle-badge" style="font-size:12px; background:var(--surface-low); color:var(--primary); padding:3px 8px; border-radius:6px; font-weight:600;">for <em><?= e($vehicleSelectionTitle) ?></em></span>
                            <?php endif; ?>
                        </div>
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
                                $specifications = is_string($product['specifications'] ?? null)
                                    ? (json_decode($product['specifications'], true) ?: [])
                                    : ($product['specifications'] ?? []);
                                $specifications = is_array($specifications) ? $specifications : [];
                                $oemReference = (string) ($specifications['OEM Reference'] ?? '');
                                $oemSpecifications = (string) ($specifications['OEM Specifications'] ?? '');
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

                                <?php if ($hasVehicleFilter): ?>
                                    <div class="product-fit-badge">
                                        <span class="material-symbols-outlined" style="font-size:15px;">verified</span>
                                        <span>Guaranteed Fit</span>
                                    </div>
                                    <?php if (!empty($product['compatibility_notes'])): ?>
                                        <div class="product-fit-note" title="<?= e($product['compatibility_notes']) ?>">
                                            <span class="material-symbols-outlined" style="font-size:13px;">info</span>
                                            <span><?= e($product['compatibility_notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

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
                                <?php if ($canManageCatalog): ?>
                                <div class="product-admin-actions" style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; padding-top:8px; border-top:1px dashed #cbd5e1; font-size:12px;">
                                    <button type="button" class="btn-edit-part" style="background:none; border:none; color:var(--primary); font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px; padding:4px 0;"
                                        data-id="<?= e((string) $product['id']) ?>"
                                        data-code="<?= e($product['product_code']) ?>"
                                        data-name="<?= e($product['name']) ?>"
                                        data-barcode="<?= e($product['barcode'] ?? '') ?>"
                                        data-category-id="<?= e((string) $product['category_id']) ?>"
                                        data-brand-id="<?= e((string) ($product['brand_id'] ?? '')) ?>"
                                        data-cost-price="<?= e((string) ($product['cost_price'] ?? '0')) ?>"
                                        data-selling-price="<?= e((string) $product['selling_price']) ?>"
                                        data-wholesale-price="<?= e((string) ($product['wholesale_price'] ?? '')) ?>"
                                        data-oem-reference="<?= e($oemReference) ?>"
                                        data-oem-specifications="<?= e($oemSpecifications) ?>"
                                        data-reorder="<?= e((string) ($product['reorder_level'] ?? '5')) ?>"
                                        data-image="<?= e($product['image_path'] ?? '') ?>"
                                        data-desc="<?= e($product['description'] ?? '') ?>">
                                        <span class="material-symbols-outlined" style="font-size:16px;">edit</span> Edit
                                    </button>
                                    <button type="button" class="btn-delete-part" style="background:none; border:none; color:#dc2626; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px; padding:4px 0;"
                                        data-id="<?= e((string) $product['id']) ?>"
                                        data-name="<?= e($product['name']) ?>"
                                        data-code="<?= e($product['product_code']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:16px;">archive</span> Archive
                                    </button>
                                </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            </div>
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
<?php if ($canManageCatalog): ?>
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

                <!-- Row 4: Initial Stock, threshold and OEM reference -->
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px;">
                    <div id="part-initial-stock-group">
                        <label for="part-initial-stock" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Initial Stock Quantity</label>
                        <input type="number" min="0" id="part-initial-stock" name="initial_stock" value="10" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-reorder-level" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Low Stock Alert Threshold</label>
                        <input type="number" min="0" id="part-reorder-level" name="reorder_level" value="5" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="part-oem-reference" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">OEM Reference Number</label>
                        <input type="text" id="part-oem-reference" name="oem_reference" maxlength="100" placeholder="e.g. 04465-0D150" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box;">
                    </div>
                </div>

                <div>
                    <label for="part-oem-specifications" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">OEM Specifications</label>
                    <textarea id="part-oem-specifications" name="oem_specifications" rows="2" maxlength="1000" placeholder="OEM dimensions, material, rating, interchange codes, or installation notes" style="width:100%; padding:9px 12px; border:1px solid var(--outline-variant); border-radius:6px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
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
<?php endif; ?>

<!-- Toast notification container -->
<div id="catalog-toast" class="toast-container" aria-live="polite"></div>
