-- News module. Import after the core CMS schema (including admins and permissions).
CREATE TABLE IF NOT EXISTS news_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  title VARCHAR(240) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  excerpt TEXT NULL,
  content LONGTEXT NOT NULL,
  featured_image VARCHAR(500) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published','scheduled','archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_news_status_publication (status, published_at),
  KEY idx_news_category (category_id),
  KEY idx_news_featured (is_featured, published_at),
  CONSTRAINT fk_news_category FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (permission_key, description)
VALUES ('news.manage', 'Create, edit, publish, and archive news articles');
