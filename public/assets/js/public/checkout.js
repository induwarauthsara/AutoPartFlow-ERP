document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkoutForm');
    const confirmBtn = document.getElementById('confirmOrderBtn');
    const fullNameInput = document.getElementById('fullName');
    const phoneInput = document.getElementById('phoneNumber');
    const addressInput = document.getElementById('deliveryAddress');

    const fullNameError = document.getElementById('fullNameError');
    const phoneError = document.getElementById('phoneError');
    const addressError = document.getElementById('addressError');

    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    const successOrderNumber = document.getElementById('successOrderNumber');
    const successDeliveryTime = document.getElementById('successDeliveryTime');
    const trackOrderBtn = document.getElementById('trackOrderBtn');

    const summaryItemsList = document.getElementById('summaryItemsList');
    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryTotal = document.getElementById('summaryTotal');

    const baseUrl = window.APP_CONFIG?.baseUrl || '';

    // 1. Load cart items from localStorage
    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('autopartflow_cart') || '[]');
        } catch (e) {
            return [];
        }
    }

    let cartItems = getCart();

    // 2. Render Order Summary
    function renderOrderSummary() {
        if (!summaryItemsList) return;

        summaryItemsList.innerHTML = '';
        let subtotal = 0;

        if (!cartItems.length) {
            summaryItemsList.innerHTML = `
                <li class="summary-item">
                    <div class="summary-item__info">
                        <strong class="summary-item__name">Your cart is empty</strong>
                        <span class="summary-item__qty">Add products before checkout.</span>
                    </div>
                </li>
            `;
            if (summarySubtotal) summarySubtotal.textContent = 'Rs. 0';
            if (summaryTotal) summaryTotal.textContent = 'Rs. 0';
            if (confirmBtn) confirmBtn.disabled = true;
            return;
        }

        if (confirmBtn) confirmBtn.disabled = false;

        cartItems.forEach(item => {
            const price = parseFloat(item.price || '0');
            const qty = parseInt(item.qty || '1', 10);
            const lineTotal = price * qty;
            subtotal += lineTotal;

            const li = document.createElement('li');
            li.className = 'summary-item';
            li.innerHTML = `
                <div class="summary-item__info">
                    <strong class="summary-item__name">${escapeHTML(item.name || item.code)}</strong>
                    <span class="summary-item__qty">Qty: ${qty}</span>
                </div>
                <span class="summary-item__price">Rs. ${Number(lineTotal).toLocaleString()}</span>
            `;
            summaryItemsList.appendChild(li);
        });

        const deliveryFee = 350;
        const total = subtotal + deliveryFee;

        if (summarySubtotal) summarySubtotal.textContent = `Rs. ${Number(subtotal).toLocaleString()}`;
        if (summaryTotal) summaryTotal.textContent = `Rs. ${Number(total).toLocaleString()}`;
    }

    function escapeHTML(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    renderOrderSummary();

    // 3. Form Validation (07XXXXXXXX Sri Lankan mobile format)
    const phoneRegex = /^(?:07\d{8}|0\d{9}|\+94\d{9})$/;

    function validateForm() {
        let isValid = true;

        // Reset errors
        [fullNameInput, phoneInput, addressInput].forEach(el => {
            if (el) el.classList.remove('border-error');
        });
        [fullNameError, phoneError, addressError].forEach(el => {
            if (el) el.classList.add('hidden');
        });

        const fullNameVal = fullNameInput?.value.trim() || '';
        const phoneVal = phoneInput?.value.trim() || '';
        const addressVal = addressInput?.value.trim() || '';

        if (!fullNameVal) {
            fullNameInput?.classList.add('border-error');
            fullNameError?.classList.remove('hidden');
            isValid = false;
        }

        if (!phoneRegex.test(phoneVal)) {
            phoneInput?.classList.add('border-error');
            phoneError?.classList.remove('hidden');
            isValid = false;
        }

        if (!addressVal) {
            addressInput?.classList.add('border-error');
            addressError?.classList.remove('hidden');
            isValid = false;
        }

        return isValid;
    }

    // Real-time input clearing on typing
    if (fullNameInput) {
        fullNameInput.addEventListener('input', () => {
            if (fullNameInput.value.trim()) {
                fullNameInput.classList.remove('border-error');
                fullNameError?.classList.add('hidden');
            }
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            if (phoneRegex.test(phoneInput.value.trim())) {
                phoneInput.classList.remove('border-error');
                phoneError?.classList.add('hidden');
            }
        });
    }

    if (addressInput) {
        addressInput.addEventListener('input', () => {
            if (addressInput.value.trim()) {
                addressInput.classList.remove('border-error');
                addressError?.classList.add('hidden');
            }
        });
    }

    // 4. Order Confirmation Handler
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            if (!cartItems.length) {
                alert('Your cart is empty. Please add a product before checkout.');
                window.location.href = `${baseUrl}/cart`;
                return;
            }

            if (!validateForm()) {
                return;
            }

            const selectedPayment = document.querySelector('input[name="paymentMethod"]:checked')?.value;
            if (selectedPayment !== 'cod') {
                alert('Only Cash on Delivery is available.');
                return;
            }

            const payload = {
                fullName: fullNameInput.value.trim(),
                phoneNumber: phoneInput.value.trim(),
                deliveryAddress: addressInput.value.trim(),
                paymentMethod: selectedPayment,
                csrf_token: window.APP_CONFIG?.csrfToken || '',
                items: cartItems
            };

            const originalBtnHTML = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `
                <span class="material-symbols-outlined spin" style="font-size:20px;">sync</span>
                <span>Placing Order...</span>
            `;

            try {
                const response = await fetch(`${baseUrl}/checkout/place`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    // Update modal content
                    if (successOrderNumber) successOrderNumber.textContent = data.order_number || '';
                    if (trackOrderBtn) trackOrderBtn.dataset.orderNumber = data.order_number || '';
                    if (successDeliveryTime) successDeliveryTime.textContent = data.estimated_delivery || 'The store will confirm the delivery date.';

                    // Clear local shopping cart
                    localStorage.removeItem('autopartflow_cart');

                    // Show success modal with smooth transition
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.add('active');
                    }, 10);
                } else {
                    alert(data.message || 'Unable to place order. Please verify your details.');
                }
            } catch (err) {
                alert('A network error occurred while placing your order. Please check your connection.');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnHTML;
            }
        });
    }

    if (trackOrderBtn) {
        trackOrderBtn.addEventListener('click', () => {
            const orderNumber = trackOrderBtn.dataset.orderNumber || '';
            if (orderNumber) {
                window.location.href = `${baseUrl}/track-order?order_number=${encodeURIComponent(orderNumber)}`;
            }
        });
    }

    // 5. Close Modal & Continue Shopping
    window.closeSuccessModal = function () {
        if (!modalContent || !modal) return;
        modalContent.classList.remove('active');
        setTimeout(() => {
            modal.classList.add('hidden');
            window.location.href = `${baseUrl}/catalog`;
        }, 250);
    };
});
