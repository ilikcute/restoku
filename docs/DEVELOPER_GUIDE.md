# Developer Guide

Welcome to the Restoku development team. This guide covers setup, common tasks, and troubleshooting.

## 1. Setup Environment

1. **Prerequisites**: PHP 8.2+, MySQL 8.0, Node.js 18+.
2. **Backend Setup**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```
3. **Frontend Setup**:
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

## 2. Troubleshooting & Pitfalls

### Common Backend Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| `403 Forbidden` on valid route | Permission missing | Check `app/Http/Middleware/PermissionMiddleware.php` and user roles. |
| `Call to undefined method ...Repository` | Interface mismatch | Ensure the method is defined in the Interface and implemented in the Repository. |
| `500 Internal Server Error` (Generic) | Exception unhandled | Check `storage/logs/laravel.log`. The global handler usually catches these. |
| `tenant_id` not saved | Mass assignment | Check if `tenant_id` is in the `$fillable` array of the Model. |

### Common Frontend Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| `[intlify] Not found 'xxx' key` | Missing i18n key | Add the key to `src/i18n/locales/en.json` and `id.json`. |
| API Returns `401 Unauthenticated` | Token expired | Clear localStorage and log in again. Check `SANCTUM_STATEFUL_DOMAINS` in `.env`. |
| CORS Errors | Incorrect origin | Update `config/cors.php` and `CORS_ALLOWED_ORIGINS` in `.env`. |

## 3. Operational Runbooks

### Database Migration Policy
- **Never** modify an existing migration that has been merged. Always create a new one.
- Use `php artisan make:migration` and follow the `YYYY_MM_DD_HHMMSS_description` naming.

### Git Workflow
1. Create a feature branch: `feature/name-of-feature`.
2. Commit often with descriptive messages.
3. Run `vendor/bin/pint` before pushing to ensure code style compliance.
4. Open a Pull Request for review.

## 4. Useful Commands

- **List API Routes**: `php artisan route:list --path=api`
- **Clear Cache**: `php artisan optimize:clear`
- **Run Tests**: `php artisan test`
- **Frontend Build**: `npm run build`
