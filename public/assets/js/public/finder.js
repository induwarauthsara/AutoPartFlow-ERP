document.addEventListener('DOMContentLoaded', () => {

    const page = document.getElementById('finderPage');

    if (!page) {
        return;
    }


    const brand = document.getElementById('brand_id');
    const model = document.getElementById('model_id');
    const engine = document.getElementById('engine_id');

    const form = document.getElementById('finderForm');

    const baseUrl = window.APP_CONFIG?.baseUrl || '';


    /*
     * Selected values restored after a search.
     */
    const container = page.querySelector('.finder-container');

    const selectedModel =
        container?.dataset.selectedModel || '0';

    const selectedEngine =
        container?.dataset.selectedEngine || '0';


    /*
     * Populate a select element.
     */
    function setOptions(
        select,
        items,
        placeholder,
        selectedId = ''
    ) {

        select.innerHTML = '';

        const placeholderOption =
            document.createElement('option');

        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;

        select.appendChild(placeholderOption);


        items.forEach(item => {

            const option =
                document.createElement('option');

            option.value = item.id;

            option.textContent =
                item.name ||
                item.engine_code ||
                'Option';

            if (
                String(item.id) ===
                String(selectedId)
            ) {
                option.selected = true;
            }

            select.appendChild(option);
        });


        select.disabled = items.length === 0;
    }


    /*
     * Fetch JSON from the MVC endpoints.
     */
    async function fetchJson(url) {

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });


        const data = await response.json();


        if (
            !response.ok ||
            data.status !== 'success'
        ) {

            throw new Error(
                data.message ||
                'Unable to load vehicle data.'
            );
        }


        return data;
    }


    /*
     * Brand changed.
     *
     * Load models.
     */
    brand.addEventListener('change', async () => {

        model.disabled = true;
        engine.disabled = true;

        setOptions(
            model,
            [],
            'Loading models...'
        );

        setOptions(
            engine,
            [],
            'Select engine'
        );


        if (!brand.value) {

            setOptions(
                model,
                [],
                'Select model'
            );

            return;
        }


        try {

            const data = await fetchJson(
                `${baseUrl}/finder/models?brand_id=${encodeURIComponent(
                    brand.value
                )}`
            );


            setOptions(
                model,
                data.models,
                'Select model'
            );

        } catch (error) {

            setOptions(
                model,
                [],
                'Unable to load models'
            );

            alert(error.message);
        }

    });


    /*
     * Model changed.
     *
     * Load engines.
     */
    model.addEventListener('change', async () => {

        engine.disabled = true;

        setOptions(
            engine,
            [],
            'Loading engines...'
        );


        if (!model.value) {

            setOptions(
                engine,
                [],
                'Select engine'
            );

            return;
        }


        try {

            const data = await fetchJson(
                `${baseUrl}/finder/engines?model_id=${encodeURIComponent(
                    model.value
                )}`
            );


            const mapped =
                data.engines.map(item => {

                    return {
                        id: item.id,

                        name:
                            `${item.engine_code || 'Engine'}` +
                            `${
                                item.displacement_cc
                                    ? ` · ${item.displacement_cc}cc`
                                    : ''
                            }` +
                            `${
                                item.fuel_type
                                    ? ` · ${item.fuel_type}`
                                    : ''
                            }`
                    };

                });


            setOptions(
                engine,
                mapped,
                'Select engine'
            );

        } catch (error) {

            setOptions(
                engine,
                [],
                'Unable to load engines'
            );

            alert(error.message);
        }

    });


    /*
     * Restore model and engine selections
     * after the page reloads with GET parameters.
     */
    async function initializeSelections() {

        const selectedBrand =
            brand.value;


        if (!selectedBrand) {
            return;
        }


        try {

            /*
             * Load models.
             */
            const modelData =
                await fetchJson(
                    `${baseUrl}/finder/models?brand_id=${encodeURIComponent(
                        selectedBrand
                    )}`
                );


            setOptions(
                model,
                modelData.models,
                'Select model',
                selectedModel
            );


            /*
             * Load engines only when a model
             * was selected.
             */
            if (
                selectedModel &&
                selectedModel !== '0'
            ) {

                const engineData =
                    await fetchJson(
                        `${baseUrl}/finder/engines?model_id=${encodeURIComponent(
                            selectedModel
                        )}`
                    );


                const mapped =
                    engineData.engines.map(item => {

                        return {
                            id: item.id,

                            name:
                                `${item.engine_code || 'Engine'}` +
                                `${
                                    item.displacement_cc
                                        ? ` · ${item.displacement_cc}cc`
                                        : ''
                                }` +
                                `${
                                    item.fuel_type
                                        ? ` · ${item.fuel_type}`
                                        : ''
                                }`
                        };

                    });


                setOptions(
                    engine,
                    mapped,
                    'Select engine',
                    selectedEngine
                );
            }

        } catch (error) {

            console.error(
                'Unable to restore vehicle selections:',
                error
            );
        }
    }


    /*
     * Prevent a completely empty search.
     *
     * At least ONE of the four fields should be selected.
     */
    form.addEventListener('submit', event => {

        const year =
            document.getElementById('year');


        const hasVehicleCriteria =
            brand.value ||
            model.value ||
            engine.value ||
            year.value;


        if (!hasVehicleCriteria) {

            event.preventDefault();

            alert(
                'Please select at least one vehicle detail before searching.'
            );
        }
    });


    /*
     * Add product to local cart.
     */
    document
        .querySelectorAll('.finder-add')
        .forEach(button => {

            button.addEventListener('click', () => {

                const code =
                    button.dataset.code || '';

                const name =
                    button.dataset.name || code;

                const price =
                    Number(
                        button.dataset.price || 0
                    );


                let cart = [];


                try {

                    cart = JSON.parse(
                        localStorage.getItem(
                            'autopartflow_cart'
                        ) || '[]'
                    );

                } catch (_) {

                    cart = [];
                }


                const existing =
                    cart.find(
                        item => item.code === code
                    );


                if (existing) {

                    existing.qty =
                        Math.min(
                            99,
                            Number(existing.qty || 1) + 1
                        );

                } else {

                    cart.push({
                        code,
                        name,
                        price,
                        qty: 1
                    });
                }


                localStorage.setItem(
                    'autopartflow_cart',
                    JSON.stringify(cart)
                );


                /*
                 * Update cart badge.
                 */
                const badge =
                    document.querySelector(
                        '.cart-count'
                    );


                if (badge) {

                    badge.textContent =
                        String(
                            cart.reduce(
                                (sum, item) =>
                                    sum +
                                    Number(item.qty || 0),
                                0
                            )
                        );
                }


                /*
                 * Button feedback.
                 */
                button.textContent = 'Added';


                setTimeout(() => {

                    button.innerHTML =
                        '<span class="material-symbols-outlined">' +
                        'add_shopping_cart' +
                        '</span> Add';

                }, 900);

            });

        });


    /*
     * Start initialization.
     */
    initializeSelections();

});