CREATE DATABASE IF NOT EXISTS QuanLyNhaSach_Nguoi1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QuanLyNhaSach_Nguoi1;

CREATE TABLE IF NOT EXISTS TAIKHOAN (
    Username VARCHAR(40) PRIMARY KEY,
    Password VARCHAR(255) NOT NULL,
    FullName VARCHAR(150) NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'User',
    CONSTRAINT chk_taikhoan_role CHECK (Role IN ('User', 'Admin'))
) ENGINE=InnoDB;

-- Tai khoan demo admin/admin123. MD5 chi dung cho mau cu va se duoc nang cap khi login thanh cong.
INSERT INTO TAIKHOAN (Username, Password, FullName, Role)
VALUES ('admin', '0192023a7bbd73250516f069df18b500', 'Quản trị viên', 'Admin')
ON DUPLICATE KEY UPDATE Username = VALUES(Username);
