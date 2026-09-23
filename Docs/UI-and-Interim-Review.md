# UI consistency and interim review

Reviewed 23 September 2026 against `.cursor/WorkLoad Distribution.txt` and the current working tree, including the existing uncommitted sales-rep migration.

## Scope and result

The changes in this review are presentation changes: the existing logo (`public/assets/images/logo-icon.png`), sales navy (`#002045`), Inter typography, shared focus/field styles, admin navigation/icons/charts, customer homepage, and responsive layouts. Inventory's references to deleted sales assets were replaced with the existing sales-rep styles and shell behavior. Customer navigation no longer promotes staff tools; stock navigation no longer presents sales tools. Existing backend controllers, models, routes, database schema and authentication behavior were not changed.

Role-specific menus are presentation only. They do not implement authorization. The authentication and role-routing defects below still prevent a fully role-based system.

## Interim requirements

1. **Login and sign-up for all users: NOT COMPLETE.** Login handlers exist, but the main login form submits `username` while `HomeController::doLogin()` reads `email`. No registration route, handler or registration screen exists. `homeRouteForRole()` sends every non-owner to sales, including store managers and customers. Admin has a separate owner-only login handler. A successful login for every role was not demonstrated.
2. **All finalized UIs implemented and navigable: PARTIAL / NOT COMPLETE against the assigned scope.** The repository contains a customer homepage, catalog, checkout, tracking, two login screens, five sales screens, one inventory screen and six admin screens. Their routes exist, and this pass makes existing navigation consistent. Many assigned screens are absent; numerous controls remain inert or operate only on mock data. A styled screen is not evidence that its workflow is complete. There is no separate finalized-screen acceptance list in the repository, so sign-off against that list remains necessary.
3. **Each member implements Create, Read, Update and Delete for an entity: NOT COMPLETE.** No complete persisted, routed four-operation entity workflow was found for any member. Client-side array updates, a database schema, and unused generic model methods are not sufficient evidence of end-to-end CRUD.

### CRUD evidence by member

- **Induwara — customers / sales orders:** customer and order UI contains mock reads, edits and deletions; POS and order creation are frontend demonstrations. `SalesController` only renders GET pages. No customer or sales Create/Update/Delete endpoints are registered. The shared public order model implements creation/read, but not routed update/delete.
- **Sashik — products / inventory:** `Product` implements catalog/detail/compatibility reads. Inventory reads and stock-in edits manipulate an in-memory mock array. No product or stock Create/Update/Delete routes exist. An “Edit stock” button opens the stock-in form and adds quantity; it is not a complete stock-adjustment workflow.
- **Chathumi — users / employees:** controllers read database records. Add User opens a placeholder, edit links are `#`, and employee creation is an unconnected button. No entity Create/Update/Delete routes exist.
- **Varshika — customer orders / deliveries:** public checkout has a create endpoint and order tracking has a read endpoint. No customer-order update/cancel/delete or delivery-assignment/status-update endpoints are registered. Creation was inspected in code, not executed against the user's database.

## Backend and integration findings — deliberately not fixed

These are findings from the reviewed code and checks, not a claim that every possible defect has been discovered. Primary ownership follows the workload file; cross-module partners are named explicitly.

### Member 1: Induwara — Sales & Customer Operations

1. **Sales screens are disconnected from persistent data.** `app/Controllers/SalesController.php`, `public/assets/js/sales-rep/mock-data.js`, `customers.js`, `orders.js`, `orders-create.js`, and `pos.js`: sales, customers, order statuses, payments and invoices are not persisted to PHP/MySQL. New-order drafts use local storage but are not a server-side order lifecycle. Refreshing/reopening screens does not provide a shared source of truth.
2. **Shared order processing accepts invalid product fallbacks.** `app/Models/Order.php::createOrderWithItems()` uses client-supplied price and product ID 1 when the submitted product code is unknown. Rejecting invalid items and pricing from trusted records needs backend work. Integration partner: Varshika.
3. **No stock availability/reservation check in order creation.** The order transaction does not lock/check/decrement available inventory or create stock movements. Quantity is coerced to at least one rather than rejecting invalid values. Integration partner: Sashik.
4. **Customer matching and code generation are incomplete.** `Order::getOrCreateCustomer()` matches by phone alone, does not link the authenticated shop account, and generates codes using `MAX(id)+1`, which can collide under concurrent orders. The order flow always creates new customers as `walking`, including shop-portal orders.
5. **Payment recording is missing.** Public order creation stores a payment-method note and always marks payment unpaid; no payment gateway, payment record or settlement workflow is invoked. Sales POS payment entry is mock-only. Integration partner: Varshika.
6. **Order-number fallback weakens uniqueness guarantees.** `getNextOrderNumber()` falls back to a random number if sequence handling fails; retries/collision handling are absent. Sequence errors are swallowed.
7. **Order errors disclose internals.** `createOrderWithItems()` returns the raw database exception in its error message. Input item shape/size validation and robust transaction failure handling also need review.
8. **Statement sending is a placeholder.** `customers.js` displays an integration toast rather than generating or sending a customer statement.

