/**
 * AutoPartFlow ERP — Universal Real-Time Form Validation & Input Guard Engine
 * Single Source of Truth for Form Validation, Negative Number Blocking, and Live Feedback.
 */
(function (global) {
    'use strict';

    // Canonical Regex Patterns & Rule Definitions (Single Source of Truth)
    const RULES = {
        required: {
            test: function (val, el) {
                if (el && el.type === 'checkbox') return el.checked;
                if (el && el.type === 'radio') {
                    const name = el.name;
                    return Boolean(document.querySelector('input[type="radio"][name="' + name + '"]:checked'));
                }
                return String(val ?? '').trim().length > 0;
            },
            message: function (el) {
                const label = getFieldLabel(el);
                return (label ? label : 'This field') + ' is required.';
            }
        },

        email: {
            test: function (val) {
                if (!val) return true; // Optional unless combined with required
                const clean = String(val).trim();
                // RFC 5322 compliant regex
                const regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
                return regex.test(clean);
            },
            message: 'Please enter a valid email address (e.g. name@company.com).'
        },

        phone: {
            test: function (val) {
                if (!val) return true;
                const clean = String(val).trim().replace(/[\s\-\(\)\.]/g, '');
                // Sri Lankan format: 07XXXXXXXX (10 digits), 0XXXXXXXXX (10 digits), +94XXXXXXXXX (12 chars), 0094...
                // Or international standard E.164: + followed by 7-15 digits
                const isSriLankan = /^(?:\+94|0094|0)?(?:7[0-9]{8}|[1-9][0-9]{8})$/.test(clean);
                const isInternational = /^\+?[1-9]\d{7,14}$/.test(clean);
                return isSriLankan || isInternational;
            },
            message: 'Please enter a valid phone number (e.g. 0712345678 or +94712345678).'
        },

        name: {
            test: function (val) {
                if (!val) return true;
                const clean = String(val).trim();
                if (clean.length < 2) return false;
                // Supports letters, spaces, hyphens, periods, and apostrophes
                try {
                    return /^[\p{L}\s.'-]{2,150}$/u.test(clean);
                } catch (e) {
                    return /^[a-zA-Z\s.'-]{2,150}$/.test(clean);
                }
            },
            message: 'Please enter a valid name (at least 2 letters).'
        },


        username: {
            test: function (val) {
                if (!val) return true;
                return /^[A-Za-z0-9._-]{3,60}$/.test(String(val).trim());
            },
            message: 'Username must be 3–60 characters (letters, numbers, ., _, -).'
        },

        password: {
            test: function (val, el) {
                if (!val && el && !el.required) return true;
                const min = el ? parseInt(el.getAttribute('minlength') || '8', 10) : 8;
                return String(val || '').length >= min;
            },
            message: function (el) {
                const min = el ? parseInt(el.getAttribute('minlength') || '8', 10) : 8;
                return 'Password must be at least ' + min + ' characters long.';
            }
        },

        confirmPassword: {
            test: function (val, el) {
                if (!el) return true;
                const form = el.form || el.closest('form');
                if (!form) return true;
                const pwd = form.querySelector('input[type="password"][name="password"], input[type="password"][name="new_password"], input[type="password"]#password, input[type="password"]#fPass');
                if (!pwd) return true;
                return String(val || '') === String(pwd.value || '');
            },
            message: 'Passwords do not match.'
        },

        nonNegative: {
            test: function (val) {
                if (val === '' || val === null || val === undefined) return true;
                const num = Number(val);
                return !Number.isNaN(num) && num >= 0;
            },
            message: 'Value cannot be negative.'
        },

        positiveNumber: {
            test: function (val) {
                if (val === '' || val === null || val === undefined) return true;
                const num = Number(val);
                return !Number.isNaN(num) && num > 0;
            },
            message: 'Value must be greater than 0.'
        },

        positiveInteger: {
            test: function (val) {
                if (val === '' || val === null || val === undefined) return true;
                const num = Number(val);
                return Number.isInteger(num) && num >= 1;
            },
            message: 'Please enter a whole number of at least 1.'
        },

        integer: {
            test: function (val) {
                if (val === '' || val === null || val === undefined) return true;
                const num = Number(val);
                return Number.isInteger(num);
            },
            message: 'Please enter a valid whole number.'
        },

        min: {
            test: function (val, el) {
                if (val === '' || val === null || val === undefined || !el) return true;
                const minVal = parseFloat(el.getAttribute('min'));
                if (Number.isNaN(minVal)) return true;
                const num = parseFloat(val);
                return !Number.isNaN(num) && num >= minVal;
            },
            message: function (el) {
                return 'Value must be at least ' + el.getAttribute('min') + '.';
            }
        },

        max: {
            test: function (val, el) {
                if (val === '' || val === null || val === undefined || !el) return true;
                const maxVal = parseFloat(el.getAttribute('max'));
                if (Number.isNaN(maxVal)) return true;
                const num = parseFloat(val);
                return !Number.isNaN(num) && num <= maxVal;
            },
            message: function (el) {
                return 'Value cannot exceed ' + el.getAttribute('max') + '.';
            }
        },

        minlength: {
            test: function (val, el) {
                if (!val || !el) return true;
                const minLen = parseInt(el.getAttribute('minlength'), 10);
                return Number.isNaN(minLen) || String(val).length >= minLen;
            },
            message: function (el) {
                return 'Must be at least ' + el.getAttribute('minlength') + ' characters.';
            }
        },

        maxlength: {
            test: function (val, el) {
                if (!val || !el) return true;
                const maxLen = parseInt(el.getAttribute('maxlength'), 10);
                return Number.isNaN(maxLen) || String(val).length <= maxLen;
            },
            message: function (el) {
                return 'Cannot exceed ' + el.getAttribute('maxlength') + ' characters.';
            }
        },

        pattern: {
            test: function (val, el) {
                if (!val || !el) return true;
                const pat = el.getAttribute('pattern');
                if (!pat) return true;
                try {
                    const reg = new RegExp('^(?:' + pat + ')$');
                    return reg.test(String(val));
                } catch (e) {
                    return true;
                }
            },
            message: 'Please match the requested format.'
        },

        url: {
            test: function (val) {
                if (!val) return true;
                const clean = String(val).trim();
                // Allow relative paths starting with / as well as http/https URLs
                if (clean.startsWith('/')) return true;
                try {
                    const parsed = new URL(clean);
                    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
                } catch (e) {
                    return false;
                }
            },
            message: 'Please enter a valid URL (e.g. https://example.com/item.jpg).'
        },

        year: {
            test: function (val) {
                if (!val) return true;
                const yr = parseInt(val, 10);
                const currentYear = new Date().getFullYear();
                return Number.isInteger(yr) && yr >= 1900 && yr <= currentYear + 2;
            },
            message: 'Please enter a valid 4-digit model year (1900–' + (new Date().getFullYear() + 1) + ').'
        }
    };

    /**
     * Extracts a human-friendly field name from labels, placeholder, or name attribute.
     */
    function getFieldLabel(el) {
        if (!el) return '';
        // 1. Explicit data-label attribute
        if (el.dataset.label) return el.dataset.label;

        // 2. Associated <label for="...">
        if (el.id) {
            const labelEl = document.querySelector('label[for="' + el.id + '"]');
            if (labelEl) {
                const text = labelEl.textContent.replace(/[*:]/g, '').trim();
                if (text) return text;
            }
        }

        // 3. Parent label
        const parentLabel = el.closest('label');
        if (parentLabel) {
            const clone = parentLabel.cloneNode(true);
            const nestedInputs = clone.querySelectorAll('input, select, textarea, button, small, .field-error-message');
            nestedInputs.forEach(function (n) { n.remove(); });
            const text = clone.textContent.replace(/[*:]/g, '').trim();
            if (text) return text;
        }

        // 4. Placeholder
        if (el.placeholder && el.placeholder.length < 30 && !el.placeholder.includes('...')) {
            return el.placeholder.replace(/[*:]/g, '').trim();
        }

        // 5. Formatted name attribute
        if (el.name) {
            return el.name
                .replace(/([A-Z])/g, ' $1')
                .replace(/[_\-]/g, ' ')
                .replace(/\b\w/g, function (c) { return c.toUpperCase(); })
                .trim();
        }

        return '';
    }

    /**
     * Checks if a field should prohibit negative numbers.
     */
    function shouldProhibitNegative(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.type !== 'number' && el.type !== 'text') return false;

        // Explicit attributes
        if (el.hasAttribute('data-non-negative') || el.hasAttribute('data-positive')) return true;

        // HTML5 min attribute >= 0
        const minAttr = el.getAttribute('min');
        if (minAttr !== null && !Number.isNaN(parseFloat(minAttr)) && parseFloat(minAttr) >= 0) {
            return true;
        }

        // Field semantic names that must never be negative
        const identifier = ((el.name || '') + ' ' + (el.id || '')).toLowerCase();
        const nonNegativeKeywords = [
            'salary', 'qty', 'quantity', 'cost', 'price', 'stock', 'reorder',
            'discount', 'rate', 'paid', 'amount', 'fee', 'charge', 'retention',
            'threshold', 'due_days', 'year'
        ];

        return nonNegativeKeywords.some(function (kw) { return identifier.includes(kw); });
    }

    /**
     * Physical Guard: Intercepts negative keys, pasted minuses, and scroll clamping.
     */
    function attachNegativeGuard(el) {
        if (el.__negativeGuardAttached) return;
        el.__negativeGuardAttached = true;

        // 1. Intercept minus sign and scientific 'e' on keydown
        el.addEventListener('keydown', function (e) {
            // Block minus sign '-' (standard key, numpad, or code)
            if (e.key === '-' || e.key === 'Subtract' || e.code === 'Minus' || e.code === 'NumpadSubtract') {
                e.preventDefault();
                showNegativeWarning(el);
                return;
            }

            // Also block 'e' / 'E' / '+' if type is number to prevent invalid expressions
            if (el.type === 'number' && (e.key === 'e' || e.key === 'E' || e.key === '+')) {
                e.preventDefault();
            }
        });

        // 2. Intercept paste and strip any minus characters
        el.addEventListener('paste', function (e) {
            const pasteData = (e.clipboardData || window.clipboardData)?.getData('text') || '';
            if (pasteData.includes('-')) {
                e.preventDefault();
                const sanitized = pasteData.replace(/-/g, '').trim();
                const num = parseFloat(sanitized);
                if (!Number.isNaN(num)) {
                    document.execCommand('insertText', false, sanitized);
                }
                showNegativeWarning(el);
            }
        });

        // 3. Intercept input event: Clamp if somehow value was set to negative
        el.addEventListener('input', function () {
            let val = el.value;
            if (val.includes('-') || (parseFloat(val) < 0)) {
                const min = el.getAttribute('min') !== null ? parseFloat(el.getAttribute('min')) : 0;
                const safeMin = Math.max(0, Number.isNaN(min) ? 0 : min);
                el.value = val.replace(/-/g, '');
                if (parseFloat(el.value) < safeMin) {
                    el.value = safeMin > 0 ? safeMin : '';
                }
                showNegativeWarning(el);
            }
        });
    }

    let warningToastTimer = null;
    function showNegativeWarning(el) {
        AppValidation.showError(el, 'Negative values are not permitted for this field.');
        // Clear error automatically after 2.5 seconds if user stops typing
        window.clearTimeout(el.__warnTimer);
        el.__warnTimer = window.setTimeout(function () {
            if (parseFloat(el.value) >= 0 || el.value === '') {
                AppValidation.validateInput(el);
            }
        }, 2500);
    }

    /**
     * Determines which validation rules apply to an element.
     */
    function getRulesForElement(el) {
        if (!el || el.disabled || el.type === 'hidden' || el.type === 'submit' || el.type === 'button') {
            return [];
        }

        const rules = [];

        // 1. Required
        if (el.required || el.hasAttribute('required') || el.dataset.validate?.includes('required')) {
            rules.push('required');
        }

        // 2. Explicit data-validate rules (e.g. data-validate="phone", data-validate="name")
        if (el.dataset.validate) {
            const customRules = el.dataset.validate.split(',').map(function (s) { return s.trim(); });
            customRules.forEach(function (r) {
                if (r && r !== 'required' && !rules.includes(r)) rules.push(r);
            });
        }

        // 3. HTML5 type inferences
        const type = (el.type || '').toLowerCase();
        if (type === 'email' && !rules.includes('email')) rules.push('email');
        if (type === 'tel' && !rules.includes('phone')) rules.push('phone');
        if (type === 'url' && !rules.includes('url')) rules.push('url');
        if (type === 'password') {
            const isConfirm = /confirm/i.test(el.name || '') || /confirm/i.test(el.id || '');
            if (isConfirm && !rules.includes('confirmPassword')) {
                rules.push('confirmPassword');
            } else if (!rules.includes('password')) {
                rules.push('password');
            }
        }

        // 4. Semantic field name inferences (if not already assigned)
        const nameId = ((el.name || '') + ' ' + (el.id || '')).toLowerCase();
        if (!rules.includes('email') && (nameId.includes('email') || nameId.includes('femail'))) {
            rules.push('email');
        }
        if (!rules.includes('phone') && (nameId.includes('phone') || nameId.includes('fphone') || nameId.includes('mobile'))) {
            rules.push('phone');
        }
        if (!rules.includes('name') && !rules.includes('username') && (nameId.includes('full_name') || nameId.includes('fullname') || nameId === 'fname' || nameId.includes('contact_person'))) {
            rules.push('name');
        }
        if (!rules.includes('username') && (nameId === 'username' || nameId === 'user_name')) {
            rules.push('username');
        }
        if (!rules.includes('year') && (nameId === 'year' || nameId === 'model_year' || nameId.includes('year_from') || nameId.includes('year_to'))) {
            rules.push('year');
        }

        // 5. Number and Non-Negative restrictions
        if (shouldProhibitNegative(el)) {
            const minVal = parseFloat(el.getAttribute('min'));
            if (!Number.isNaN(minVal) && minVal >= 1) {
                if (!rules.includes('positiveInteger') && !rules.includes('min')) rules.push('min');
            } else {
                if (!rules.includes('nonNegative')) rules.push('nonNegative');
            }
        }

        // 6. Generic HTML5 constraints
        if (el.hasAttribute('min') && !rules.includes('min')) rules.push('min');
        if (el.hasAttribute('max') && !rules.includes('max')) rules.push('max');
        if (el.hasAttribute('minlength') && !rules.includes('minlength') && !rules.includes('password')) rules.push('minlength');
        if (el.hasAttribute('maxlength') && !rules.includes('maxlength')) rules.push('maxlength');
        if (el.hasAttribute('pattern') && !rules.includes('pattern') && !rules.includes('username')) rules.push('pattern');

        return rules;
    }

    /**
     * Finds or creates an error message container for an input.
     */
    function getOrCreateErrorElement(el) {
        // 1. Check for dedicated attribute reference (aria-describedby)
        const describedBy = el.getAttribute('aria-describedby');
        if (describedBy) {
            const target = document.getElementById(describedBy);
            if (target) return target;
        }

        // 2. Check conventional IDs (e.g. "#{id}-error", "#{id}Error", "#{id}_error", "eName")
        const id = el.id;
        if (id) {
            const candidates = [
                id + '-error',
                id + 'Error',
                id + '_error',
                'e' + id.charAt(0).toUpperCase() + id.slice(1), // e.g. fEmail -> eFEmail
                id.startsWith('f') ? 'e' + id.slice(1) : null     // e.g. fEmail -> eEmail
            ];
            for (const cand of candidates) {
                if (cand) {
                    const found = document.getElementById(cand);
                    if (found) return found;
                }
            }
        }

        // 3. Check for existing sibling error container
        const parent = el.closest('.form-group, .field, .finder-field, .delivery-field, .purchase-field, label') || el.parentElement;
        if (parent) {
            const existing = parent.querySelector('.field-error-message, .err, .error-message');
            if (existing && !existing.dataset.customManaged) return existing;
        }

        // 4. Create and insert a new standard error container
        const errorEl = document.createElement('div');
        errorEl.className = 'field-error-message';
        errorEl.setAttribute('role', 'alert');
        errorEl.setAttribute('aria-live', 'polite');
        const generatedId = (el.id || el.name || 'field') + '-error-msg';
        errorEl.id = generatedId;
        el.setAttribute('aria-describedby', generatedId);

        // Position after the input or after the input-with-icon container
        const insertAfter = el.closest('.input-with-icon, .quantity-control, .input-group') || el;
        if (insertAfter.nextSibling) {
            insertAfter.parentNode.insertBefore(errorEl, insertAfter.nextSibling);
        } else {
            insertAfter.parentNode.appendChild(errorEl);
        }

        return errorEl;
    }

    /**
     * Displays a validation error on an element.
     */
    function showError(el, message) {
        if (!el) return;
        el.classList.add('is-invalid', 'border-error');
        el.classList.remove('is-valid');
        el.setAttribute('aria-invalid', 'true');

        const errorEl = getOrCreateErrorElement(el);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            errorEl.style.display = 'block';
        }
    }

    /**
     * Clears validation error on an element.
     */
    function clearError(el) {
        if (!el) return;
        el.classList.remove('is-invalid', 'border-error');
        if (el.value && String(el.value).trim()) {
            el.classList.add('is-valid');
        } else {
            el.classList.remove('is-valid');
        }
        el.removeAttribute('aria-invalid');

        const errorEl = getOrCreateErrorElement(el);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            errorEl.style.display = 'none';
        }
    }

    /**
     * Validates a single element against all active rules.
     * Returns true if valid, false if invalid.
     */
    function validateInput(el) {
        if (!el || el.disabled || el.type === 'hidden' || el.type === 'submit' || el.type === 'button') {
            return true;
        }

        // Negative check guard
        if (shouldProhibitNegative(el)) {
            attachNegativeGuard(el);
        }

        const rules = getRulesForElement(el);
        const val = el.value;

        for (const ruleName of rules) {
            const rule = RULES[ruleName];
            if (!rule) continue;

            const passed = rule.test(val, el);
            if (!passed) {
                const msg = typeof rule.message === 'function' ? rule.message(el) : rule.message;
                showError(el, msg);
                return false;
            }
        }

        clearError(el);
        return true;
    }

    /**
     * Validates all inputs inside a form or container element.
     * Returns true if all valid, false otherwise.
     */
    function validateForm(form) {
        if (!form) return true;
        const elements = form.querySelectorAll('input, select, textarea');
        let isAllValid = true;
        let firstInvalid = null;

        elements.forEach(function (el) {
            if (el.type === 'hidden' || el.disabled) return;
            const valid = validateInput(el);
            if (!valid) {
                isAllValid = false;
                if (!firstInvalid) firstInvalid = el;
            }
        });

        if (!isAllValid && firstInvalid) {
            firstInvalid.focus();
            if (typeof firstInvalid.scrollIntoView === 'function') {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isAllValid;
    }

    /**
     * Global Event Delegator & Auto-Initializer
     */
    function setupDelegatedListeners() {
        // 1. Negative Number Guard Keydown & Paste Delegation
        document.addEventListener('keydown', function (e) {
            const target = e.target;
            if (target && target.tagName === 'INPUT' && shouldProhibitNegative(target)) {
                attachNegativeGuard(target);
                if (e.key === '-' || e.key === 'Subtract' || e.code === 'Minus' || e.code === 'NumpadSubtract') {
                    e.preventDefault();
                    showNegativeWarning(target);
                }
            }
        }, true);

        document.addEventListener('paste', function (e) {
            const target = e.target;
            if (target && target.tagName === 'INPUT' && shouldProhibitNegative(target)) {
                attachNegativeGuard(target);
            }
        }, true);

        // 2. Real-Time Input Event Delegation (Live validation while typing)
        document.addEventListener('input', function (e) {
            const el = e.target;
            if (!el || !['INPUT', 'TEXTAREA'].includes(el.tagName)) return;

            if (shouldProhibitNegative(el)) {
                attachNegativeGuard(el);
            }

            // If the element has already been marked invalid or touched, validate immediately
            if (el.classList.contains('is-invalid') || el.__touched) {
                validateInput(el);
            }
        }, true);

        // 3. Blur Event Delegation (Initial validation when leaving a field)
        document.addEventListener('blur', function (e) {
            const el = e.target;
            if (!el || !['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) return;

            el.__touched = true;
            // Only validate if field has content or is required
            if (el.value || el.required || el.hasAttribute('required')) {
                validateInput(el);
            }
        }, true);

        // 4. Change Event Delegation (for dropdowns, radios, checkboxes, date pickers)
        document.addEventListener('change', function (e) {
            const el = e.target;
            if (!el || !['SELECT', 'INPUT'].includes(el.tagName)) return;
            if (el.tagName === 'SELECT' || ['radio', 'checkbox', 'date', 'number'].includes(el.type)) {
                el.__touched = true;
                validateInput(el);
            }
        }, true);

        // 5. Universal Form Submit Interception
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Allow forms with data-no-validate to skip
            if (form.hasAttribute('data-no-validate') || form.noValidate) return;

            const isValid = validateForm(form);
            if (!isValid) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }, true);
    }

    /**
     * Auto-discovers and scans existing DOM elements on load.
     */
    function scanAndAttachGuards() {
        document.querySelectorAll('input[type="number"], input[min="0"], input[min="1"], input[data-non-negative]').forEach(function (input) {
            if (shouldProhibitNegative(input)) {
                attachNegativeGuard(input);
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setupDelegatedListeners();
            scanAndAttachGuards();
        });
    } else {
        setupDelegatedListeners();
        scanAndAttachGuards();
    }

    // Public API object
    const AppValidation = {
        rules: RULES,
        validateInput: validateInput,
        validateForm: validateForm,
        showError: showError,
        clearError: clearError,
        preventNegative: attachNegativeGuard,
        shouldProhibitNegative: shouldProhibitNegative,
        scan: scanAndAttachGuards
    };

    global.AppValidation = AppValidation;

})(typeof window !== 'undefined' ? window : this);
