USE db_restoran;

CREATE TABLE restaurant_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_date DATE NOT NULL,
    opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    opened_by INT NULL,
    closed_by INT NULL,
    opening_note VARCHAR(255) NULL,
    closing_note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant_sessions_date (business_date),
    INDEX idx_restaurant_sessions_status (status),
    CONSTRAINT fk_session_opened_by
        FOREIGN KEY (opened_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_session_closed_by
        FOREIGN KEY (closed_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE orders
    ADD COLUMN session_id INT NULL AFTER id,
    ADD INDEX idx_orders_session_id (session_id),
    ADD CONSTRAINT fk_orders_session
        FOREIGN KEY (session_id) REFERENCES restaurant_sessions(id)
        ON UPDATE CASCADE ON DELETE SET NULL;
