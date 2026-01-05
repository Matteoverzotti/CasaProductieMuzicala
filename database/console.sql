CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE artist (
    id INT PRIMARY KEY,
    stage_name VARCHAR(200),
    bio TEXT,
    country VARCHAR(100),
    genre VARCHAR(100),
    FOREIGN KEY (id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE angajat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    salary INT NOT NULL DEFAULT 0,
    hire_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE proiect (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    created_by INT,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME DEFAULT NULL,
    FOREIGN KEY created_by (created_by) REFERENCES user(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Un proiect poate avea mai mulți utilizatori asociați (artiști și/sau angajați)
CREATE TABLE proiect_user (
    proiect_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
    PRIMARY KEY (proiect_id, user_id),
    FOREIGN KEY (proiect_id) REFERENCES proiect(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proiect_id INT NOT NULL,
    booked_by INT NOT NULL,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    FOREIGN KEY (proiect_id) REFERENCES proiect(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE album (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    artist_id INT NOT NULL,
    release_year INT DEFAULT NULL,
    genre VARCHAR(100),
    FOREIGN KEY (artist_id) REFERENCES artist(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE piesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proiect_id INT NOT NULL,
    album_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    duration INT NOT NULL, -- durata in secunde
    release_year INT DEFAULT NULL,
    status ENUM('draft','released','archived') DEFAULT 'draft',
    FOREIGN KEY (proiect_id) REFERENCES proiect(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (album_id) REFERENCES album(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE mesaj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(150) NOT NULL,
    sender_email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_archived BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS refresh_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE ,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  user_agent VARCHAR(255),
  ip VARCHAR(45),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE project_file (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT DEFAULT 0,
    is_directory BOOLEAN DEFAULT FALSE,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    file_size INT DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES proiect(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CHECK (parent_id >= 0)
);