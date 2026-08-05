# SmartAuto ERP — Full Entity-Relationship Diagram

Database: `smartauto_erp` · 31 tables · 5 views · MySQL 8+

---

## Legend

| Symbol | Meaning |
|--------|---------|
| **PK** | Primary Key |
| **FK** | Foreign Key |
| **UK** | Unique Key |
| `||--||` | One-to-One |
| `||--o{` | One-to-Many |
| `}o--o{` | Many-to-Many (via junction table) |

---

## Master Relationship Overview

High-level map of all 31 tables and foreign-key relationships.

```mermaid
erDiagram
    roles ||--o{ users : has
    roles ||--o{ notifications : targets
    users ||--|| employees : "is"
    users ||--o{ activity_logs : performs
    users ||--o{ notifications : receives
    users ||--o{ stock_movements : creates
    users ||--o{ purchase_orders : "creates/receives"
    users ||--o{ sales : creates
    users ||--o{ sale_returns : processes
    users ||--o{ payments : receives

    employees ||--o{ employee_attendance : logs
    employees ||--o{ employee_sales_targets : has
    employees ||--o{ shops : "assigned to"
    employees ||--o{ orders : "sales rep"
    employees ||--o{ sales : "sales rep"
    employees ||--o{ deliveries : "delivery rep"

    customers ||--|| shops : "extends (B2B)"
    customers ||--o{ orders : places
    customers ||--o{ sales : buys
    customers ||--o{ deliveries : receives
    customers ||--o{ payments : pays

    suppliers ||--o{ purchase_orders : supplies
    suppliers ||--o{ supplier_products : offers
    suppliers ||--o{ payments : "paid to"

    categories ||--o{ categories : "parent of"
    categories ||--o{ products : categorizes
    brands ||--o{ products : manufactures

    products ||--|| inventory : "stock level"
    products ||--o{ stock_movements : tracks
    products ||--o{ supplier_products : "supplied by"
    products ||--o{ product_compatibility : "fits"
    products ||--o{ purchase_order_items : "ordered in"
    products ||--o{ order_items : "ordered in"
    products ||--o{ sale_items : "sold in"
    products ||--o{ sale_return_items : "returned in"

    vehicle_brands ||--o{ vehicle_models : has
    vehicle_models ||--o{ vehicle_engines : has
    vehicle_brands ||--o{ product_compatibility : matches
    vehicle_models ||--o{ product_compatibility : matches
    vehicle_engines ||--o{ product_compatibility : matches

    purchase_orders ||--o{ purchase_order_items : contains
    orders ||--o{ order_items : contains
    orders ||--o| sales : "converts to"
    sales ||--o{ sale_items : contains
    sales ||--o{ sale_returns : "returned via"
    sale_returns ||--o{ sale_return_items : contains
    orders ||--o{ deliveries : shipped
    sales ||--o{ deliveries : shipped
```

---

## Module 1 — Authentication & User Management

```mermaid
erDiagram
    roles {
        tinyint id PK
        varchar name UK
        varchar slug UK
        varchar description
        json permissions
        timestamp created_at
        timestamp updated_at
    }

    users {
        int id PK
        tinyint role_id FK
        varchar username UK
        varchar email UK
        varchar password_hash
        varchar full_name
        varchar phone
        varchar avatar
        tinyint is_active
        datetime last_login_at
        datetime password_changed_at
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    employees {
        int id PK
        int user_id FK_UK
        varchar employee_code UK
        varchar designation
        enum department
        date hire_date
        decimal base_salary
        decimal commission_rate
        text address
        varchar emergency_contact
        varchar emergency_phone
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    employee_attendance {
        int id PK
        int employee_id FK
        date attend_date
        time check_in
        time check_out
        enum status
        varchar notes
        timestamp created_at
        timestamp updated_at
    }

    employee_sales_targets {
        int id PK
        int employee_id FK
        tinyint target_month
        smallint target_year
        decimal target_amount
        decimal achieved_amount
        timestamp created_at
        timestamp updated_at
    }

    activity_logs {
        bigint id PK
        int user_id FK
        varchar action
        varchar module
        varchar record_type
        int record_id
        text description
        varchar ip_address
        varchar user_agent
        timestamp created_at
    }

    settings {
        int id PK
        varchar setting_key UK
        text setting_value
        varchar setting_group
        varchar description
        timestamp updated_at
    }

    sequences {
        int id PK
        varchar seq_type UK
        varchar prefix
        int current_value
        tinyint pad_length
        timestamp updated_at
    }

    roles ||--o{ users : "1:N"
    users ||--|| employees : "1:1"
    employees ||--o{ employee_attendance : "1:N"
    employees ||--o{ employee_sales_targets : "1:N"
    users ||--o{ activity_logs : "1:N"
```

