<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Part;

class PartController extends Controller
{
    private Part $partModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->partModel = new Part();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $this->view('parts/index', [
            'title' => 'Parts Inventory',
            'parts' => $this->partModel->findAll(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        $this->view('parts/create', [
            'title'      => 'Add New Part',
            'categories' => $this->categoryModel->findAllActive(),
            'flash'      => $this->getFlash(),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !verify_csrf()) {
            $this->setFlash('error', 'Invalid request.');
            $this->redirect('/parts/create');
        }

        $data = $this->validatePartInput();

        if ($data === null) {
            $this->redirect('/parts/create');
        }

        if ($this->partModel->productCodeExists($data['product_code'])) {
            $this->setFlash('error', 'That product code is already in use.');
            $this->redirect('/parts/create');
        }

        $this->partModel->create($data);
        $this->setFlash('success', 'Part added successfully.');
        $this->redirect('/parts');
    }

    public function edit(string $id): void
    {
        $part = $this->partModel->findById((int) $id);

        if ($part === null) {
            $this->setFlash('error', 'Part not found.');
            $this->redirect('/parts');
        }

        $this->view('parts/edit', [
            'title'      => 'Edit Part',
            'part'       => $part,
            'categories' => $this->categoryModel->findAllActive(),
            'flash'      => $this->getFlash(),
        ]);
    }

    public function update(string $id): void
    {
        if (!$this->isPost() || !verify_csrf()) {
            $this->setFlash('error', 'Invalid request.');
            $this->redirect('/parts/edit/' . $id);
        }

        $part = $this->partModel->findById((int) $id);

        if ($part === null) {
            $this->setFlash('error', 'Part not found.');
            $this->redirect('/parts');
        }

        $data = $this->validatePartInput();

        if ($data === null) {
            $this->redirect('/parts/edit/' . $id);
        }

        if ($this->partModel->productCodeExists($data['product_code'], (int) $id)) {
            $this->setFlash('error', 'That product code is already in use.');
            $this->redirect('/parts/edit/' . $id);
        }

        $this->partModel->updatePart((int) $id, $data);
        $this->setFlash('success', 'Part updated successfully.');
        $this->redirect('/parts');
    }

    public function delete(string $id): void
    {
        if (!$this->isPost() || !verify_csrf()) {
            $this->setFlash('error', 'Invalid request.');
            $this->redirect('/parts');
        }

        $this->partModel->delete((int) $id);
        $this->setFlash('success', 'Part deleted successfully.');
        $this->redirect('/parts');
    }

    private function validatePartInput(): ?array
    {
        $productCode = trim((string) $this->input('product_code', ''));
        $name        = trim((string) $this->input('name', ''));
        $categoryId  = $this->input('category_id', '');
        $price       = $this->input('selling_price', '');
        $quantity    = $this->input('quantity_on_hand', '');

        if ($productCode === '' || $name === '' || $categoryId === '') {
            $this->setFlash('error', 'Product code, name, and category are required.');
            return null;
        }

        if (!ctype_digit((string) $categoryId) || (int) $categoryId < 1) {
            $this->setFlash('error', 'Please select a valid category.');
            return null;
        }

        if (!is_numeric($price) || (float) $price < 0) {
            $this->setFlash('error', 'Price must be a valid number.');
            return null;
        }

        if (!ctype_digit((string) $quantity) || (int) $quantity < 0) {
            $this->setFlash('error', 'Quantity must be a valid whole number.');
            return null;
        }

        return [
            'product_code'     => $productCode,
            'name'             => $name,
            'category_id'      => (int) $categoryId,
            'selling_price'    => number_format((float) $price, 2, '.', ''),
            'quantity_on_hand' => (int) $quantity,
        ];
    }
}
