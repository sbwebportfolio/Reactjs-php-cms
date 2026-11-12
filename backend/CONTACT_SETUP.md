<?php
/** Contact submission endpoint contract and local testing notes. */
?>
# Contact form API

Run `backend/database/contact_module.sql` after the core schema. The endpoint is `POST /api/v1/contact` and accepts JSON or form-encoded fields: `name`, `email`, optional `phone`, `subject`, and `message`. Include the empty `website` honeypot field in legitimate forms. Requests are validated server-side and limited to five submissions per IP per 15 minutes. Admins with `inquiries.manage` can review and update submissions at `/backend/admin/inquiries.php` and export CSV.
