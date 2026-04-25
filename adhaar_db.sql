-- ============================================================
--  Adhaar – The SoulServe  |  Complete Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS adhaar_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE adhaar_db;

-- 1. USERS / REGISTER
CREATE TABLE IF NOT EXISTS register (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(120)  NOT NULL,
  email            VARCHAR(180)  NOT NULL UNIQUE,
  mobile           VARCHAR(20)   NOT NULL,
  password         VARCHAR(255)  NOT NULL,
  role             ENUM('donor','volunteer') NOT NULL DEFAULT 'donor',
  address          TEXT,
  volunteer_reason TEXT,
  verified         TINYINT(1)    NOT NULL DEFAULT 0,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. OTPs
CREATE TABLE IF NOT EXISTS otps (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(180) NOT NULL,
  otp        VARCHAR(10)  NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB;

-- 3. FOOD DONATIONS
CREATE TABLE IF NOT EXISTS food_donations (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_email     VARCHAR(180)  NOT NULL,
  food_time       DATETIME,
  safe_hours      INT           DEFAULT 0,
  quantity        VARCHAR(100),
  priority        ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  pickup_address  TEXT,
  contact         VARCHAR(20),
  image           VARCHAR(300),
  status          ENUM('pending','accepted','rejected','scheduled',
                       'out_for_pickup','picked_up','delivered')
                  NOT NULL DEFAULT 'pending',
  pickup_date     DATE,
  pickup_time     TIME,
  volunteer_email VARCHAR(180),
  notes           TEXT,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_donor  (donor_email),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- 4. CLOTH DONATIONS
CREATE TABLE IF NOT EXISTS cloth_donations (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_email     VARCHAR(180)  NOT NULL,
  purchase_time   VARCHAR(100),
  quantity        INT           DEFAULT 1,
  cloth_type      VARCHAR(80),
  condition_type  ENUM('new','good','fair','worn') NOT NULL DEFAULT 'good',
  is_clean        TINYINT(1)    NOT NULL DEFAULT 1,
  pickup_address  TEXT,
  contact         VARCHAR(20),
  image           VARCHAR(300),
  status          ENUM('pending','accepted','rejected','scheduled',
                       'out_for_pickup','picked_up','delivered')
                  NOT NULL DEFAULT 'pending',
  pickup_date     DATE,
  pickup_time     TIME,
  volunteer_email VARCHAR(180),
  notes           TEXT,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_donor  (donor_email),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- 5. ADMINS
CREATE TABLE IF NOT EXISTS admins (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(180) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. VOLUNTEERS (public interest form)
CREATE TABLE IF NOT EXISTS volunteers (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(180) NOT NULL,
  phone      VARCHAR(20),
  city       VARCHAR(80),
  interest   VARCHAR(120),
  message    TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. CONTACT MESSAGES
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(180) NOT NULL,
  message    TEXT         NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. PASSWORD RESETS
CREATE TABLE IF NOT EXISTS password_resets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(180) NOT NULL,
  token      VARCHAR(255) NOT NULL UNIQUE,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_token (token)
) ENGINE=InnoDB;