### Member 2: Sashik — Inventory & Procurement

1. **Confirmed catalog search failure.** `app/Models/Product.php::catalog()` repeats the same `:search` named parameter in several predicates, while `app/Core/Database.php` disables emulated prepares. A read-only request to `/catalog?q=brake` reproduced `SQLSTATE[HY093]: Invalid parameter number`. Integration partner: Varshika.
2. **Inventory changes are only mock changes.** `public/assets/js/inventory/inventory.js` changes `INVENTORY_MOCK_DATA`; it does not persist stock, location, supplier receipts or stock movements. Incoming-purchase and stock-value displays are sample data. `InventoryController` only renders the screen.
3. **Product lookup eligibility differs from catalog eligibility.** `Product::findByCode()` excludes deleted products but does not require active products/categories as the catalog does. It feeds public details and ordering, so disabled products may still be accessible/orderable.
4. **Inventory aggregation is not defined in product reads.** Product queries join inventory only on `product_id`. If the product has multiple inventory/location rows, catalog results can duplicate and `findByCode()` selects one arbitrary row with `LIMIT 1`, rather than a defined available quantity.
5. **Catalog limit is ignored.** `OrderController::checkout()` requests four sample products, but `Product::catalog()` never uses the `limit` filter.
6. **Procurement and compatibility maintenance are missing.** No routed product/category/brand/vehicle/compatibility/supplier/purchase-order CRUD or goods-receiving backend is present. Compatibility can be read but cannot be maintained through the implemented application.

### Member 3: Chathumi — Administration & Business Intelligence

1. **Main login contract mismatch.** `app/Views/auth/login.php` posts `username`; `HomeController::doLogin()` reads `email`. The username-or-email label also overstates the handler's email-only lookup. Left unchanged because authentication completion was explicitly check-only.
2. **Sign-up is absent.** No registration form, route or persistence flow exists for any role.
3. **Role routing is incomplete.** `HomeController::homeRouteForRole()` uses a hardcoded owner ID and routes every other role to `/sales`; store manager and customer destinations are missing.
4. **Sales and inventory lack server-side role guards.** `SalesController` and `InventoryController` permit unauthenticated access. Confirmed by fresh read-only HTTP requests. Role-specific UI menus do not solve this.
5. **Admin login bypasses the inactive-user check.** `AdminController::doLogin()` selects `is_active` but never tests it. The general login handler does test it.
6. **Admin login detection/redirect is deployment-path dependent.** `AdminController::__construct()` checks whether the raw request URI starts with `/admin/login` and redirects to a hardcoded `/admin/login`. A subdirectory deployment can misidentify its login route and redirect incorrectly.
7. **CSRF helper is not enforced.** Forms emit a token, but the authentication/checkout handlers and router do not call `verify_csrf()`. Logout is state-changing GET. `verify_csrf()` also compares two default empty strings when both tokens are absent.
8. **No login throttling, password recovery or persistent remember-me implementation found.** The recovery link and remember-me checkbox do not have corresponding implemented flows. Demo credentials are prefilled in the general form; only the admin is seeded in the supplied schema.
9. **Admin CRUD and settings writes are absent.** Users/employees are limited read-only queries (eight rows, no pagination implementation). Settings are rendered readonly. Save/discard/export/manage-role/edit actions are not connected to endpoints.
10. **Reports do not consistently describe their data.** Employee “Total Sales (YTD)” displays `base_salary`; “Performance Score” derives from commission rate; the reports table places employee code under Role and revenue under Accuracy Rate. These data/meaning mismatches were reported rather than changing the underlying logic.
11. **Business metrics and audit events are placeholders.** Dashboard fallback sales, revenue/order KPI values, category percentages, report charts, role counts and security log entries contain hardcoded data. Notifications have no mark-read/filter persistence. These should not be presented as verified live business records.
12. **Role-scoped notification retrieval is absent.** The admin notification query reads all notifications, without recipient/read-state handling. Sales and stock notifications are placeholders.
13. **Debug/error handling exposes failures.** The live catalog search failure returned HTTP 200 with a PHP stack trace in the body. `public/index.php` connects to the database even for static/login pages, so database availability is required to reach them.
14. **Seed credential documentation is inconsistent.** The schema's default password comment needs verification against its stored password hash before distributing demo credentials. No credentials were changed or generated in this review.

### Member 4: Varshika — Customer Portal & Public Services

