<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales POS') ?> | SmartAuto ERP</title>
    <link rel="stylesheet" href="<?= asset('css/sales/pos.css') ?>">
</head>
<body class="pos-app">

    <?= $content ?>

    <?php require APP_PATH . '/Views/sales/pos/_icons.php'; ?>

    <!--
        PHP integration (wire in your PosController):
        - GET  /api/products?q=       → replace MOCK_PRODUCTS in mock-data.js
        - POST /api/sales             → confirmSale() in pos.js
        - GET  /api/customers/trade   → populate #trade-account options
    -->
    <script src="<?= asset('js/sales/mock-data.js') ?>"></script>
    <script src="<?= asset('js/sales/pos.js') ?>"></script>
</body>
</html>
