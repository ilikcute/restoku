# Repository Pattern Architecture

## Layer Diagram

```
Controller
  → RepositoryInterface  (contract in App\Interfaces)
      → Repository       (implementation in App\Repositories)
```

Controllers **never** call Eloquent models directly (except `show()` for `->load()` / `->loadCount()` convenience).

## Standard Repository Interface

Every master-data module follows this contract (`App\Interfaces\*RepositoryInterface`):

```php
interface ExampleRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null);
    public function findById(string $id, array $with = []);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
}
```

### Method responsibilities

| Method | Returns | Notes |
|--------|---------|-------|
| `getAllByTenant` | `Collection` or `LengthAwarePaginator` | `$perPage` = null → collection, otherwise paginate. Always `orderBy('name')`. |
| `findById` | Model or `null` | Uses `find()` (not `findOrFail`). Accepts eager-loading via `$with`. |
| `create` | Model | Mass-assignment via `Model::create($data)`. |
| `update` | Model | Uses `findOrFail()`, then `$model->update($data)`, returns the model. |
| `delete` | `bool` or `null` | Uses `findOrFail()`. Returns `false` if model has dependent children (products). |

### Repository rules

- `update()` and `delete()` **must** use `findOrFail()` — never `find()`, never return `bool` from update.
- `delete()` returns `false` (not throw) when protected by child relationships — controller checks this.
- All query scoping by `tenant_id` is done at the repository level.
- Use `when()` or `if()` for optional search filters — consistent style within the file.
- Eager load relationships in `getAllByTenant()` using `with()`.

## Controller Contract

Every controller doing CRUD **must**:

```php
public function __construct(
    protected ExampleRepositoryInterface $exampleRepository
) {}
```

### Method delegation

| Controller Method | Delegates to Repository | Notes |
|-------------------|------------------------|-------|
| `index()` | `$repo->getAllByTenant(...)` | Pass `$request->search`, `$request->integer('per_page')` |
| `store()` | `$repo->create(...)` | Manually inject `tenant_id` before calling create |
| `show()` | Route model binding + `->load()` | Eager load relationships directly |
| `update()` | `$repo->update($model->id, ...)` | **Must** reassign: `$model = $repo->update(...)` |
| `destroy()` | `$repo->delete($model->id)` | **Must** check return: `if (! $deleted) { errorResponse(...) }` |

### show() convention

`show()` uses route model binding + direct `->load()` / `->loadCount()`:

```php
public function show(Product $product)
{
    $this->authorizeTenant($product);
    $product->load(['category', 'unit', 'supplier', 'stock']);
    return $this->successResponse(new ProductResource($product));
}
```

This is acceptable because the instance is already resolved by the framework.

## Auth Module Exception

Auth is the **only** module that uses a Service layer:

```
AuthController → AuthService → AuthRepositoryInterface → AuthRepository
```

`AuthService` handles business logic (password hashing, token creation, ability mapping, validation exceptions). `AuthRepository` handles data access only (find user, create tenant, create user, revoke token).

| Method | Orchestration |
|--------|---------------|
| `register()` | Service hashes password, generates slug, creates token; repo creates Tenant + User |
| `login()` | Service validates credentials, checks active, creates token; repo finds user |
| `logout()` | Service delegates to repo `revokeCurrentToken()` |

## Module Status

| Module | Interface | Repository | Controller Injection | Delegates to Repo | Est. |
|--------|-----------|------------|---------------------|-------------------|------|
| Auth | ✅ Custom | ✅ | Via AuthService | Partial | Done |
| User | ✅ | ✅ | ✅ | ✅ | Done |
| Product | ✅ (+getNextCode) | ✅ | ✅ | ✅ | Done |
| Category | ✅ | ✅ | ✅ | ✅ | Done |
| Unit | ✅ | ✅ | ✅ | ✅ | Done |
| Supplier | ✅ | ✅ | ✅ | ✅ | Done |
| Customer | ✅ | ✅ | ✅ | ✅ | Done |

## Common Pitfalls

1. **Missing import** — Always add `use App\Interfaces\XxxRepositoryInterface;` in the controller.
2. **`<? php` typo** — PHP open tag must be `<?php`, no space.
3. **`update()` return value** — `$model->update($data)` returns `bool`. The repository must `return $model` after update, not `return $model->update($data)`.
4. **`find()` vs `findOrFail()`** — `update()` and `delete()` in repositories must use `findOrFail()`. Silent `null` causes "call to null" errors.
5. **`loadCount('address')`** — `loadCount()` only works on Eloquent **relationships**, not columns. Check the model for the relationship method.
6. **Debug logs in production** — Remove `Log::info()` / `Log::debug()` before committing.
7. **ServiceProvider binding** — After adding a new repository, register: `$this->app->bind(XxxRepositoryInterface::class, XxxRepository::class);`

## Registration

All bindings live in `app/Providers/RepositoryServiceProvider.php`:

```php
public function register(): void
{
    $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
    $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
    $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
    $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
}
```

Registered in `bootstrap/providers.php`.