---

## Module 2 — Customers & Shops

```mermaid
erDiagram
    customers {
        int id PK
        varchar customer_code UK
        enum customer_type
        varchar name
        varchar contact_person
        varchar phone
        varchar email
        text address
        varchar city
        text notes
        tinyint is_active
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    shops {
        int id PK
        int customer_id FK_UK
        varchar shop_name
        varchar registration_no
        decimal credit_limit
        decimal credit_balance
        tinyint payment_terms_days
        int assigned_rep_id FK
        varchar tax_number
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    employees {
        int id PK
        int user_id FK
        varchar employee_code UK
        varchar designation
    }

    customers ||--|| shops : "1:1 B2B extension"
    employees ||--o{ shops : "assigned rep 1:N"
```

---

## Module 3 — Product Catalog & Suppliers

```mermaid
erDiagram
    categories {
        int id PK
        varchar name UK
        varchar slug UK
        text description
        int parent_id FK
        tinyint is_active
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    brands {
        int id PK
        varchar name UK
        varchar slug UK
        varchar country
        tinyint is_active
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    products {
        int id PK
        varchar product_code UK
        varchar barcode UK
        varchar name
        text description
        int category_id FK
        int brand_id FK
        varchar unit
        decimal cost_price
        decimal selling_price
        decimal wholesale_price
        decimal tax_rate
        tinyint warranty_months
        varchar image_path
        json specifications
        tinyint is_active
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    suppliers {
        int id PK
        varchar supplier_code UK
        varchar company_name
        varchar contact_person
        varchar phone
        varchar email
        text address
        varchar city
        varchar payment_terms
        decimal outstanding_balance
        tinyint is_active
        text notes
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    supplier_products {
        int id PK
        int supplier_id FK
        int product_id FK
        varchar supplier_sku
        decimal cost_price
        tinyint lead_time_days
        tinyint is_preferred
        timestamp created_at
        timestamp updated_at
    }

    categories ||--o{ categories : "self-ref parent"
    categories ||--o{ products : "1:N"
    brands ||--o{ products : "1:N optional"
    suppliers ||--o{ supplier_products : "1:N"
    products ||--o{ supplier_products : "1:N"
```

---

## Module 4 — Vehicle Compatibility

```mermaid
erDiagram
    vehicle_brands {
        int id PK
        varchar name UK
        varchar country
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    vehicle_models {
        int id PK
        int vehicle_brand_id FK
        varchar name
        varchar body_type
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    vehicle_engines {
        int id PK
        int vehicle_model_id FK
        varchar engine_code
        int displacement_cc
        enum fuel_type
        enum transmission
        smallint year_from
        smallint year_to
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    products {
        int id PK
        varchar product_code UK
        varchar name
    }

    product_compatibility {
        int id PK
        int product_id FK
        int vehicle_brand_id FK
        int vehicle_model_id FK
        int vehicle_engine_id FK
        smallint year_from
        smallint year_to
        varchar notes
        timestamp created_at
    }

    vehicle_brands ||--o{ vehicle_models : "Brand has Models"
    vehicle_models ||--o{ vehicle_engines : "Model has Engines"
    products ||--o{ product_compatibility : "Part fits Vehicles"
    vehicle_brands ||--o{ product_compatibility : "optional match"
    vehicle_models ||--o{ product_compatibility : "optional match"
    vehicle_engines ||--o{ product_compatibility : "optional match"
```

**Compatibility hierarchy:** Brand → Model → Engine → Year range

---

## Module 5 — Inventory Management

```mermaid
erDiagram
    products {
        int id PK
        varchar product_code UK
        varchar name
        decimal cost_price
        decimal selling_price
    }

    inventory {
        int id PK
        int product_id FK_UK
        int quantity_on_hand
        int quantity_reserved
        int quantity_damaged
        int reorder_level
        int reorder_quantity
        datetime last_stock_in_at
        datetime last_stock_out_at
        timestamp updated_at
    }

    stock_movements {
        bigint id PK
        int product_id FK
        enum movement_type
        int quantity
        int quantity_before
        int quantity_after
        decimal unit_cost
        varchar reference_type
        int reference_id
        varchar notes
        int created_by FK
        timestamp created_at
    }

    users {
        int id PK
        varchar username UK
    }

    products ||--|| inventory : "1:1 stock record"
    products ||--o{ stock_movements : "1:N history"
    users ||--o{ stock_movements : "created by"
```

