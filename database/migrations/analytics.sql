-- Analytics tables for tracking website usage

CREATE TABLE IF NOT EXISTS page_view (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(64) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(200) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    device_type ENUM('desktop', 'mobile', 'tablet', 'unknown') DEFAULT 'unknown',
    browser VARCHAR(100) DEFAULT NULL,
    os VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_created_at (created_at),
    INDEX idx_page_url (page_url(255))
);

CREATE TABLE IF NOT EXISTS analytics_session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    page_views INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL,
    INDEX idx_session_id (session_id),
    INDEX idx_started_at (started_at)
);

CREATE TABLE IF NOT EXISTS daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL UNIQUE,
    total_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    unique_sessions INT DEFAULT 0,
    registered_users INT DEFAULT 0,
    new_registrations INT DEFAULT 0,
    INDEX idx_stat_date (stat_date)
);
