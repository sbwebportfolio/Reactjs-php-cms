# React.js + PHP CMS

This repository is set up for a production-ready corporate CMS built with:

- React.js public frontend
- Core PHP 8+
- MySQLi database access
- REST API layer using JSON responses
- PHP admin dashboard

## Phase 1 completed
- Architecture and folder structure
- Database schema plan
- Core API route design
- Admin and frontend module planning

## Phase 2 current implementation
This initial backend foundation includes:

- MySQLi database connection
- Config-driven environment settings
- Shared helper functions
- Request validation utilities
- Session-based admin authentication
- CSRF-protected login/logout flow
- Central API router with route scaffolding
- Basic admin dashboard bootstrap

## Directory structure

```text
backend/
  admin/
  api/
  config/
  database/
  includes/
  logs/
  uploads/
```

## Setup

1. Create the MySQL database and import `backend/database/cms_database.sql`.
2. Update `backend/config/config.php` and `backend/config/database.php` with your environment values.
3. Ensure PHP 8+ and MySQLi are enabled.
4. Place the project behind a web server (Apache/Nginx) with PHP support.
5. Open the admin login page at `/backend/admin/login.php`.

## Notes

- All database access uses MySQLi prepared statements.
- API responses are standardized JSON objects.
- Admin routes require authentication and authorization checks.

## Next phase

The next step will extend the backend with CMS CRUD operations, media management, and public APIs for homepage and content modules.
