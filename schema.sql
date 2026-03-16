CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    event_label VARCHAR(100) DEFAULT NULL
);

CREATE TABLE guest_event_invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    event_id INT NOT NULL,
    allowed_guests INT NOT NULL DEFAULT 1,
    rsvp_status VARCHAR(20) DEFAULT NULL,
    guest_count INT DEFAULT NULL,
    message TEXT DEFAULT NULL,
    invite_sent_sms TINYINT(1) NOT NULL DEFAULT 0,
    invite_sent_whatsapp TINYINT(1) NOT NULL DEFAULT 0,
    invite_sent_sms_at DATETIME DEFAULT NULL,
    invite_sent_whatsapp_at DATETIME DEFAULT NULL,
    responded_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_guest_event (guest_id, event_id),
    CONSTRAINT fk_gei_guest FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    CONSTRAINT fk_gei_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE message_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    channel VARCHAR(20) NOT NULL,
    provider_message_id VARCHAR(100) DEFAULT NULL,
    status VARCHAR(50) DEFAULT NULL,
    payload TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_guest FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE
);

INSERT INTO events (event_name, event_date, event_label) VALUES
('Engagement / Haldi', '2026-04-30', 'April 30'),
('Marriage', '2026-05-03', 'May 3'),
('Rathram / Reception', '2026-05-04', 'May 4');
