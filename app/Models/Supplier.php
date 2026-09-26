<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Supplier extends Model
{
    protected string $table = 'suppliers';

    public function allWithSummary(): array
    {
        $sql = "
            SELECT
                s.id,
                s.supplier_code,
                s.company_name,
                s.contact_person,
                s.phone,
                s.email,
                s.city,
                s.payment_terms,
                s.outstanding_balance,
                s.is_active,
                COUNT(DISTINCT poi.product_id) AS key_products,
                COALESCE(SUM(
                    CASE
                        WHEN po.id IS NOT NULL AND YEAR(po.order_date) = YEAR(CURDATE())
                        THEN poi.line_total
                        ELSE 0
                    END
                ), 0) AS ytd_purchases
            FROM suppliers s
            LEFT JOIN purchase_orders po
                ON po.supplier_id = s.id
               AND po.deleted_at IS NULL
               AND po.status <> 'cancelled'
            LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
            WHERE s.deleted_at IS NULL
            GROUP BY
                s.id, s.supplier_code, s.company_name, s.contact_person,
                s.phone, s.email, s.city, s.payment_terms,
                s.outstanding_balance, s.is_active
            ORDER BY s.company_name ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function summary(): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total_suppliers,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_suppliers,
                COALESCE(SUM(outstanding_balance), 0) AS outstanding_payables
             FROM suppliers
             WHERE deleted_at IS NULL"
        )->fetch() ?: [];

        $activeOrders = $this->db->query(
            "SELECT COUNT(*)
             FROM purchase_orders
             WHERE deleted_at IS NULL AND status IN ('pending', 'partial')"
        )->fetchColumn();

        return [
            'totalSuppliers'      => (int) ($row['total_suppliers'] ?? 0),
            'activeSuppliers'     => (int) ($row['active_suppliers'] ?? 0),
            'activePurchaseOrders' => (int) $activeOrders,
            'outstandingPayables' => (float) ($row['outstanding_payables'] ?? 0),
        ];
    }

    public function create(array $data): array
    {
        $companyName = trim((string) ($data['company_name'] ?? ''));
        $contactPerson = trim((string) ($data['contact_person'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $paymentTerms = trim((string) ($data['payment_terms'] ?? 'Net 30'));
        $address = trim((string) ($data['address'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));

        if ($companyName === '') {
            throw new \InvalidArgumentException('Company name is required.');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A valid email address is required.');
        }
        if ($phone === '') {
            throw new \InvalidArgumentException('Phone number is required.');
        }
        if ($paymentTerms === '') {
            $paymentTerms = 'Net 30';
        }

        $code = 'SUP-' . str_pad((string) (((int) $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM suppliers')->fetchColumn())), 3, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare(
            "INSERT INTO suppliers (
                supplier_code, company_name, contact_person, phone, email,
                address, city, payment_terms, notes
             ) VALUES (
                :supplier_code, :company_name, :contact_person, :phone, :email,
                :address, :city, :payment_terms, :notes
             )"
        );
        $stmt->execute([
            'supplier_code' => $code,
            'company_name' => $companyName,
            'contact_person' => $contactPerson !== '' ? $contactPerson : null,
            'phone' => $phone,
            'email' => $email,
            'address' => $address !== '' ? $address : null,
            'city' => $city !== '' ? $city : null,
            'payment_terms' => $paymentTerms,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        $id = (int) $this->db->lastInsertId();
        $row = $this->db->prepare(
            "SELECT id, supplier_code, company_name, contact_person, phone, email,
                    city, payment_terms, outstanding_balance, is_active,
                    0 AS key_products, 0 AS ytd_purchases
             FROM suppliers WHERE id = :id"
        );
        $row->execute(['id' => $id]);

        return $row->fetch() ?: throw new \RuntimeException('Supplier was created but could not be loaded.');
    }
}
