-- Contact inquiry module. Import after the core admins/permissions schema.
CREATE TABLE IF NOT EXISTS contact_inquiries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(50) NULL,
  subject VARCHAR(240) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('unread','read','replied','archived') NOT NULL DEFAULT 'unread',
  ip_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_inquiries_status_created (status, created_at),
  KEY idx_inquiries_ip_created (ip_hash, created_at),
  KEY idx_inquiries_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (permission_key, description)
VALUES ('inquiries.manage', 'View, update, and export contact inquiries');
