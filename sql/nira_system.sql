-- Somalia National Identification & Registration Authority (NIRA) System
-- Database Schema

CREATE DATABASE IF NOT EXISTS nira_system;
USE nira_system;

-- Citizens table
CREATE TABLE citizens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nin VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    dob DATE NOT NULL,
    region VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    face_encoding TEXT,
    fingerprint_template TEXT,
    photo VARCHAR(255),
    birth_certificate VARCHAR(255),
    passport VARCHAR(255),
    residency_proof VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'officer', 'verifier') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Verification logs table
CREATE TABLE verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nin VARCHAR(20) NOT NULL,
    verifier_id INT,
    verification_type ENUM('web', 'api') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    result ENUM('success', 'failed', 'not_found') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (verifier_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- ID card generation logs
CREATE TABLE id_card_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    generated_by INT NOT NULL,
    card_type ENUM('digital', 'physical') NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES citizens(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES admins(id) ON DELETE CASCADE
);

-- System settings
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, email, password_hash, role, full_name) VALUES 
('admin', 'admin@nira.gov.so', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('system_name', 'Somalia NIRA System', 'Official name of the system'),
('version', '1.0.0', 'System version'),
('maintenance_mode', '0', 'System maintenance mode (0=off, 1=on)'),
('max_file_size', '5242880', 'Maximum file upload size in bytes (5MB)'),
('allowed_extensions', 'jpg,jpeg,png,pdf', 'Allowed file extensions for uploads');

-- Create indexes for better performance
CREATE INDEX idx_citizens_nin ON citizens(nin);
CREATE INDEX idx_citizens_status ON citizens(status);
CREATE INDEX idx_citizens_region ON citizens(region);
CREATE INDEX idx_verification_logs_nin ON verification_logs(nin);
CREATE INDEX idx_verification_logs_created_at ON verification_logs(created_at);
