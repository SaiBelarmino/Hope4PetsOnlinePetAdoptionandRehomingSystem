-- ===================================================
-- DATABASE
-- ===================================================
CREATE DATABASE IF NOT EXISTS hope4pets
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE hope4pets;

-- ===================================================
-- USERS
-- ===================================================
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  birthday DATE,
  gender ENUM('male','female','other','unspecified') DEFAULT 'unspecified',
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  profile_photo VARCHAR(255),
  location VARCHAR(255),
  contact_number VARCHAR(50),
  is_verified TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
);

-- ===================================================
-- ADMINS
-- ===================================================
CREATE TABLE admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admins_username (username),
  UNIQUE KEY uq_admins_email (email)
);

-- ===================================================
-- USER DOCUMENTS
-- ===================================================
CREATE TABLE user_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  doc_type VARCHAR(100),
  file_path VARCHAR(500) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  CONSTRAINT fk_userdocs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_userdocs_admin FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ===================================================
-- SHELTERS
-- ===================================================
CREATE TABLE shelters (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  shelter_name VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  contact_number VARCHAR(50),
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  verified_at TIMESTAMP NULL DEFAULT NULL,
  verified_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_shelters_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE shelter_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shelter_id BIGINT UNSIGNED NOT NULL,
  doc_type VARCHAR(100),
  file_path VARCHAR(500) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  CONSTRAINT fk_shelterdocs_shelter FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE CASCADE,
  CONSTRAINT fk_shelterdocs_admin FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ===================================================
-- PETS
-- ===================================================
CREATE TABLE pets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id BIGINT UNSIGNED NOT NULL,
  shelter_id BIGINT UNSIGNED NULL,
  name VARCHAR(200) NOT NULL,
  species ENUM('dog','cat','bird','rabbit','other') DEFAULT 'other',
  breed VARCHAR(200),
  age VARCHAR(50),
  gender ENUM('male','female','unknown') DEFAULT 'unknown',
  size ENUM('small','medium','large','extra-large') DEFAULT 'medium',
  vaccine_status VARCHAR(255),
  health_status VARCHAR(255),
  location VARCHAR(255),
  description TEXT,
  status ENUM('available','pending','adopted','removed') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pets_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_pets_shelter FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL
);

CREATE TABLE pet_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  photo_path VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  CONSTRAINT fk_petphotos_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

CREATE TABLE pet_comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_petcomments_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_petcomments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE pet_reactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  reaction_type VARCHAR(50) DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pet_react (pet_id, user_id),
  CONSTRAINT fk_petreactions_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_petreactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===================================================
-- POSTS
-- ===================================================
CREATE TABLE posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  pet_id BIGINT UNSIGNED NULL,
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_posts_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL
);

CREATE TABLE post_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  photo_path VARCHAR(500) NOT NULL,
  CONSTRAINT fk_postphotos_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE post_comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_postcomments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_postcomments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_reactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  reaction_type VARCHAR(50) DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_post_react (post_id, user_id),
  CONSTRAINT fk_postreactions_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_postreactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===================================================
-- MESSAGES
-- ===================================================
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT UNSIGNED,
  recipient_id BIGINT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_messages_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===================================================
-- DONATIONS
-- ===================================================
CREATE TABLE donations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_id BIGINT UNSIGNED,
  shelter_id BIGINT UNSIGNED,
  transaction_id VARCHAR(100) NOT NULL,
  donor_name VARCHAR(150),
  amount DECIMAL(12,2) NOT NULL,
  payment_method ENUM('credit_card','paypal','gcash','paymaya','bank_transfer','other') NOT NULL,
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_transaction_id (transaction_id),
  CONSTRAINT fk_donations_donor FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_donations_shelter FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL
);

-- ===================================================
-- ADOPTIONS
-- ===================================================
CREATE TABLE adoptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  applicant_id BIGINT UNSIGNED NOT NULL,
  shelter_id BIGINT UNSIGNED,
  status ENUM('applied','approved','denied','completed','cancelled') DEFAULT 'applied',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  CONSTRAINT fk_adoptions_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_adoptions_applicant FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_adoptions_shelter FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL,
  CONSTRAINT fk_adoptions_admin FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ===================================================
-- REPORTS
-- ===================================================
CREATE TABLE user_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED,
  reported_user_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255),
  status ENUM('open','resolved','dismissed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  handled_by BIGINT UNSIGNED NULL,
  handled_at TIMESTAMP NULL,
  CONSTRAINT fk_userreports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_userreports_reported FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_userreports_admin FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE post_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED,
  post_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255),
  status ENUM('open','resolved','dismissed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  handled_by BIGINT UNSIGNED NULL,
  handled_at TIMESTAMP NULL,
  CONSTRAINT fk_postreports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_postreports_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_postreports_admin FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE pet_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED,
  pet_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255),
  status ENUM('open','resolved','dismissed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  handled_by BIGINT UNSIGNED NULL,
  handled_at TIMESTAMP NULL,
  CONSTRAINT fk_petreports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_petreports_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_petreports_admin FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ===================================================
-- ADMIN LOGS
-- ===================================================
CREATE TABLE admin_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(255),
  target_type VARCHAR(100),
  target_id BIGINT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_adminlogs_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
