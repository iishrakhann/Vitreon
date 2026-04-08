CREATE DATABASE IF NOT EXISTS pune_event_hub;
USE pune_event_hub;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    google_sub VARCHAR(191) NULL UNIQUE,
    full_name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone_number VARCHAR(30) NULL,
    role ENUM('CUSTOMER', 'OWNER', 'ADMIN') NOT NULL DEFAULT 'CUSTOMER',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS venues (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    owner_id BIGINT NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    name VARCHAR(191) NOT NULL,
    neighborhood VARCHAR(120) NOT NULL,
    event_category VARCHAR(120) NOT NULL,
    base_price DECIMAL(12,2) NOT NULL,
    capacity_range VARCHAR(120) NULL,
    description TEXT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_venues_owner FOREIGN KEY (owner_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS venue_images (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    venue_id BIGINT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_venue_images_venue FOREIGN KEY (venue_id) REFERENCES venues(id),
    UNIQUE KEY uniq_venue_image_sort (venue_id, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS venue_slots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    venue_id BIGINT NOT NULL,
    slot_start DATETIME NOT NULL,
    slot_end DATETIME NOT NULL,
    status ENUM('AVAILABLE', 'HELD', 'BOOKED', 'EXPIRED') NOT NULL DEFAULT 'AVAILABLE',
    hold_reference VARCHAR(191) NULL,
    hold_expires_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_slots_venue FOREIGN KEY (venue_id) REFERENCES venues(id),
    INDEX idx_slot_lookup (venue_id, slot_start, slot_end),
    INDEX idx_hold_expiry (hold_expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    venue_slot_id BIGINT NOT NULL,
    booking_reference VARCHAR(191) NOT NULL UNIQUE,
    hold_reference VARCHAR(191) NULL UNIQUE,
    total_amount DECIMAL(12,2) NOT NULL,
    deposit_amount DECIMAL(12,2) NOT NULL,
    payment_status ENUM('PENDING', 'DEPOSIT_PAID', 'FAILED', 'REFUNDED') NOT NULL DEFAULT 'PENDING',
    booking_status ENUM('PENDING_REVIEW', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING_REVIEW',
    razorpay_order_id VARCHAR(191) NULL,
    razorpay_payment_id VARCHAR(191) NULL,
    venue_name VARCHAR(191) NULL,
    owner_phone VARCHAR(30) NULL,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_bookings_slot FOREIGN KEY (venue_slot_id) REFERENCES venue_slots(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    venue_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT NOT NULL,
    ai_sentiment_score DECIMAL(4,3) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_venue FOREIGN KEY (venue_id) REFERENCES venues(id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_webhook_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(40) NOT NULL,
    event_name VARCHAR(120) NOT NULL,
    booking_reference VARCHAR(191) NULL,
    razorpay_order_id VARCHAR(191) NULL,
    razorpay_payment_id VARCHAR(191) NULL,
    payload_json LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook_booking_reference (booking_reference),
    INDEX idx_webhook_order_id (razorpay_order_id)
) ENGINE=InnoDB;

-- Atomic hold pattern used by the Spring Boot validator:
-- START TRANSACTION;
-- SELECT * FROM venue_slots WHERE id = ? FOR UPDATE;
-- validate current status and hold_expires_at
-- UPDATE venue_slots SET status = 'HELD', hold_reference = ?, hold_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = ?;
-- COMMIT;
