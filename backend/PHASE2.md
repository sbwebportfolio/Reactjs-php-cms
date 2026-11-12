# Phase 2 backend foundation setup

## Requirements
- PHP 8.1+ with `mysqli`, `mbstring`, `fileinfo`, and sessions enabled
- MySQL 8+
- Web server configured to route `/api/v1/*` requests to `backend/api/index.php`

## Configure environment
Set these values in the web server / PHP process environment (do not commit secrets):

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corporate_cms
DB_USERNAME=cms_user
DB_PASSWORD=use-a-unique-secret
```

Import `backend/database/cms_database.sql`. For older installations, apply `backend/database/phase2_security.sql` where appropriate. Then create the first administrator from a shell:

```bash
php backend/bin/create-admin.php "CMS Owner" owner@example.com 'a-long-unique-password'
```

The command only succeeds when no admin exists, hashes the password with `password_hash()`, and assigns the Super Admin role. Do not execute it through a public web request.

## Admin and API
- Admin login: `/backend/admin/login.php`
- Admin logout: POST `/backend/admin/logout.php` with a valid `csrf_token`
- Health check: `/api/v1/health`
- Admin dashboard API: `/api/v1/admin/dashboard` (session authenticated)
- Fetch a CSRF token for admin API mutations: `/api/v1/admin/csrf`; send it as `X-CSRF-Token` for POST/PUT/PATCH/DELETE.

Use HTTPS in production. Configure session cookies as secure at the hosting layer, keep config outside public document root when possible, and deny direct access to storage, config, and includes. The current Phase 2 API only implements the foundation routes; CMS CRUD endpoints are not included yet.
