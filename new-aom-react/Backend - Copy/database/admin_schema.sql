USE new_aom_db;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Super Admin', 'Content Manager', 'Moderator') DEFAULT 'Super Admin',
  is_active TINYINT(1) DEFAULT 1,
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS security_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(150) NOT NULL,
  action VARCHAR(200) NOT NULL,
  ip_address VARCHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Super Admin (Password: Admin@12345 — login செய்த பின் மாற்றவும்)
INSERT IGNORE INTO admin_users (full_name, email, password_hash, role)
VALUES (
  'NEW AOM Admin',
  'admin@newaom.org',
  '$2y$10$au6ZuKjAkJSoAOyi2WuK9uVtQwcjqmlzLFHTqB6vN.Fiycv87DIGa',
  'Super Admin'
);

UPDATE admin_users
SET password_hash = '$2y$10$au6ZuKjAkJSoAOyi2WuK9uVtQwcjqmlzLFHTqB6vN.Fiycv87DIGa'
WHERE email = 'admin@newaom.org'
  AND password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';