**Movement types:** `purchase_in`, `sale_out`, `adjustment_in`, `adjustment_out`, `transfer_in`, `transfer_out`, `return_in`, `return_out`, `damaged`

---

## Module 6 — Purchase Management

```mermaid
erDiagram
    suppliers {
        int id PK
        varchar supplier_code UK
        varchar company_name
    }

    purchase_orders {
        int id PK
        varchar po_number UK
        int supplier_id FK
        date order_date
        date expected_date
        enum status
        decimal subtotal
        decimal tax_amount
        decimal discount_amount
        decimal total_amount
        decimal amount_paid
        text notes
        int created_by FK
        int received_by FK
        datetime received_at
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    purchase_order_items {
        int id PK
        int purchase_order_id FK
        int product_id FK
        int quantity_ordered
        int quantity_received
        decimal unit_cost
        decimal tax_rate
        decimal line_total
        timestamp created_at
        timestamp updated_at
    }

    products {
        int id PK
        varchar product_code UK
        varchar name
    }

    users {
        int id PK
        varchar username UK
    }

    suppliers ||--o{ purchase_orders : "1:N"
    purchase_orders ||--o{ purchase_order_items : "1:N"
    products ||--o{ purchase_order_items : "1:N"
    users ||--o{ purchase_orders : "created_by"
    users ||--o{ purchase_orders : "received_by"
```

**PO status flow:** `draft` → `pending` → `partial` → `received` | `cancelled`

---

## Module 7 — Order Management

```mermaid
erDiagram
    customers {
        int id PK
        varchar customer_code UK
        enum customer_type
        varchar name
    }

    employees {
        int id PK
        varchar employee_code UK
        varchar designation
    }

    orders {
        int id PK
        varchar order_number UK
        int customer_id FK
        int sales_rep_id FK
        datetime order_date
        enum status
        enum order_source
        decimal subtotal
        decimal discount_amount
        decimal tax_amount
        decimal total_amount
        enum payment_status
        text delivery_address
        text notes
        datetime deleted_at
        timestamp created_at
        timestamp updated_at
    }

    order_items {
        int id PK
        int order_id FK
        int product_id FK
        int quantity
        decimal unit_price
        decimal discount_amount
        decimal tax_rate
        decimal line_total
        timestamp created_at
    }

    products {
        int id PK
        varchar product_code UK
        varchar name
        decimal selling_price
    }

    customers ||--o{ orders : "1:N"
    employees ||--o{ orders : "sales rep 1:N"
    orders ||--o{ order_items : "1:N"
    products ||--o{ order_items : "1:N"
```

**Order sources:** `pos`, `shop_portal`, `rep_field`, `phone`, `walk_in`

---

## Module 8 — Sales, Returns & Invoicing

```mermaid
erDiagram
    orders {
        int id PK
        varchar order_number UK
    }

    customers {
        int id PK
        varchar customer_code UK
        varchar name
    }

    employees {
        int id PK
        varchar employee_code UK
    }

    users {
        int id PK
        varchar username UK
    }

    sales {
        int id PK
        varchar invoice_number UK
        int order_id FK
        int customer_id FK
        int sales_rep_id FK
        datetime sale_date
        enum sale_type
        enum payment_method
        decimal subtotal
        decimal discount_amount
        decimal tax_amount
        decimal total_amount
        decimal amount_paid
        decimal change_amount
        enum payment_status
        text notes
        datetime deleted_at
        int created_by FK
        timestamp created_at
        timestamp updated_at
    }

    sale_items {
        int id PK
        int sale_id FK
        int product_id FK
        int quantity
        decimal unit_price
        decimal cost_price
        decimal discount_amount
        decimal tax_rate
        decimal line_total
        timestamp created_at
    }

    sale_returns {
        int id PK
        varchar return_number UK
        int sale_id FK
        datetime return_date
        text reason
        decimal refund_amount
        enum refund_method
        enum status
        int processed_by FK
        timestamp created_at
        timestamp updated_at
    }

    sale_return_items {
        int id PK
        int sale_return_id FK
        int product_id FK
        int quantity
        decimal unit_price
        decimal line_total
        varchar condition_note
        timestamp created_at
    }

    products {
        int id PK
        varchar product_code UK
        varchar name
    }

    orders ||--o| sales : "0:1 converts"
    customers ||--o{ sales : "1:N"
    employees ||--o{ sales : "sales rep 1:N"
    users ||--o{ sales : "created by"
    sales ||--o{ sale_items : "1:N"
    products ||--o{ sale_items : "1:N"
    sales ||--o{ sale_returns : "1:N"
    sale_returns ||--o{ sale_return_items : "1:N"
    products ||--o{ sale_return_items : "1:N"
    users ||--o{ sale_returns : "processed by"
```