1. **An empty cart becomes an orderable sample item.** Both `public/assets/js/public/checkout.js` and `OrderController::placeOrder()` substitute a brake-pad item when the cart/items are empty. This can place an unintended order. Left unchanged.
2. **Order tracking has no ownership check.** `OrderController::track()` / `Order::trackOrder()` accept an order number without authenticating its owner, and the JSON response includes customer name, phone and delivery address. Sequential order numbers make enumeration possible. Integration partners: Chathumi and Induwara.
3. **Public product details disclose internal pricing.** `CatalogController::details()` returns the full result of `Product::findByCode()` (`p.*`), which includes cost and wholesale pricing, even without login. Hiding wholesale prices in HTML is not authorization. Integration partners: Sashik and Chathumi.
4. **Payment option is not a payment integration.** Card selection does not charge a card; the backend only records a note, and unsupported method values are not explicitly rejected.
5. **Delivery information is not an implemented workflow.** The estimate “Tomorrow by 2PM” is hardcoded; no delivery assignment/status persistence exists. Tracking reads order status, not a complete delivery history.
6. **Customer account integration is missing.** Public orders are not scoped to the signed-in shop customer; there is no customer account/order-history/update/cancellation workflow. Registration and wholesale-access promises previously linked to the ordinary login form; the catalog promotion now accurately labels that destination Sign In.
7. **Frontend/server totals can diverge.** Checkout totals are calculated from local-storage prices, while the server normally reloads prices. The empty-cart and invalid-product fallbacks further weaken consistency. Cart and order amounts need server validation and a defined confirmation flow.
8. **Support and legal destinations are placeholders.** Checkout contains hardcoded support contact text; no Help Center, FAQ, privacy or terms pages were found. Nonexistent public header/footer destinations were removed from navigation, not implemented.

## Missing screens and unfinished user workflows

### Sales representative — Induwara

- Existing screens: dashboard, POS, orders list/detail, new order and customer directory/profile/forms.
- Still missing/incomplete: persisted customer CRUD; real order creation/status updates/cancellation/deletion; persisted sales/payment/invoice workflow; real sales and customer purchase history; statement generation; live assigned-customer/delivery feeds and notifications. Existing mock panels are not separate completed business features.
- Optional per workload: sales returns have no implemented end-to-end workflow.

### Store manager — Sashik

- Existing screen: inventory summary/list with mock stock-in dialog.
- Missing screens: product management and images, category management, brand management, vehicle/engine/compatibility mapping, supplier management, purchasing, purchase-order detail/edit, goods receiving, stock adjustment/movement history.
- Missing behavior: persistent inventory CRUD, actual low-stock/incoming-purchase data and a working catalog text search. A distinct store dashboard beyond the current inventory summary is not present.

### Business owner / administrator — Chathumi

- Existing screens: admin login, dashboard, users, employees, reports, notifications and settings.
- Missing screens/forms: registration, password recovery/reset, completed user add/edit/delete, employee add/edit/delete, role/permission management, real activity log, invoice/tax/backup/preference settings panels.
- Missing behavior: complete role-based authentication/authorization, live KPI/report calculations, useful report periods/exports, searches, pagination, notification read state/filter actions and settings saves.

### Customer / public user — Varshika

- Existing screens: customer home, catalog/details/compatibility dialogs, checkout/confirmation and order tracking.
- Missing screens: vehicle-based spare-part finder, customer account/profile, own-order history/detail actions and delivery management/assignment.
- Missing behavior: registration (with Chathumi), customer-scoped order lifecycle, delivery updates, working text search (with Sashik), completed payment processing if card payment is retained, empty-cart handling and validated server totals.
- Optional per workload: Help Center, FAQ and user guides. No legal-content pages are implemented either.

## Verification and limits

- All 24 PHP view files passed final PHP 8.2 syntax validation. All frontend JavaScript files passed Node syntax checks. Every literal view asset reference resolves to an existing file; the final whitespace/diff check passed.
- Read-only HTTP checks covered home/login, catalog, details/compatibility, checkout/tracking, all five sales routes, inventory, admin login and the six protected admin routes. Protected admin requests redirect to login; HTTP 200 after following a redirect is not evidence that the admin screen was authenticated.
- Admin visual checks used isolated, temporary view fixtures with empty/sample arrays, without changing sessions, user credentials or database records. These validate presentation, not live admin business data.
- Browser checks cover desktop and narrow-phone layouts, logos, customer/sales/stock screens and admin view fixtures. Wide data tables intentionally scroll inside their cards.
- Confirmed catalog text-search error is recorded above and remains unfixed. Authentication, full CRUD, payments and database writes were not claimed as passing or repaired.
- The initial XAMPP session directory was not writable in the review environment. The temporary preview server used the system temporary directory for sessions; project configuration was not changed.

## Maintaining the visual system

Use the existing sales tokens as the source of brand values. `public/assets/css/shared.css` maps public/admin colors and shared controls to those tokens; `admin.css` and `public/home.css` hold role/page-specific presentation. Preserve success/warning/error colors for status meaning. Use the same logo asset on headers, auth screens and printed invoices. New UI work should reuse these styles rather than introduce another independent theme.
