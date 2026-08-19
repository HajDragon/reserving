# Reserving

This project is a Laravel application for managing reservations and products.

## Adding a New Product Field

When you add a new field to `products`, update every layer that participates in validation, mass assignment, and UI rendering.

### 1. Database

- Add the column to the `products` table with a new migration.
- Follow the existing schema style in the product table migration.

### 2. Model

- Add the new column name to `App\Models\Product::$fillable`.
- If the field needs type conversion, add a cast in `casts()`.

### 3. Validation

- Add the field to `App\Http\Requests\StoreManagedProductRequest`.
- Add the field to `App\Http\Requests\UpdateManagedProductRequest`.
- Use the right validation rule for the data type, such as `string`, `integer`, `boolean`, or `url`.

### 4. Form UI

- Add the input to `resources/views/cms/products/partials/form-fields.blade.php`.
- Make sure the input `name` matches the database column and validation key exactly.
- For checkboxes, keep the label text visible and wrap the checkbox markup correctly.

### 5. Controller

- The admin product controller already uses `$request->validated()`, so new validated fields are saved automatically.
- If the field needs special handling, transform it before calling `create()` or `update()`.

### 6. Display

- If the field should be visible in the product list, detail page, or admin panel, update the relevant Blade views.

### 7. Tests

- Add or update feature tests to confirm the field is saved on create and update.
- Add UI assertions if the field should appear in rendered views.

## Current Status

The `external_link` field has already been wired through the project:

- database column exists
- model fillable includes `external_link`
- store and update requests validate `external_link`
- form partial includes an `external_link` input

If you add another field, use the same checklist above so it is saved consistently.

## Eloquent Observers

The project uses Eloquent Observers to decouple model lifecycle events from the models themselves.

### ReservationObserver (`app/Observers/ReservationObserver.php`)

- **`created`** — Deducts product inventory via `AdjustProductInventoryAction::deductForReservation()` when a new reservation is saved.

### ProductObserver (`app/Observers/ProductObserver.php`)

- **`updating`** — Clamps `available_quantity` so it never exceeds the product's `quantity`. Fires before the database write to avoid recursive saves.

### Registration

Both observers are registered in `AppServiceProvider::configureDefaults()`:

```php
Reservation::observe(ReservationObserver::class);
Product::observe(ProductObserver::class);
```

### Why observers instead of inline `booted()` hooks?

- Observers keep models focused on data/relationships.
- Observer classes are testable in isolation.
- Adding new event hooks (e.g. `deleted`, `updated`) doesn't bloat the model.
