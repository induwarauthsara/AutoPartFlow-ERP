<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';

$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
    $dbConfig['username'],
    $dbConfig['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$existingSalesCount = (int) $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
echo "Current sales count in database: {$existingSalesCount}\n";

if ($existingSalesCount >= 20) {
    echo "Sufficient sales records already exist. Skipping seed.\n";
    exit(0);
}

echo "Seeding realistic historical and seasonal sales dataset for 2025 & 2026...\n";

// Fetch existing products
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.deleted_at IS NULL")->fetchAll();
if (empty($products)) {
    echo "No products found. Cannot seed sales.\n";
    exit(1);
}

// Fetch existing customers
$customers = $pdo->query("SELECT id, customer_code, customer_type FROM customers WHERE deleted_at IS NULL")->fetchAll();
if (empty($customers)) {
    echo "No customers found. Cannot seed sales.\n";
    exit(1);
}

// Fetch existing sales employees
$employees = $pdo->query("SELECT id FROM employees WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);

$customerIds = array_column($customers, 'id');
$empIds = $employees ?: [1];

// Product IDs mapped by category
$prodsByCategory = [];
foreach ($products as $p) {
    $prodsByCategory[$p['category_name']][] = $p;
}

$saleInsert = $pdo->prepare(
    "INSERT INTO sales (
        invoice_number, customer_id, sales_rep_id, sale_date, sale_type, 
        payment_method, subtotal, discount_amount, tax_amount, total_amount, 
        amount_paid, change_amount, payment_status, notes, created_by, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$itemInsert = $pdo->prepare(
    "INSERT INTO sale_items (
        sale_id, product_id, quantity, unit_price, cost_price, discount_amount, tax_rate, line_total, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$paymentMethods = ['cash', 'card', 'bank_transfer', 'credit'];
$saleTypes = ['pos', 'invoice', 'credit'];
$invoiceIndex = 1000;

// Helper to generate sales for a specific date
$generateSale = function (string $dateStr, array $preferredCategories = []) use (
    &$invoiceIndex, $customerIds, $empIds, $products, $prodsByCategory, 
    $paymentMethods, $saleTypes, $saleInsert, $itemInsert, $pdo
) {
    $invoiceIndex++;
    $invNum = 'INV-' . str_pad((string) $invoiceIndex, 6, '0', STR_PAD_LEFT);
    $customerId = $customerIds[array_rand($customerIds)];
    $empId = $empIds[array_rand($empIds)];
    $payMethod = $paymentMethods[array_rand($paymentMethods)];
    $saleType = $saleTypes[array_rand($saleTypes)];
    
    // Choose 1 to 4 items
    $numItems = rand(1, 4);
    $selectedProducts = [];
    
    for ($i = 0; $i < $numItems; $i++) {
        if (!empty($preferredCategories) && rand(1, 10) <= 7) {
            $cat = $preferredCategories[array_rand($preferredCategories)];
            if (!empty($prodsByCategory[$cat])) {
                $selectedProducts[] = $prodsByCategory[$cat][array_rand($prodsByCategory[$cat])];
                continue;
            }
        }
        $selectedProducts[] = $products[array_rand($products)];
    }

    $subtotal = 0.0;
    $itemsData = [];

    foreach ($selectedProducts as $p) {
        $qty = rand(1, 4);
        $unitPrice = (float) $p['selling_price'];
        $costPrice = (float) $p['cost_price'];
        $itemDiscount = (rand(1, 10) === 1) ? round($unitPrice * 0.05 * $qty, 2) : 0.0;
        $lineTotal = round(($unitPrice * $qty) - $itemDiscount, 2);
        $subtotal += $lineTotal;

        $itemsData[] = [
            'product_id' => $p['id'],
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'cost_price' => $costPrice,
            'discount' => $itemDiscount,
            'line_total' => $lineTotal,
        ];
    }

    $discountAmount = (rand(1, 10) === 1) ? round($subtotal * 0.02, 2) : 0.0;
    $taxRate = 18.00; // 18% VAT
    $taxable = $subtotal - $discountAmount;
    $taxAmount = round($taxable * ($taxRate / 100), 2);
    $totalAmount = round($taxable + $taxAmount, 2);

    $payStatus = 'paid';
    $amountPaid = $totalAmount;
    $changeAmount = 0.0;

    if ($payMethod === 'credit') {
        $payStatus = (rand(1, 3) === 1) ? 'partial' : 'unpaid';
        $amountPaid = ($payStatus === 'partial') ? round($totalAmount * 0.4, 2) : 0.0;
    }

    $dateTimeStr = $dateStr . ' ' . sprintf('%02d:%02d:%02d', rand(8, 18), rand(0, 59), rand(0, 59));

    $saleInsert->execute([
        $invNum,
        $customerId,
        $empId,
        $dateTimeStr,
        $saleType,
        $payMethod,
        $subtotal,
        $discountAmount,
        $taxAmount,
        $totalAmount,
        $amountPaid,
        $changeAmount,
        $payStatus,
        'Seasonal retail/wholesale invoice',
        1,
        $dateTimeStr,
    ]);

    $saleId = (int) $pdo->lastInsertId();

    foreach ($itemsData as $it) {
        $itemInsert->execute([
            $saleId,
            $it['product_id'],
            $it['qty'],
            $it['unit_price'],
            $it['cost_price'],
            $it['discount'],
            $taxRate,
            $it['line_total'],
            $dateTimeStr,
        ]);
    }
};

$pdo->beginTransaction();

// 1. Year 2025 Historical Baseline (across all months)
echo "Generating 2025 historical data...\n";
for ($month = 1; $month <= 12; $month++) {
    // Monsoon peak in May-Sep: 6 sales per month, other months 3-4 sales
    $isMonsoon = ($month >= 5 && $month <= 9);
    $isSummer = ($month >= 2 && $month <= 4);
    $isFestive = ($month >= 10 && $month <= 12);

    $prefCats = [];
    if ($isMonsoon) $prefCats = ['Brakes', 'Suspension', 'Fluids'];
    elseif ($isSummer) $prefCats = ['Electrical', 'Engine Parts', 'Fluids'];
    elseif ($isFestive) $prefCats = ['Filters', 'Fluids', 'Ignition', 'Brakes'];

    $count = $isMonsoon ? 6 : ($isFestive ? 5 : 4);
    for ($k = 0; $k < $count; $k++) {
        $day = rand(1, 28);
        $dStr = sprintf('2025-%02d-%02d', $month, $day);
        $generateSale($dStr, $prefCats);
    }
}

// 2. Year 2026 Data (Jan to Sep 2026)
echo "Generating 2026 data up to current month...\n";
for ($month = 1; $month <= 9; $month++) {
    $isMonsoon = ($month >= 5 && $month <= 9);
    $isSummer = ($month >= 2 && $month <= 4);

    $prefCats = [];
    if ($isMonsoon) $prefCats = ['Brakes', 'Suspension', 'Fluids'];
    elseif ($isSummer) $prefCats = ['Electrical', 'Engine Parts', 'Fluids'];

    // 2026 has growth compared to 2025
    $count = $isMonsoon ? 8 : 5;
    $maxDay = ($month === 9) ? 27 : 28;

    for ($k = 0; $k < $count; $k++) {
        $day = rand(1, $maxDay);
        $dStr = sprintf('2026-%02d-%02d', $month, $day);
        $generateSale($dStr, $prefCats);
    }
}

// 3. Ensure Today (2026-09-27) and This Week have active sales
echo "Generating sales for today and this week...\n";
$generateSale('2026-09-27', ['Brakes', 'Fluids']);
$generateSale('2026-09-27', ['Suspension', 'Filters']);
$generateSale('2026-09-26', ['Engine Parts', 'Electrical']);
$generateSale('2026-09-25', ['Ignition', 'Fluids']);
$generateSale('2026-09-24', ['Brakes', 'Filters']);

// Update sequences table
$pdo->query("UPDATE sequences SET current_value = {$invoiceIndex} WHERE seq_type = 'invoice'");

$pdo->commit();

$finalCount = (int) $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
echo "Seeding completed successfully! Total sales in DB: {$finalCount}\n";
