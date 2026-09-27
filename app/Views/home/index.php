<link rel="stylesheet" href="<?= asset('css/public/home.css') ?>">
<div class="customer-home">
<section class="customer-hero">
<div><span class="customer-eyebrow">AutoPartFlow · Spare parts</span><h1>The right part.<br>Back on the road.</h1>
<p>Browse spare parts, check vehicle compatibility, and place your order seamlessly.</p>
<div class="customer-links">
    <a class="customer-link customer-link--primary" href="<?= url('catalog') ?>">Browse parts</a>
    <a class="customer-link" href="<?= url('finder') ?>">Spare Parts Finder</a>
</div></div>
<img src="<?= asset('images/warehouse.jpg') ?>" alt="Automotive parts warehouse" width="600" height="360">
</section>
<section class="customer-services" aria-label="Shop with AutoPartFlow">
<article><h2>Find your part</h2><p>Filter parts precisely by vehicle make, model, year, and engine type.</p><a href="<?= url('finder') ?>">Use Spare Parts Finder</a></article>
<article><h2>Check the fit</h2><p>Open a part in the catalog to review its details and compatible vehicles before ordering.</p><a href="<?= url('catalog') ?>">Explore the catalog</a></article>
<article><h2>Fast Delivery</h2><p>Prompt dispatch and reliable delivery directly to your garage or doorstep.</p><a href="<?= url('track-order') ?>">Track an order</a></article>
</section></div>
