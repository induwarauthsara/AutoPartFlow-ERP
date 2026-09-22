<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class CatalogController extends Controller
{
    public function index(): void
    {
        $productModel = new Product();

        $selectedCategories = $this->input('category', []);
        $selectedBrands = $this->input('brand', []);

        $selectedCategories = is_array($selectedCategories) ? $selectedCategories : [$selectedCategories];
        $selectedBrands = is_array($selectedBrands) ? $selectedBrands : [$selectedBrands];

        $filters = [
            'search'     => trim((string) $this->input('q', '')),
            'categories' => array_values(array_filter($selectedCategories)),
            'brands'     => array_values(array_filter($selectedBrands)),
            'sort'       => (string) $this->input('sort', 'relevance'),
        ];

        $products = $productModel->catalog($filters);

        // Product Catalog image fallback map
        $imageMap = [
            'PRD-00001' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ayr4--XmcXIdj2_0j20NXNhwrNFVFHPhCw6RsqQzlx5S9U0D7O2rVc9ojrLLKdEKNRs_zVwHUqXJ27qp0gKJyX_jCNLzmZ5uNGoqmVVexNpeSpo7V6sga3SXzMhmANQkAmz2OjyAo-Dff3zk1sshDzp6foTQUI_o3PWFatdXSTJ6xV7rRlLA8AJj4o5ePetMhF_M02G8T_r04Hln6nW8W11Z-uQr_sbIIhz9GsTWqTzK_eJWRUk3IQ',
            'PRD-00002' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDXVa7PI7qpTpWwjgiCBwpq8ISA56sfTdR6HuO9PHQnt54ZoUHSSbDb3LknhC7yy_nJZgMspkICqPGjvbb4Pp0b_1yODnIhsnuB8rDnkJCm2sR516fd9O9b7syPNg0FxtsXpKH3yyiwigE59xh_MojH9cNCCOyzREH4v80JbGmmr3zCGZq7rj6ZZHJ5-PBTitAe7zb37bbkmOzlf_6V2i4OFKnXBMQEFFHZlPN9sDBpu-mfJBIxkG9Y9g',
            'PRD-00003' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDbNtYLS-IoL-F11aS1DyKBafmPcMgfW4tiNId1TA526e0jZHa5T43u3hk0hQYCoOPb16i2_2iIXRpcs_pL1dwOqWfb9S98LcLbXg9xMlZCg_sapW6i9xm6kRlazc3l5_OSJw_qRKQL-mV_Cy2pGxzSy6jo97qQe0F_am0tjT8Wm8xGhCiuEs11JMgUwwwizmSgen-MZfkzNUTa_OSDuphOigU7itdLhvTr1v-nVO6Ksi1g2pVNGVIymA',
            'PRD-00004' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDp1g1Jjfz_qJpnd44qiiNhUd-JxBd0WpkvzZIlluvXVLDIAFFXJNX-heHxAvAiQbo9WQsPS0fPW6FuW6b2rLw-839uh_ScmLlbK19JvReOfXmVLh7nuQrJdhuOamhwnmEZ1XEf4r01b5akqxzLcmhDmt0gB_ypQAo-EKOlykWUQP0AXMqJIdrqT6r-4quCr6EgfVBkPHmwuMwomLxTGuGaY-Q2h5K9PA5hG2ow1VEoEOSLnuzb4DweDA',
            'PRD-00005' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBDARRJpu8-yk_D7G5BU-lm8TOrhf_WsDfk91WFhOHxaWdBYJoDvCGxHu505DRZLBE1Dzw946jk5UOz8hn1QmTRD54LpGCjMvv0xZi86BIP5rF14-xGYxVVxcVA0LTZQfl_zI6SHAxZZ1KJdajLLVLtmJsPlLm9ZFq1nHHbNAwwrhbHzYxRXZn54op1dWWakqJlTo-MqNmCHShHIf39M3XfQDqBHGLbCtevRzUItz90QinjTJw2CUw90g',
            'PRD-00006' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=400&q=80',
            'PRD-00007' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=400&q=80',
            'PRD-00008' => 'https://images.unsplash.com/photo-1615906655593-ad0386982a0f?auto=format&fit=crop&w=400&q=80',
        ];

        foreach ($products as &$product) {
            $product['display_image'] = !empty($product['image_path']) ? $product['image_path'] : ($imageMap[$product['product_code']] ?? '');
            $qty = (int) ($product['quantity_on_hand'] ?? 0);
            $reorder = (int) ($product['reorder_level'] ?? 0);
            $product['stock_status'] = $qty <= 0 ? 'Out of Stock' : ($qty <= $reorder ? 'Low Stock' : 'In Stock');
        }
        unset($product);

        $this->view('catalog/index', [
            'title' => 'Product Catalog | AutoPartFlow',
            'products' => $products,
            'categories' => $productModel->categories(),
            'brands' => $productModel->brands(),
            'filters' => $filters,
        ], 'public');
    }

    public function compatibility(): void
    {
        $productId = (int) $this->input('product_id', 0);
        $productCode = (string) $this->input('product_code', '');
        $productModel = new Product();

        if ($productId === 0 && !empty($productCode)) {
            $product = $productModel->findByCode($productCode);
            if ($product) {
                $productId = (int) $product['id'];
            }
        }

        if ($productId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid product identifier.'], 400);
            return;
        }

        $compatibilities = $productModel->getCompatibility($productId);
        $this->json([
            'status' => 'success',
            'product_id' => $productId,
            'compatibility' => $compatibilities,
        ]);
    }

    public function details(): void
    {
        $code = trim((string) $this->input('code', ''));
        if (empty($code)) {
            $this->json(['status' => 'error', 'message' => 'Product code is required.'], 400);
            return;
        }

        $productModel = new Product();
        $product = $productModel->findByCode($code);

        if (!$product) {
            $this->json(['status' => 'error', 'message' => 'Product not found.'], 404);
            return;
        }

        $qty = (int) ($product['quantity_on_hand'] ?? 0);
        $reorder = (int) ($product['reorder_level'] ?? 0);
        $product['stock_status'] = $qty <= 0 ? 'Out of Stock' : ($qty <= $reorder ? 'Low Stock' : 'In Stock');
        $compatibilities = $productModel->getCompatibility((int) $product['id']);
        $product['compatibility'] = $compatibilities;

        $this->json([
            'status' => 'success',
            'product' => $product,
        ]);
    }
}
