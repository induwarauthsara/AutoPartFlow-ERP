<?php

$brands = $brands ?? [];
$results = $results ?? [];
$selection = $selection ?? null;

$brandId = (int) ($brandId ?? 0);
$modelId = (int) ($modelId ?? 0);
$engineId = (int) ($engineId ?? 0);
$year = (int) ($year ?? 0);

$error = $error ?? null;
$hasSearchCriteria = $hasSearchCriteria ?? false;
?>

<div class="finder-page" id="finderPage">

    <div
        class="finder-container"
        data-selected-model="<?= (int) $modelId ?>"
        data-selected-engine="<?= (int) $engineId ?>"
    >

        <!-- Breadcrumb -->
        <div class="breadcrumb">

            <a href="<?= url() ?>">
                Home
            </a>

            <span class="material-symbols-outlined">
                chevron_right
            </span>

            <strong>
                Spare Part Finder
            </strong>

        </div>


        <!-- Hero -->
        <section class="finder-hero">

            <div>

                <span class="finder-eyebrow">
                    VEHICLE-BASED SEARCH
                </span>

                <h1>
                    Find the right spare parts for your vehicle
                </h1>

                <p>
                    Select any vehicle details you know.
                    You do not need to fill all four fields.
                    More details give more specific results.
                </p>

            </div>

            <span class="material-symbols-outlined finder-hero-icon">
                directions_car
            </span>

        </section>


        <!-- Search form -->
        <section class="finder-card">

            <div class="finder-card-header">

                <div>

                    <h2>
                        Vehicle Selection
                    </h2>

                    <p>
                        Select one or more fields to find compatible spare parts.
                    </p>

                </div>

                <span class="finder-step">
                    01
                </span>

            </div>


            <form
                method="get"
                action="<?= url('finder') ?>"
                id="finderForm"
                class="finder-form"
            >

                <!-- Make -->
                <div class="finder-field">

                    <label for="brand_id">
                        Make
                    </label>

                    <select
                        id="brand_id"
                        name="brand_id"
                    >

                        <option value="">
                            Select make
                        </option>

                        <?php foreach ($brands as $brand): ?>

                            <option
                                value="<?= (int) $brand['id'] ?>"
                                <?= $brandId === (int) $brand['id'] ? 'selected' : '' ?>
                            >
                                <?= e($brand['name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Model -->
                <div class="finder-field">

                    <label for="model_id">
                        Model
                    </label>

                    <select
                        id="model_id"
                        name="model_id"
                        <?= $brandId ? '' : 'disabled' ?>
                    >

                        <option value="">
                            Select model
                        </option>

                    </select>

                </div>


                <!-- Engine -->
                <div class="finder-field">

                    <label for="engine_id">
                        Engine
                    </label>

                    <select
                        id="engine_id"
                        name="engine_id"
                        <?= $modelId ? '' : 'disabled' ?>
                    >

                        <option value="">
                            Select engine
                        </option>

                    </select>

                </div>


                <!-- Year -->
                <div class="finder-field">

                    <label for="year">
                        Year
                    </label>

                    <input
                        id="year"
                        name="year"
                        type="number"
                        min="1900"
                        max="<?= (int) date('Y') + 1 ?>"
                        value="<?= $year > 0 ? $year : '' ?>"
                        placeholder="e.g. 2018"
                    >

                </div>


                <!-- Submit -->
                <button
                    class="finder-submit"
                    type="submit"
                >

                    <span class="material-symbols-outlined">
                        search
                    </span>

                    Find Compatible Parts

                </button>

            </form>


            <!-- Empty search message -->
            <?php if (!$hasSearchCriteria && !$error): ?>

                <div class="finder-alert">

                    <span class="material-symbols-outlined">
                        info
                    </span>

                    Select at least one vehicle detail to find compatible spare parts.

                </div>

            <?php endif; ?>

        </section>


        <!-- Error -->
        <?php if ($error): ?>

            <div class="finder-alert finder-alert--error">

                <span class="material-symbols-outlined">
                    error
                </span>

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- Results -->
        <?php if ($hasSearchCriteria && !$error): ?>

            <section class="finder-results-section">

                <div class="finder-results-header">

                    <div>

                        <span class="finder-eyebrow">
                            COMPATIBLE PARTS
                        </span>


                        <h2>

                            <?php if (!empty($selection['brand_name'])): ?>

                                <?= e($selection['brand_name']) ?>

                            <?php endif; ?>


                            <?php if (!empty($selection['model_name'])): ?>

                                <?= e($selection['model_name']) ?>

                            <?php endif; ?>


                            <?php if (
                                empty($selection['brand_name']) &&
                                empty($selection['model_name'])
                            ): ?>

                                Compatible Spare Parts

                            <?php endif; ?>

                        </h2>


                        <p>

                            <?php if (!empty($selection['engine_code'])): ?>

                                <?= e($selection['engine_code']) ?>

                            <?php endif; ?>


                            <?php if ($year > 0): ?>

                                <?= !empty($selection['engine_code']) ? ' · ' : '' ?>

                                Model year <?= (int) $year ?>

                            <?php endif; ?>


                            <?php if (
                                empty($selection['engine_code']) &&
                                $year === 0
                            ): ?>

                                Based on your selected vehicle details

                            <?php endif; ?>

                        </p>

                    </div>


                    <span class="finder-result-count">

                        <?= count($results) ?>

                        part<?= count($results) === 1 ? '' : 's' ?>

                    </span>

                </div>


                <!-- No results -->
                <?php if (!$results): ?>

                    <div class="finder-empty">

                        <span class="material-symbols-outlined">
                            search_off
                        </span>

                        <h3>
                            No compatible parts found
                        </h3>

                        <p>
                            No active compatibility record matches
                            the selected vehicle details.
                        </p>

                    </div>


                <?php else: ?>


                    <!-- Product grid -->
                    <div class="finder-grid">

                        <?php foreach ($results as $product): ?>

                            <?php
                            $available = max(
                                0,
                                (int) $product['quantity_on_hand']
                                -
                                (int) $product['quantity_reserved']
                            );
                            ?>

                            <article class="finder-product-card">

                                <div class="finder-product-top">

                                    <span class="finder-compatible-badge">

                                        <span class="material-symbols-outlined">
                                            verified
                                        </span>

                                        Compatible

                                    </span>


                                    <code>
                                        <?= e($product['product_code']) ?>
                                    </code>

                                </div>


                                <h3>
                                    <?= e($product['name']) ?>
                                </h3>


                                <p class="finder-product-meta">

                                    <?= e($product['brand_name'] ?: 'No brand') ?>

                                    ·

                                    <?= e($product['category_name']) ?>

                                </p>


                                <?php if (!empty($product['compatibility_notes'])): ?>

                                    <p class="finder-note">

                                        <?= e($product['compatibility_notes']) ?>

                                    </p>

                                <?php endif; ?>


                                <div class="finder-product-bottom">

                                    <div>

                                        <strong>
                                            Rs.
                                            <?= number_format(
                                                (float) $product['selling_price'],
                                                0
                                            ) ?>
                                        </strong>


                                        <span
                                            class="finder-stock <?= $available > 0
                                                ? 'finder-stock--ok'
                                                : 'finder-stock--out' ?>"
                                        >

                                            <?= $available > 0
                                                ? $available . ' in stock'
                                                : 'Out of stock' ?>

                                        </span>

                                    </div>


                                    <?php if ($available > 0): ?>

                                        <button
                                            type="button"
                                            class="finder-add"
                                            data-code="<?= e($product['product_code']) ?>"
                                            data-name="<?= e($product['name']) ?>"
                                            data-price="<?= e((string) $product['selling_price']) ?>"
                                        >

                                            <span class="material-symbols-outlined">
                                                add_shopping_cart
                                            </span>

                                            Add

                                        </button>

                                    <?php else: ?>

                                        <button
                                            type="button"
                                            class="finder-add"
                                            disabled
                                        >
                                            Unavailable
                                        </button>

                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </div>

</div>