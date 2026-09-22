<?php
$filters = $filters ?? ['search' => '', 'categories' => [], 'brands' => [], 'sort' => 'relevance'];
$products = $products ?? [];
$categories = $categories ?? [];
$brands = $brands ?? [];
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
                <h2>Welcome to AutoPartFlow</h2>
                <p>Find the best genuine and aftermarket spare parts for your vehicle. Register for an account to unlock exclusive wholesale pricing and track your orders easily.</p>
            </div>
            <a class="hero-register" href="<?= url('login') ?>">Register Now</a>
            <span class="material-symbols-outlined catalog-hero__watermark">directions_car</span>
        </section>

        <!-- Page Header -->
        <section class="catalog-heading">
            <div>
                <h1>Product Catalog</h1>
                <p>Browse our extensive collection of auto parts.</p>
            </div>
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

<!-- Toast notification container -->
<div id="catalog-toast" class="toast-container" aria-live="polite"></div>
