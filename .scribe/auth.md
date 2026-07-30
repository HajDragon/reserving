# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_SANCTUM_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

### Token abilities

This API uses Sanctum token abilities to scope access:

| Ability       | Description                              |
|---------------|------------------------------------------|
| `products.read`  | View product listings and details     |
| `products.write` | Create, update, and delete products   |

A token must have the matching ability for the endpoint. For example, `GET /api/products` requires `products.read`, while `POST /api/products` requires `products.write`.

### Generating a token

Tokens are created via the API Token Management panel in the CMS at `/cms/api-tokens`, or via the Laravel Sanctum API directly.
