document.addEventListener('DOMContentLoaded', function () {
    // 1. Sort & Filter Handling
    const form = document.getElementById('catalog-filter-form');
    const sortSelect = document.getElementById('sort-select');
    const hiddenSort = document.getElementById('hidden-sort');

    if (form && sortSelect && hiddenSort) {
        sortSelect.addEventListener('change', function () {
            hiddenSort.value = sortSelect.value;
            form.submit();
        });
    }

    // 2. Shopping Cart Count & Badge State
    const cartCountBadge = document.querySelector('.cart-count');
    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('autopartflow_cart') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('autopartflow_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        if (!cartCountBadge) return;
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + (item.qty || 1), 0);
        cartCountBadge.textContent = totalItems;
    }

    updateCartBadge();

    // 3. Toast Notifications
    const toastContainer = document.getElementById('catalog-toast');
    function showToast(message, type = 'success') {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.innerHTML = `
            <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'info'}</span>
            <span>${message}</span>
        `;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    // 4. Add to Cart Button Handlers
    document.querySelectorAll('.add-button').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const code = button.dataset.product || '';
            const name = button.dataset.name || code;
            const price = parseFloat(button.dataset.price || '0');

            const cart = getCart();
            const existing = cart.find(item => item.code === code);
            if (existing) {
                existing.qty = (existing.qty || 1) + 1;
            } else {
                cart.push({ code, name, price, qty: 1 });
            }
            saveCart(cart);

            const originalHTML = button.innerHTML;
            button.innerHTML = '<span class="material-symbols-outlined">check</span> Added';
            button.disabled = true;
            showToast(`Added <strong>${name}</strong> to cart!`);

            setTimeout(function () {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 1200);
        });
    });

    // 5. Vehicle Compatibility Modal
    const compatModal = document.getElementById('compatibility-modal');
    const compatCloseBtn = document.getElementById('compat-modal-close');
    const compatDoneBtn = document.getElementById('compat-modal-done');
    const compatTitle = document.getElementById('compat-product-name');
    const compatCode = document.getElementById('compat-product-code');
    const compatContent = document.getElementById('compat-modal-content');

    function openCompatModal(productId, productCode, productName) {
        if (!compatModal) return;
        compatModal.classList.add('active');
        compatModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        compatTitle.textContent = productName || 'Vehicle Compatibility';
        compatCode.textContent = productCode || '';
        compatContent.innerHTML = `
            <div class="modal-loading">
                <span class="material-symbols-outlined spin">sync</span>
                <p>Loading vehicle compatibility details...</p>
            </div>
        `;

        const baseUrl = window.APP_CONFIG?.baseUrl || '';
        fetch(`${baseUrl}/catalog/compatibility?product_id=${encodeURIComponent(productId)}&product_code=${encodeURIComponent(productCode)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.compatibility && res.compatibility.length > 0) {
                    let html = `
                        <p style="margin-top:0; color:var(--on-surface-variant);">Verified compatible vehicle models and engine specifications:</p>
                        <table class="compat-table">
                            <thead>
                                <tr>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Engine</th>
                                    <th>Fuel / Trans</th>
                                    <th>Year Range</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    res.compatibility.forEach(c => {
                        const yearRange = c.year_from ? `${c.year_from} - ${c.year_to || 'Present'}` : 'Universal';
                        const fuelTrans = [c.fuel_type, c.transmission].filter(Boolean).join(' / ') || '—';
                        html += `
                            <tr>
                                <td><strong>${c.vehicle_brand || 'All Brands'}</strong></td>
                                <td>${c.vehicle_model || 'All Models'}</td>
                                <td><span class="compat-badge">${c.engine_code || 'All Engines'}</span></td>
                                <td>${fuelTrans}</td>
                                <td>${yearRange}</td>
                                <td><small style="color:var(--on-surface-variant);">${c.notes || 'Direct fit'}</small></td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table>`;
                    compatContent.innerHTML = html;
                } else {
                    compatContent.innerHTML = `
                        <div style="text-align:center; padding: 24px 0;">
                            <span class="material-symbols-outlined" style="font-size:40px; color:var(--secondary); margin-bottom:8px;">info</span>
                            <p style="margin:0; font-weight:600;">Universal Fit / Standard Specification</p>
                            <p style="margin:4px 0 0; color:var(--on-surface-variant); font-size:13px;">This part fits universal specifications or multiple vehicles without specific engine restrictions.</p>
                        </div>
                    `;
                }
            })
            .catch(() => {
                compatContent.innerHTML = `
                    <div style="text-align:center; padding: 20px; color:var(--error);">
                        <span class="material-symbols-outlined">error</span>
                        <p>Unable to load compatibility info at this time.</p>
                    </div>
                `;
            });
    }

    function closeCompatModal() {
        if (!compatModal) return;
        compatModal.classList.remove('active');
        compatModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.compatibility-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            openCompatModal(this.dataset.id, this.dataset.code, this.dataset.name);
        });
    });

    if (compatCloseBtn) compatCloseBtn.addEventListener('click', closeCompatModal);
    if (compatDoneBtn) compatDoneBtn.addEventListener('click', closeCompatModal);
    if (compatModal) {
        compatModal.addEventListener('click', function (e) {
            if (e.target === compatModal) closeCompatModal();
        });
    }

    // 6. Product Details Modal
    const detailsModal = document.getElementById('details-modal');
    const detailsCloseBtn = document.getElementById('details-modal-close');
    const detailsDoneBtn = document.getElementById('details-modal-done');
    const detailsAddBtn = document.getElementById('details-modal-add');
    const detailsTitle = document.getElementById('details-product-name');
    const detailsCode = document.getElementById('details-product-code');
    const detailsContent = document.getElementById('details-modal-content');
    let currentDetailProduct = null;

    function openDetailsModal(code) {
        if (!detailsModal) return;
        detailsModal.classList.add('active');
        detailsModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        detailsTitle.textContent = 'Product Details';
        detailsCode.textContent = code;
        detailsContent.innerHTML = `
            <div class="modal-loading">
                <span class="material-symbols-outlined spin">sync</span>
                <p>Loading product details...</p>
            </div>
        `;

        const baseUrl = window.APP_CONFIG?.baseUrl || '';
        fetch(`${baseUrl}/catalog/details?code=${encodeURIComponent(code)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.product) {
                    const p = res.product;
                    currentDetailProduct = p;
                    detailsTitle.textContent = p.name;
                    detailsCode.textContent = `${p.product_code} | Barcode: ${p.barcode || '—'}`;

                    let specsHtml = '';
                    if (p.specifications) {
                        try {
                            const specsObj = typeof p.specifications === 'string' ? JSON.parse(p.specifications) : p.specifications;
                            specsHtml = Object.entries(specsObj).map(([k, v]) => `<div><strong>${k}:</strong> ${v}</div>`).join('');
                        } catch (e) {
                            specsHtml = p.specifications;
                        }
                    }

                    const stockBadgeClass = p.stock_status.toLowerCase().replace(/\s+/g, '-');

                    detailsContent.innerHTML = `
                        <div class="product-detail-layout">
                            <div class="product-detail-img">
                                <img src="${p.display_image || 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=400&q=80'}" alt="${p.name}">
                            </div>
                            <div class="product-detail-info">
                                <div class="detail-row">
                                    <span class="detail-label">Category</span>
                                    <span class="detail-val">${p.category || '—'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Brand / Manufacturer</span>
                                    <span class="detail-val">${p.brand || '—'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Retail Price</span>
                                    <span class="detail-val" style="color:var(--primary); font-size:16px;">Rs. ${Number(p.selling_price).toLocaleString()}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Stock Status</span>
                                    <span class="detail-val">
                                        <span class="stock-badge stock-badge--${stockBadgeClass}" style="position:static;">
                                            <span></span>${p.stock_status} (${p.quantity_on_hand} available)
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Warranty</span>
                                    <span class="detail-val">${p.warranty_months > 0 ? p.warranty_months + ' Months' : 'Standard'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Unit Type</span>
                                    <span class="detail-val">${p.unit || 'pcs'}</span>
                                </div>
                                ${p.description ? `
                                <div style="margin-top:8px;">
                                    <span class="detail-label" style="display:block; margin-bottom:4px;">Description:</span>
                                    <p style="margin:0; font-size:13px; color:var(--on-surface-variant); line-height:1.4;">${p.description}</p>
                                </div>` : ''}
                                ${specsHtml ? `
                                <div style="margin-top:6px;">
                                    <span class="detail-label" style="display:block; margin-bottom:4px;">Specifications:</span>
                                    <div style="font-size:12px; color:var(--on-surface-variant);">${specsHtml}</div>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
                } else {
                    detailsContent.innerHTML = `<p style="color:var(--error);">Product details not found.</p>`;
                }
            })
            .catch(() => {
                detailsContent.innerHTML = `<p style="color:var(--error);">Error loading product details.</p>`;
            });
    }

    function closeDetailsModal() {
        if (!detailsModal) return;
        detailsModal.classList.remove('active');
        detailsModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        currentDetailProduct = null;
    }

    document.querySelectorAll('.details-button').forEach(btn => {
        btn.addEventListener('click', function () {
            openDetailsModal(this.dataset.product);
        });
    });

    if (detailsCloseBtn) detailsCloseBtn.addEventListener('click', closeDetailsModal);
    if (detailsDoneBtn) detailsDoneBtn.addEventListener('click', closeDetailsModal);
    if (detailsModal) {
        detailsModal.addEventListener('click', function (e) {
            if (e.target === detailsModal) closeDetailsModal();
        });
    }

    if (detailsAddBtn) {
        detailsAddBtn.addEventListener('click', function () {
            if (currentDetailProduct) {
                const cart = getCart();
                const existing = cart.find(item => item.code === currentDetailProduct.product_code);
                if (existing) {
                    existing.qty = (existing.qty || 1) + 1;
                } else {
                    cart.push({
                        code: currentDetailProduct.product_code,
                        name: currentDetailProduct.name,
                        price: parseFloat(currentDetailProduct.selling_price || '0'),
                        qty: 1
                    });
                }
                saveCart(cart);
                showToast(`Added <strong>${currentDetailProduct.name}</strong> to cart!`);
                closeDetailsModal();
            }
        });
    }

    // Escape key to close any active modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCompatModal();
            closeDetailsModal();
        }
    });
});
