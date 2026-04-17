## Plan: Quantity-Aware Cart and Reserving Admin Panel

Implement a reservation-cart workflow where users add products to a personal cart with start/end dates and extra wishes, then place an order that reserves inventory by quantity. Availability is reduced when reserved and restored only when an admin confirms return. Add an admin-only reserving panel with card-based records plus a calendar view and weekday sorting for both start and return dates.

**Steps**
1. Phase 1 — Reservation Quantity Lifecycle and Status Model:
Define the lifecycle that controls quantity deduction/restore.
- Introduce explicit reservation statuses (for example: pending, reserved, returned) and document valid transitions.
- Reserve quantity at order placement (status enters reserved).
- Restore quantity only on admin “confirm returned” action (status moves to returned).
- Add reservation metadata fields needed for return audit (returned_at, returned_by, admin notes if required).
- Dependency: none.

2. Phase 2 — Schema and Relationships:
Create storage structures for carts, orders, and quantity-aware reservations.
- Add migrations for carts and cart_items (user-scoped).
- Add reservation_orders table (group checkout records).
- Extend reservations with: reservation_order_id, extra_wishes, reserved_quantity (or equivalent line quantity), return confirmation fields.
- Add/adjust model relationships: User↔Cart, Cart↔CartItems, ReservationOrder↔Reservations, Reservation↔User/Product.
- Keep max request per user per product bounded by product.quantity.
- Depends on Phase 1.

3. Phase 3 — Availability Engine (Capacity by Time Window):
Implement quantity-aware capacity checks used by cart and checkout.
- Extend availability service to calculate remaining capacity for a product and date window from overlapping non-returned reservations.
- Enforce the rule: user cannot request more units than current available capacity, and never more than product.quantity.
- Update product availability projection so products with zero remaining capacity become unavailable and are marked is_active = 0.
- Re-enable is_active = 1 when capacity becomes available again after return confirmation.
- Depends on Phase 2.

4. Phase 4 — Cart Logic and Product Add Flow:
Implement cart operations and enforce max-quantity constraints at input time.
- Add add/update/remove cart item routes and request validation.
- Capture required fields per cart line: product, start_date, end_date, extra_wishes, requested units.
- Enforce requested units <= product.quantity and <= remaining capacity for selected dates.
- Wire products list Add to Cart action to validated backend endpoints.
- Depends on Phase 3.

5. Phase 5 — Atomic Checkout (Order Placement):
Create order placement that deducts availability safely.
- Checkout converts cart lines into a single reservation_order with reservation rows inside one DB transaction.
- Lock affected product rows during placement, re-validate capacity, then persist reserved quantities.
- On success, clear cart and recalculate product availability states.
- On any failure, rollback all writes and return actionable validation errors.
- Depends on Phase 4.

6. Phase 6 — Admin Return Confirmation Flow:
Implement manual return confirmation that restores capacity.
- Add admin action endpoint to confirm returned reservations/orders.
- When confirmed, move reservations to returned state and release reserved quantity back to availability.
- Recalculate product is_active and product ordering flags after confirmation.
- Keep audit trail of who confirmed return and when.
- Depends on Phases 3 and 5.

7. Phase 7 — Admin /reserving Panel (Card View):
Build admin-only reserving board with required card details.
- Add /reserving route and controller for admin users only.
- Render reserving cards with: product names, quantity reserved, reserving user name and email, reservation date, return date, status, wishes, and relevant references (order id / reservation id).
- Add filters for status and date ranges to keep panel usable at scale.
- Depends on Phases 5 and 6.

8. Phase 8 — Calendar Visualization + Weekday Sorting:
Add time-based visualization and sorting controls.
- Add calendar view in admin /reserving showing reservations across dates.
- Add sorting/filter controls by weekday for both start date weekday and return date weekday.
- Ensure weekday sorting works in both card list and calendar context.
- Depends on Phase 7.

9. Phase 9 — User My Reservings Panel Enhancements:
Ensure users can track their own reservings with new fields.
- Keep /reservations user-scoped.
- Show reserved quantities, reservation/return dates, and extra wishes.
- Reflect returned status promptly after admin confirmation.
- Depends on Phases 5 and 6.

10. Phase 10 — Product List Unavailable UX:
Apply availability behavior to products listing.
- Sort unavailable products to the end of products list.
- Gray out unavailable product cards, show unavailable badge/label, disable add-to-cart controls.
- Ensure unavailable state derives from quantity/date capacity logic and is_active.
- Depends on Phases 3 and 4.

