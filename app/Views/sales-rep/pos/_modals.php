<!-- POS dialogs: discount, payment, printable invoice. Wire confirm to your SaleController. -->
<dialog class="modal sales-dialog" id="discount-modal">
    <div class="modal__content">
        <header class="modal__header">
            <h2 class="modal__title">Apply Discount</h2>
            <button type="button" class="sales-icon-button" data-close-modal aria-label="Close">
                <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
            </button>
        </header>
        <form id="discount-form" class="modal__body">
            <label class="field-label" for="discount-type">Discount Type</label>
            <select id="discount-type" class="field-select">
                <option value="percent">Percentage (%)</option>
                <option value="fixed">Fixed Amount (Rs.)</option>
            </select>
            <label class="field-label" for="discount-value">Value</label>
            <input type="number" id="discount-value" class="field-input" min="0" step="0.01" placeholder="0.00" required>
            <footer class="modal__footer">
                <button type="button" class="sales-button sales-button--secondary" data-close-modal>Cancel</button>
                <button type="submit" class="sales-button sales-button--primary">Apply</button>
            </footer>
        </form>
    </div>
</dialog>

<dialog class="modal sales-dialog" id="checkout-modal">
    <div class="modal__content">
        <header class="modal__header">
            <h2 class="modal__title">Complete Sale</h2>
            <button type="button" class="sales-icon-button" data-close-modal aria-label="Close">
                <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
            </button>
        </header>
        <div class="modal__body">
            <div class="checkout-summary">
                <div class="checkout-summary__row"><span>Subtotal</span><span id="modal-subtotal">Rs. 0.00</span></div>
                <div class="checkout-summary__row"><span>Discount</span><span id="modal-discount">- Rs. 0.00</span></div>
                <div class="checkout-summary__row checkout-summary__row--total"><span>Total due</span><strong id="modal-total">Rs. 0.00</strong></div>
            </div>
            <p class="field-label">Payment Method</p>
            <div class="payment-methods" role="radiogroup" aria-label="Payment method">
                <label class="payment-option"><input type="radio" name="payment_method" value="cash" checked> <span>Cash</span></label>
                <label class="payment-option"><input type="radio" name="payment_method" value="card"> <span>Card</span></label>
                <label class="payment-option"><input type="radio" name="payment_method" value="credit"> <span>Credit</span></label>
            </div>
            <label class="field-label" for="amount-paid">Amount Paid</label>
            <input type="number" id="amount-paid" class="field-input" min="0" step="0.01" placeholder="0.00">
            <p class="checkout-change">Change: <strong id="change-amount">Rs. 0.00</strong></p>
        </div>
        <footer class="modal__footer">
            <button type="button" class="sales-button sales-button--secondary" data-close-modal>Cancel</button>
            <button type="button" class="sales-button sales-button--primary" id="btn-confirm-sale">Confirm &amp; Print Invoice</button>
        </footer>
    </div>
</dialog>

<dialog class="modal modal--wide sales-dialog" id="invoice-modal">
    <div class="modal__content">
        <header class="modal__header">
            <h2 class="modal__title">Invoice Generated</h2>
            <button type="button" class="sales-icon-button" data-close-modal aria-label="Close">
                <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
            </button>
        </header>
        <div class="modal__body invoice-preview" id="invoice-preview"></div>
        <footer class="modal__footer">
            <button type="button" class="sales-button sales-button--secondary" onclick="window.print()">Print</button>
            <button type="button" class="sales-button sales-button--primary" data-close-modal>New Sale</button>
        </footer>
    </div>
</dialog>
