<link rel="stylesheet" href="<?= asset('css/public/home.css') ?>">
<div class="customer-home">
<section class="customer-hero">
<div><span class="customer-eyebrow">AutoPartFlow · Spare parts</span><h1>The right part.<br>Back on the road.</h1>
<p>Browse spare parts, check vehicle compatibility, and place your order seamlessly in one place.</p>
<div class="customer-links">
    <a class="customer-link customer-link--primary" href="<?= url('catalog') ?>">Browse Catalog & Parts Finder</a>
</div></div>
<img src="<?= asset('images/warehouse.jpg') ?>" alt="Automotive parts warehouse" width="600" height="360">
</section>
<section class="customer-services" aria-label="Shop with AutoPartFlow">
<article><h2>Find by Vehicle</h2><p>Filter parts precisely by vehicle make, model, year, and engine type directly in the catalog.</p><a href="<?= url('catalog') ?>">Find Vehicle Parts</a></article>
<article><h2>Check the Fit</h2><p>Open any part to review technical specs, OEM codes, and verified compatible vehicles.</p><a href="<?= url('catalog') ?>">Explore the Catalog</a></article>
<article><h2>Fast Delivery</h2><p>Prompt dispatch and reliable delivery directly to your garage or doorstep.</p><a href="<?= url('track-order') ?>">Track an order</a></article>
</section></div>
