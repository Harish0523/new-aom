USE new_aom_db;

CREATE TABLE IF NOT EXISTS cms_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content_type VARCHAR(40) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  locale VARCHAR(5) NOT NULL DEFAULT 'en',
  title VARCHAR(240) NOT NULL,
  payload_json LONGTEXT NOT NULL,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0,
  updated_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY cms_content_locale_slug (content_type, locale, slug),
  KEY cms_content_public_list (content_type, locale, status, sort_order),
  CONSTRAINT cms_content_admin_fk
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
);
