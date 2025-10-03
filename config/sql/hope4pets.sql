-- ==============================
-- USERS & PROFILES
-- ==============================

CREATE TABLE users (
    user_id        BIGINT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('adopter','owner','rescuer','shelter_admin','platform_admin') DEFAULT 'adopter',
    status         ENUM('active','inactive','banned') DEFAULT 'active',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profiles (
    profile_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT NOT NULL,
    bio            TEXT,
    address        VARCHAR(255),
    phone          VARCHAR(20),
    verification_status ENUM('pending','verified','rejected') DEFAULT 'pending',
    documents      TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ==============================
-- SHELTERS / ORGANIZATIONS
-- ==============================

CREATE TABLE shelters (
    shelter_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(150) NOT NULL,
    location       VARCHAR(255),
    contact_info   VARCHAR(255),
    verification_status ENUM('pending','verified','rejected') DEFAULT 'pending'
);

-- ==============================
-- PETS & MEDIA
-- ==============================

CREATE TABLE pets (
    pet_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    owner_id       BIGINT NOT NULL,
    shelter_id     BIGINT,
    name           VARCHAR(100) NOT NULL,
    type           VARCHAR(50) NOT NULL,
    breed          VARCHAR(100),
    age            INT,
    gender         ENUM('male','female','unknown'),
    size           ENUM('small','medium','large'),
    health_status  VARCHAR(255),
    vaccination_status VARCHAR(255),
    special_needs  TEXT,
    status         ENUM('available','adopted','archived') DEFAULT 'available',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shelter_id) REFERENCES shelters(shelter_id) ON DELETE SET NULL,
    INDEX idx_pet_status_type (status, type)
);

CREATE TABLE petmedia (
    media_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    pet_id         BIGINT NOT NULL,
    file_url       VARCHAR(255) NOT NULL,
    caption        VARCHAR(255),
    media_type     ENUM('image','video','document') DEFAULT 'image',
    uploaded_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
);

-- ==============================
-- ADOPTION / APPLICATION FLOW
-- ==============================

CREATE TABLE applications (
    application_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pet_id         BIGINT NOT NULL,
    applicant_id   BIGINT NOT NULL,
    form_responses TEXT,
    documents      TEXT,
    status         ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
    submitted_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at    TIMESTAMP NULL,
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_applicant_status (applicant_id, status)
);

CREATE TABLE adoptions (
    adoption_id    BIGINT AUTO_INCREMENT PRIMARY KEY,
    pet_id         BIGINT NOT NULL,
    adopter_id     BIGINT NOT NULL,
    application_id BIGINT NOT NULL,
    adoption_date  DATE,
    status         ENUM('pending','accepted','rejected','completed') DEFAULT 'pending',
    notes          TEXT,
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (adopter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

-- ==============================
-- MESSAGING & NOTIFICATIONS
-- ==============================

CREATE TABLE messages (
    message_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    sender_id      BIGINT NOT NULL,
    receiver_id    BIGINT NOT NULL,
    content        TEXT NOT NULL,
    attachment     VARCHAR(255),
    sent_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_status    ENUM('unread','read') DEFAULT 'unread',
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_receiver_read (receiver_id, read_status)
);

CREATE TABLE notifications (
    notification_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT NOT NULL,
    type           VARCHAR(100),
    message        TEXT,
    status         ENUM('unread','read') DEFAULT 'unread',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ==============================
-- APPOINTMENTS (MEET & GREET)
-- ==============================

CREATE TABLE appointments (
    appointment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    adoption_id    BIGINT NOT NULL,
    date           DATETIME NOT NULL,
    location       VARCHAR(255),
    status         ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    confirmation_sent BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (adoption_id) REFERENCES adoptions(adoption_id) ON DELETE CASCADE
);

-- ==============================
-- DONATIONS & PAYMENTS
-- ==============================

CREATE TABLE donations (
    donation_id    BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT NOT NULL,
    shelter_id     BIGINT NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    currency       VARCHAR(10) DEFAULT 'PHP',
    date           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100) UNIQUE,
    status         ENUM('pending','completed','failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shelter_id) REFERENCES shelters(shelter_id) ON DELETE CASCADE
);

CREATE TABLE payments (
    payment_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    donation_id    BIGINT NOT NULL,
    method         ENUM('credit_card','paypal','gcash','maya','gotyme','other'),
    status         ENUM('pending','completed','failed') DEFAULT 'pending',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE
);

-- ==============================
-- MEDICAL RECORDS
-- ==============================

CREATE TABLE medicalrecords (
    record_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
    pet_id         BIGINT NOT NULL,
    vaccination_type VARCHAR(100),
    date_administered DATE,
    next_due_date  DATE,
    status         ENUM('done','scheduled','missed') DEFAULT 'scheduled',
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
);

-- ==============================
-- FEEDBACK & RATINGS
-- ==============================

CREATE TABLE feedback (
    feedback_id    BIGINT AUTO_INCREMENT PRIMARY KEY,
    from_user_id   BIGINT NOT NULL,
    to_user_id     BIGINT NOT NULL,
    adoption_id    BIGINT NOT NULL,
    rating         INT CHECK (rating >= 1 AND rating <= 5),
    comment        TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (adoption_id) REFERENCES adoptions(adoption_id) ON DELETE CASCADE
);

-- ==============================
-- MODERATION & REPORTS
-- ==============================

CREATE TABLE reports (
    report_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
    reported_by    BIGINT NOT NULL,
    content_id     BIGINT NOT NULL,
    type           ENUM('listing','message','user') NOT NULL,
    reason         TEXT,
    status         ENUM('open','reviewed','resolved') DEFAULT 'open',
    action_taken   TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ==============================
-- OPTIONAL: AUDIT LOGS (Admin)
-- ==============================

CREATE TABLE audit_logs (
    log_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    admin_id       BIGINT NOT NULL,
    action         VARCHAR(255),
    details        TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);
