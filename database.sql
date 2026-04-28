-- Database for RPS Application
CREATE DATABASE IF NOT EXISTS rps_db;
USE rps_db;

-- Table for Program Studi (Master Data)
CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_prodi VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Prodi Data
INSERT IGNORE INTO prodi (nama_prodi) VALUES 
('Teknik Informatika'),
('Sistem Informasi'),
('Teknik Elektro'),
('Teknik Sipil'),
('Arsitektur');

-- Table for Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip_nidn VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    prodi_id INT NOT NULL,
    role ENUM('Dosen', 'Kaprodi', 'Admin') DEFAULT 'Dosen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);

-- Seed default Admin User
-- Password: AdminPassword123!
INSERT IGNORE INTO users (nip_nidn, nama_lengkap, email, no_hp, password, prodi_id, role) VALUES 
('123456789012345678', 'Admin RPS', 'admin@rps.com', '081111111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Admin');