---

## Module 9 — Delivery, Payments & Notifications

```mermaid
erDiagram
    orders {
        int id PK
        varchar order_number UK
    }

    sales {
        int id PK
        varchar invoice_number UK
    }

    customers {
        int id PK
        varchar customer_code UK
        varchar name
    }

    employees {
        int id PK
        varchar employee_code UK
    }

    users {
        int id PK
        varchar username UK
    }

    roles {
        tinyint id PK
        varchar slug UK
    }

    suppliers {
        int id PK
        varchar supplier_code UK
    }

    deliveries {
        int id PK
        varchar delivery_number UK
        int order_id FK
        int sale_id FK
        int customer_id FK
        int delivery_rep_id FK
        enum status
        date scheduled_date
        datetime delivered_at
        text delivery_address
        varchar recipient_name
        varchar recipient_phone
        text notes
        timestamp created_at
        timestamp updated_at
    }

    payments {
        int id PK
        varchar payment_number UK
        enum payable_type
        int payable_id
        int customer_id FK
        int supplier_id FK
        datetime payment_date
        decimal amount
        enum payment_method
        varchar reference_no
        text notes
        int received_by FK
        timestamp created_at
    }

    notifications {
        bigint id PK
        int user_id FK
        tinyint role_id FK
        enum type
        varchar title
        text message
        varchar link_url
        varchar reference_type
        int reference_id
        tinyint is_read
        datetime read_at
        timestamp created_at
    }

    orders ||--o{ deliveries : "optional 1:N"
    sales ||--o{ deliveries : "optional 1:N"
    customers ||--o{ deliveries : "1:N"
    employees ||--o{ deliveries : "delivery rep 1:N"
    customers ||--o{ payments : "1:N optional"
    suppliers ||--o{ payments : "1:N optional"
    users ||--o{ payments : "received by"
    users ||--o{ notifications : "1:N"
    roles ||--o{ notifications : "broadcast 1:N"
```

**Payments polymorphic reference:** `payable_type` + `payable_id` → `sale`, `purchase_order`, `customer_credit`, `supplier`

---

## Complete Business Workflow Diagram

End-to-end operational flow across modules.

```mermaid
flowchart LR
    subgraph SUPPLY["Purchasing"]
        SUP[suppliers]
        PO[purchase_orders]
        POI[purchase_order_items]
        SUP --> PO --> POI
    end

    subgraph CATALOG["Catalog"]
        CAT[categories]
        BR[brands]
        PRD[products]
        CAT --> PRD
        BR --> PRD
    end

    subgraph VEHICLE["Compatibility"]
        VB[vehicle_brands]
        VM[vehicle_models]
        VE[vehicle_engines]
        PC[product_compatibility]
        VB --> VM --> VE
        PRD --> PC
        VB --> PC
        VM --> PC
        VE --> PC
    end

    subgraph STOCK["Inventory"]
        INV[inventory]
        SM[stock_movements]
        PRD --> INV
        PRD --> SM
    end

    subgraph SALES_FLOW["Sales Pipeline"]
        CUS[customers]
        SH[shops]
        ORD[orders]
        ORI[order_items]
        SAL[sales]
        SI[sale_items]
        CUS --> SH
        CUS --> ORD --> ORI
        ORD --> SAL --> SI
    end

    subgraph FULFILL["Fulfillment"]
        DEL[deliveries]
        PAY[payments]
        ORD --> DEL
        SAL --> DEL
        SAL --> PAY
        PO --> PAY
    end

    POI -->|"goods received"| SM
    ORI --> PRD
    SI --> PRD
    SI -->|"stock deducted"| SM
```

