# Introduction


This API provides programmatic access to the Experience Lab equipment catalog at Summa Zorg en Welzijn. It allows external systems (such as a WordPress integration or third-party dashboards) to query, create, update, and delete products.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

**Features:**
- Full CRUD for products (create, read, update, delete)
- Partial-match filtering on asset_tag, name, and type
- Sorting by any product field
- Paginated responses with configurable page size
- Sanctum token-based authentication with ability scoping (`products.read`, `products.write`)

All requests must include a valid Sanctum personal access token in the `Authorization` header.
