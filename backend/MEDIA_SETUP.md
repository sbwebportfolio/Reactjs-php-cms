# Phase 4: Media library

1. Import the core schema, then run `mysql -u USER -p DATABASE < backend/database/media_module.sql`.
2. Assign `media.manage` permission to any non-super-admin roles that need uploads. Super Admin inherits all seeded permissions.
3. Ensure PHP has `fileinfo` enabled and `APP_UPLOAD_PATH` is writable by the PHP worker. The default directory is `backend/storage/uploads`.
4. Open `/backend/admin/media.php`; allowed uploads are JPG, PNG, GIF, WebP and PDF, up to 5 MB.
5. Public references use `/backend/media-file.php?id=MEDIA_ID`; do not expose storage paths directly.

The library soft-removes rows to avoid deleting a file that another content item may reference. Test uploads, MIME detection, permissions, and Apache/Nginx access controls in the deployment environment before production.
