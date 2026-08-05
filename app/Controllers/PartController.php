<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Part;

class PartController extends Controller
{
    private Part $partModel;

    public function __construct()
    {
        $this->partModel = new Part();
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
            'title' => 'Add New Part',
            'flash' => $this->getFlash(),
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
            'title' => 'Edit Part',
            'part'  => $part,
            'flash' => $this->getFlash(),
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
        $partNumber = trim((string) $this->input('part_number', ''));
        $name       = trim((string) $this->input('name', ''));
        $category   = trim((string) $this->input('category', ''));
        $price      = $this->input('price', '');
        $quantity   = $this->input('quantity', '');

        if ($partNumber === '' || $name === '' || $category === '') {
            $this->setFlash('error', 'Part number, name, and category are required.');
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
            'part_number' => $partNumber,
            'name'        => $name,
            'category'    => $category,
            'price'       => number_format((float) $price, 2, '.', ''),
            'quantity'    => (int) $quantity,
        ];
    }
}
