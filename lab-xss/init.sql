-- =============================================
-- Lab XSS Injection - Database Initialization
-- =============================================

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT NOW()
);

INSERT INTO comments (author, content) VALUES
('Alice',   'Artikel ini sangat membantu, terima kasih!'),
('Bob',     'Penjelasannya mudah dipahami.'),
('Charlie', 'Saya sudah mencoba dan berhasil!');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    session_token VARCHAR(128),
    secret VARCHAR(200)
);

INSERT INTO users (username, password, email, role, session_token, secret) VALUES
('admin',   'admin123',   'admin@lab.local',   'admin', 'tok_admin_9f8e7d6c5b4a3210', 'FLAG{xss_session_hijack_admin}'),
('alice',   'alice_pass', 'alice@lab.local',   'user',  'tok_alice_1a2b3c4d5e6f7890', 'FLAG{xss_alice_cookie_stolen}'),
('bob',     'b0b_pass',   'bob@lab.local',     'user',  'tok_bob_abcdef1234567890',   'FLAG{xss_bob_cookie_stolen}');

CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT NOW()
);

INSERT INTO guestbook (name, message, ip_address) VALUES
('Visitor1', 'Selamat datang di guestbook!', '192.168.1.1'),
('Visitor2', 'Lab ini sangat edukatif.',      '192.168.1.2');

CREATE TABLE IF NOT EXISTS search_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(500),
    searched_at DATETIME DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    body TEXT,
    author VARCHAR(100),
    approved BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT NOW()
);

INSERT INTO feedback (title, body, author, approved) VALUES
('Saran fitur baru', 'Tambahkan fitur export PDF.', 'user1', TRUE),
('Bug report',       'Halaman login kadang error.',  'user2', TRUE);
