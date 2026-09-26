document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('finderPage');
    if (!page) return;

    const brand = document.getElementById('brand_id');
    const model = document.getElementById('model_id');
    const engine = document.getElementById('engine_id');
    const baseUrl = window.APP_CONFIG?.baseUrl || '';

    function setOptions(select, items, placeholder, selectedId = '') {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name || item.engine_code || 'Option';
            if (String(item.id) === String(selectedId)) option.selected = true;
            select.appendChild(option);
        });
        select.disabled = items.length === 0;
    }

    async function fetchJson(url) {
        const response = await fetch(url, {headers: {'Accept': 'application/json'}});
        const data = await response.json();
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'Unable to load vehicle data.');
        return data;
    }

    brand.addEventListener('change', async () => {
        model.disabled = true;
        engine.disabled = true;
        setOptions(model, [], 'Loading models...');
        setOptions(engine, [], 'Select engine');
        if (!brand.value) { setOptions(model, [], 'Select model'); return; }
        try {
            const data = await fetchJson(`${baseUrl}/finder/models?brand_id=${encodeURIComponent(brand.value)}`);
            setOptions(model, data.models, 'Select model');
        } catch (error) {
            setOptions(model, [], 'Unable to load models');
            alert(error.message);
        }
    });

    model.addEventListener('change', async () => {
        engine.disabled = true;
        setOptions(engine, [], 'Loading engines...');
        if (!model.value) { setOptions(engine, [], 'Select engine'); return; }
        try {
            const data = await fetchJson(`${baseUrl}/finder/engines?model_id=${encodeURIComponent(model.value)}`);
            const mapped = data.engines.map(item => ({id: item.id, name: `${item.engine_code || 'Engine'}${item.displacement_cc ? ` · ${item.displacement_cc}cc` : ''} · ${item.fuel_type}`}));
            setOptions(engine, mapped, 'Select engine');
        } catch (error) {
            setOptions(engine, [], 'Unable to load engines');
            alert(error.message);
        }
    });

    async function initializeSelections() {
        const selectedBrand = brand.value;
        const selectedModel = page.querySelector('.finder-container')?.dataset.selectedModel || '0';
        const selectedEngine = page.querySelector('.finder-container')?.dataset.selectedEngine || '0';
        if (!selectedBrand) return;
        try {
            const modelData = await fetchJson(`${baseUrl}/finder/models?brand_id=${encodeURIComponent(selectedBrand)}`);
            setOptions(model, modelData.models, 'Select model', selectedModel);
            if (selectedModel) {
                const engineData = await fetchJson(`${baseUrl}/finder/engines?model_id=${encodeURIComponent(selectedModel)}`);
                const mapped = engineData.engines.map(item => ({id: item.id, name: `${item.engine_code || 'Engine'}${item.displacement_cc ? ` · ${item.displacement_cc}cc` : ''} · ${item.fuel_type}`}));
                setOptions(engine, mapped, 'Select engine', selectedEngine);
            }
        } catch (error) {
            console.error(error);
        }
    }

    document.querySelectorAll('.finder-add').forEach(button => {
        button.addEventListener('click', () => {
            const code = button.dataset.code || '';
            const name = button.dataset.name || code;
            const price = Number(button.dataset.price || 0);
            let cart = [];
            try { cart = JSON.parse(localStorage.getItem('autopartflow_cart') || '[]'); } catch (_) { cart = []; }
            const existing = cart.find(item => item.code === code);
            if (existing) existing.qty = Math.min(99, Number(existing.qty || 1) + 1);
            else cart.push({code, name, price, qty: 1});
            localStorage.setItem('autopartflow_cart', JSON.stringify(cart));
            const badge = document.querySelector('.cart-count');
            if (badge) badge.textContent = String(cart.reduce((sum, item) => sum + Number(item.qty || 0), 0));
            button.textContent = 'Added';
            setTimeout(() => { button.innerHTML = '<span class="material-symbols-outlined">add_shopping_cart</span> Add'; }, 900);
        });
    });

    initializeSelections();
});