---

## Relationship Summary Table

| Parent Entity | Child Entity | Cardinality | FK Column |
|---------------|--------------|-------------|-----------|
| roles | users | 1:N | users.role_id |
| users | employees | 1:1 | employees.user_id |
| employees | employee_attendance | 1:N | employee_attendance.employee_id |
| employees | employee_sales_targets | 1:N | employee_sales_targets.employee_id |
| users | activity_logs | 1:N | activity_logs.user_id |
| customers | shops | 1:1 | shops.customer_id |
| employees | shops | 1:N | shops.assigned_rep_id |
| categories | categories | 1:N | categories.parent_id |
| categories | products | 1:N | products.category_id |
| brands | products | 1:N | products.brand_id |
| suppliers | supplier_products | 1:N | supplier_products.supplier_id |
| products | supplier_products | 1:N | supplier_products.product_id |
| vehicle_brands | vehicle_models | 1:N | vehicle_models.vehicle_brand_id |
| vehicle_models | vehicle_engines | 1:N | vehicle_engines.vehicle_model_id |
| products | product_compatibility | 1:N | product_compatibility.product_id |
| vehicle_brands | product_compatibility | 1:N | product_compatibility.vehicle_brand_id |
| vehicle_models | product_compatibility | 1:N | product_compatibility.vehicle_model_id |
| vehicle_engines | product_compatibility | 1:N | product_compatibility.vehicle_engine_id |
| products | inventory | 1:1 | inventory.product_id |
| products | stock_movements | 1:N | stock_movements.product_id |
| users | stock_movements | 1:N | stock_movements.created_by |
| suppliers | purchase_orders | 1:N | purchase_orders.supplier_id |
| purchase_orders | purchase_order_items | 1:N | purchase_order_items.purchase_order_id |
| products | purchase_order_items | 1:N | purchase_order_items.product_id |
| customers | orders | 1:N | orders.customer_id |
| employees | orders | 1:N | orders.sales_rep_id |
| orders | order_items | 1:N | order_items.order_id |
| products | order_items | 1:N | order_items.product_id |
| orders | sales | 1:0..1 | sales.order_id |
| customers | sales | 1:N | sales.customer_id |
| employees | sales | 1:N | sales.sales_rep_id |
| sales | sale_items | 1:N | sale_items.sale_id |
| products | sale_items | 1:N | sale_items.product_id |
| sales | sale_returns | 1:N | sale_returns.sale_id |
| sale_returns | sale_return_items | 1:N | sale_return_items.sale_return_id |
| orders | deliveries | 1:N | deliveries.order_id |
| sales | deliveries | 1:N | deliveries.sale_id |
| customers | deliveries | 1:N | deliveries.customer_id |
| employees | deliveries | 1:N | deliveries.delivery_rep_id |
| customers | payments | 1:N | payments.customer_id |
| suppliers | payments | 1:N | payments.supplier_id |
| users | notifications | 1:N | notifications.user_id |
| roles | notifications | 1:N | notifications.role_id |

**Standalone tables (no FKs):** `settings`, `sequences`

---

## Database Views (Read-Only)

| View | Purpose | Key Joins |
|------|---------|-----------|
| v_low_stock_products | Low-stock alerts | inventory → products → categories |
| v_product_stock | Stock valuation | products → inventory → categories → brands |
| v_daily_sales_summary | Daily revenue | sales (aggregated) |
| v_employee_sales_performance | Rep KPIs | employees → users → sales |
| v_vehicle_parts_finder | Public parts search | product_compatibility → products → vehicles → inventory |

---

## Entity Count by Domain

| Domain | Tables |
|--------|--------|
| Auth & Users | 7 (roles, users, employees, attendance, targets, activity_logs, + settings, sequences) |
| Customers | 2 (customers, shops) |
| Catalog | 5 (categories, brands, products, suppliers, supplier_products) |
| Vehicle | 4 (vehicle_brands, models, engines, product_compatibility) |
| Inventory | 2 (inventory, stock_movements) |
| Purchasing | 2 (purchase_orders, purchase_order_items) |
| Orders | 2 (orders, order_items) |
| Sales | 4 (sales, sale_items, sale_returns, sale_return_items) |
| Fulfillment | 3 (deliveries, payments, notifications) |
| **Total** | **31 tables + 5 views** |

---

*Generated from `database/schema.sql` — SmartAuto ERP*
