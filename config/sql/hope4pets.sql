-- Create Database
CREATE DATABASE IF NOT EXISTS hope4pets
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE hope4pets;

-- ========================================
-- ROLES
-- ========================================
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE, -- guest, user, shelter, admin
  description VARCHAR(255)
);

INSERT INTO roles (name, description) VALUES
('guest','Unauthenticated visitor'),
('user','Individual user / adopter'),
('shelter','Shelter / Organization user'),
('admin','Administrator');

-- ========================================
-- USERS (individual + shelter + admin)
-- ========================================
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255),
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  display_name VARCHAR(150),
  birthday DATE,
  gender ENUM('male','female','other','unspecified') DEFAULT 'unspecified',
  contact_number VARCHAR(50),
  address TEXT,
  is_verified TINYINT(1) DEFAULT 0,
  signup_source ENUM('email','google','facebook','other') DEFAULT 'email',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- ========================================
-- SHELTER META
-- ========================================
CREATE TABLE shelters (
  id BIGINT UNSIGNED PRIMARY KEY, -- same as users.id
  shelter_name VARCHAR(255) NOT NULL,
  address VARCHAR(500),
  contact_number VARCHAR(50),
  contact_person VARCHAR(150),
  registration_number VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- FILES (uploads: IDs, permits, pet photos, etc.)
-- ========================================
CREATE TABLE files (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED,
  filename VARCHAR(255) NOT NULL,
  filepath VARCHAR(500) NOT NULL,
  filetype VARCHAR(100),
  filesize BIGINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- PETS
-- ========================================
CREATE TABLE pets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shelter_id BIGINT UNSIGNED NULL,
  posted_by BIGINT UNSIGNED NOT NULL,
  name VARCHAR(200),
  species ENUM('dog','cat','bird','rabbit','other') DEFAULT 'other',
  breed VARCHAR(200),
  age VARCHAR(50),
  gender ENUM('male','female','unknown') DEFAULT 'unknown',
  size ENUM('small','medium','large','extra-large') DEFAULT 'medium',
  description TEXT,
  status ENUM('available','pending','adopted','not_for_adoption','removed') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL,
  FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Pet Photos
CREATE TABLE pet_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  file_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
);

-- ========================================
-- COMMUNITY POSTS (feed)
-- ========================================
CREATE TABLE posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Post Photos
CREATE TABLE post_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  file_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
);

-- ========================================
-- COMMENTS & REACTIONS
-- ========================================
CREATE TABLE comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  target_type ENUM('post','pet') NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  target_type ENUM('post','pet') NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  reaction_type VARCHAR(50) DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- MESSAGES
-- ========================================
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT UNSIGNED,
  recipient_id BIGINT UNSIGNED NOT NULL,
  body TEXT,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- DONATIONS
-- ========================================
CREATE TABLE donations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_user_id BIGINT UNSIGNED,
  shelter_id BIGINT UNSIGNED,
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('pending','completed','failed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL
);

-- ========================================
-- ADOPTIONS
-- ========================================
CREATE TABLE adoptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id BIGINT UNSIGNED NOT NULL,
  applicant_user_id BIGINT UNSIGNED NOT NULL,
  shelter_id BIGINT UNSIGNED,
  status ENUM('applied','approved','rejected','completed','cancelled') DEFAULT 'applied',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  FOREIGN KEY (applicant_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE SET NULL
);

-- ========================================
-- REPORTS (system reports)
-- ========================================
CREATE TABLE reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_user_id BIGINT UNSIGNED,
  target_type ENUM('post','pet','user','donation','message') NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255),
  status ENUM('open','resolved','dismissed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE SET NULL
);