11. Phase 11 — Authorization and Admin Role Enforcement:
Add role boundary needed for /reserving and return confirmations.
- Introduce/administer role flag or gate for admins (since no current role system is present).
- Restrict /reserving and return-confirmation actions to admins.
- Keep cart/checkout/my reservings limited to authenticated user ownership.
- Depends on Phases 6 and 7.

12. Phase 12 — Testing and Verification Coverage:
Protect workflow with focused feature tests.
- Capacity tests: overlapping reservations reduce available units correctly.
- Max quantity tests: user cannot request above product.quantity.
- Checkout concurrency tests: no double reserve under concurrent requests.
- Return confirmation tests: capacity restores only after admin confirmation.
- Admin access tests for /reserving and confirm-return actions.
- Calendar and weekday sorting tests for start and return weekdays.
- Product list tests for unavailable ordering and disabled actions.
- Depends on all implementation phases.

**Relevant files**
- [routes/web.php](routes/web.php) — add cart CRUD, checkout, admin reserving, and return-confirmation endpoints.
- [app/Services/AvailabilityService.php](app/Services/AvailabilityService.php) — capacity math by overlapping windows and quantities.
- [app/Http/Controllers/CartController.php](app/Http/Controllers/CartController.php) — cart operations and validations.
- [app/Http/Controllers/ReservationController.php](app/Http/Controllers/ReservationController.php) — user panel behavior and data exposure.
- [app/Http/Controllers/ProductController.php](app/Http/Controllers/ProductController.php) — product ordering and unavailable flags for listing.
- [app/Models/Product.php](app/Models/Product.php) — quantity/is_active semantics and relations.
- [app/Models/Reservation.php](app/Models/Reservation.php) — quantity, wishes, status/return tracking, order linkage.
- [app/Models/User.php](app/Models/User.php) — cart/reservation/order/admin-role relations.
- [resources/views/products/index.blade.php](resources/views/products/index.blade.php) — unavailable card state and disabled add action.
- [resources/views/carts/index.blade.php](resources/views/carts/index.blade.php) — cart item fields and checkout actions.
- [resources/views/reservations/index.blade.php](resources/views/reservations/index.blade.php) — my reservings with quantities/status/wishes.
- [resources/views/layouts/app/sidebar.blade.php](resources/views/layouts/app/sidebar.blade.php) — admin/user navigation entries.
- [database/migrations/2026_04_15_142023_create_products_table.php](database/migrations/2026_04_15_142023_create_products_table.php) — existing quantity/is_active baseline.
- [database/migrations/2026_04_15_142023_create_reservations_table.php](database/migrations/2026_04_15_142023_create_reservations_table.php) — extend with quantity/order/return fields.
- [tests/Feature/ReservationStoreTest.php](tests/Feature/ReservationStoreTest.php) — extend for quantity and overlap logic.
- [tests/Feature/ReservationIndexTest.php](tests/Feature/ReservationIndexTest.php) — user-scoped reservings assertions.
- [tests/Feature/CartIndexTest.php](tests/Feature/CartIndexTest.php) — evolve to cart quantity and checkout behaviors.

**Verification**
1. Run focused tests iteratively:
- php artisan test --compact tests/Feature/CartIndexTest.php
- php artisan test --compact tests/Feature/ReservationStoreTest.php
- php artisan test --compact tests/Feature/ReservationIndexTest.php
- php artisan test --compact --filter=reserving
2. Add dedicated tests for admin return confirmation and weekday sorting (start and return).
3. Validate manual scenario with two users + admin:
- Users place overlapping reservations until product capacity is exhausted.
- Product becomes unavailable and moves to list end.
- Admin confirms return; capacity restores and product becomes available again.
- /reserving cards and calendar reflect updates.
4. Run vendor/bin/pint --dirty --format agent after PHP edits.

**Decisions**
- Maximum user request is bounded by product.quantity and date-window capacity.
- Quantity is deducted when order is reserved.
- Quantity is restored only when admin confirms return.
- /reserving is admin-only.
- Admin panel uses cards plus calendar visualization.
- Sorting/filtering by weekdays supports both start-date and return-date weekdays.
- No pricing is part of this system.

**Further Considerations**
1. Choose whether reserved_quantity is stored per reservation row or normalized into reservation_items if multi-product lines become complex.
2. Decide whether calendar UI is month-only or supports week/day zoom for operations.
3. Decide if partially returned orders are allowed or return confirmation is always all lines at once.
