CREATE DATABASE IF NOT EXISTS new_aom_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE new_aom_db;

-- 1. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  service_name VARCHAR(150) NOT NULL,
  preferred_date DATE NOT NULL,
  preferred_time VARCHAR(30) NOT NULL,
  notes TEXT NULL,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Donation OTP Verifications Table
CREATE TABLE IF NOT EXISTS donation_otps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  donor_name VARCHAR(150) NOT NULL,
  donor_email VARCHAR(150) NOT NULL,
  project_title VARCHAR(200) NOT NULL,
  currency VARCHAR(10) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  attempts INT DEFAULT 0,
  is_verified TINYINT(1) DEFAULT 0,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Bank & QR Settings Table (Configured via Admin)
CREATE TABLE IF NOT EXISTS donation_bank_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bank_name VARCHAR(150) NOT NULL,
  account_holder VARCHAR(150) NOT NULL,
  account_number VARCHAR(100) NOT NULL,
  branch_name VARCHAR(150) NOT NULL,
  swift_code VARCHAR(50) NULL,
  qr_image_path VARCHAR(255) NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 5. Visitor Tracking (feeds the Total Visitors dashboard card)
CREATE TABLE IF NOT EXISTS visitors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page VARCHAR(255) NOT NULL DEFAULT '',
  ip_hash CHAR(64) NOT NULL DEFAULT '',
  visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY visitors_visited_at (visited_at)
);

INSERT INTO donation_bank_settings (bank_name, account_holder, account_number, branch_name, swift_code, qr_image_path)
SELECT 'Bank of Ceylon', 'NEW AOM', '0000000000', 'Colombo Main Branch', 'BCEYLKLX', 'uploads/donations/default-qr.png'
WHERE NOT EXISTS (SELECT 1 FROM donation_bank_settings);