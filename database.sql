-- ============================================
-- SCRIPT TẠO CSDL VÀ DỮ LIỆU MẪU: QUẢN LÝ NHÀ SÁCH
-- ============================================

CREATE DATABASE IF NOT EXISTS `QuanLyNhaSach` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `QuanLyNhaSach`;

-- 1. Bảng TAIKHOAN
DROP TABLE IF EXISTS `CHITIETPHIEUHOADON`;
DROP TABLE IF EXISTS `CHITIETPHIEUNHAP`;
DROP TABLE IF EXISTS `PHIEUHOADON`;
DROP TABLE IF EXISTS `PHIEUNHAP`;
DROP TABLE IF EXISTS `PHIEUTHUTIEN`;
DROP TABLE IF EXISTS `SACH`;
DROP TABLE IF EXISTS `KHACHHANG`;
DROP TABLE IF EXISTS `THAMSO`;
DROP TABLE IF EXISTS `TAIKHOAN`;

CREATE TABLE `TAIKHOAN` (
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'User',
  PRIMARY KEY (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng THAMSO
CREATE TABLE `THAMSO` (
  `SoLuongNhapItNhat` int(11) NOT NULL DEFAULT 150,
  `SoLuongTonToiDaTruocNhap` int(11) NOT NULL DEFAULT 300,
  `SoTienNoToiDa` decimal(15,2) NOT NULL DEFAULT 20000.00,
  `SoLuongTonSauToiThieu` int(11) NOT NULL DEFAULT 20,
  `DonGiaBanYeuCau` float NOT NULL DEFAULT 1.05,
  `QuyDinh` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng SACH
CREATE TABLE `SACH` (
  `MaSach` varchar(20) NOT NULL,
  `TenSach` varchar(255) NOT NULL,
  `TheLoai` varchar(100) DEFAULT NULL,
  `TacGia` varchar(100) DEFAULT NULL,
  `SoLuongTon` int(11) NOT NULL DEFAULT 0,
  `DonGia` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`MaSach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng KHACHHANG
CREATE TABLE `KHACHHANG` (
  `MaKhachHang` varchar(20) NOT NULL,
  `HoTenKhachHang` varchar(100) NOT NULL,
  `DiaChi` varchar(255) DEFAULT NULL,
  `DienThoai` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `SoTienNo` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`MaKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng PHIEUNHAP
CREATE TABLE `PHIEUNHAP` (
  `MaPhieuNhap` int(11) NOT NULL AUTO_INCREMENT,
  `NgayNhap` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaPhieuNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng CHITIETPHIEUNHAP
CREATE TABLE `CHITIETPHIEUNHAP` (
  `MaPhieuNhap` int(11) NOT NULL,
  `MaSach` varchar(20) NOT NULL,
  `SoLuongNhap` int(11) NOT NULL,
  `DonGiaNhap` decimal(15,2) NOT NULL,
  PRIMARY KEY (`MaPhieuNhap`,`MaSach`),
  KEY `FK_CTPN_SACH` (`MaSach`),
  CONSTRAINT `FK_CTPN_PN` FOREIGN KEY (`MaPhieuNhap`) REFERENCES `PHIEUNHAP` (`MaPhieuNhap`) ON DELETE CASCADE,
  CONSTRAINT `FK_CTPN_SACH` FOREIGN KEY (`MaSach`) REFERENCES `SACH` (`MaSach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng PHIEUHOADON
CREATE TABLE `PHIEUHOADON` (
  `MaPhieuHoaDon` int(11) NOT NULL AUTO_INCREMENT,
  `NgayLapHoaDon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `MaKhachHang` varchar(20) NOT NULL,
  PRIMARY KEY (`MaPhieuHoaDon`),
  KEY `FK_HD_KH` (`MaKhachHang`),
  CONSTRAINT `FK_HD_KH` FOREIGN KEY (`MaKhachHang`) REFERENCES `KHACHHANG` (`MaKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng CHITIETPHIEUHOADON
CREATE TABLE `CHITIETPHIEUHOADON` (
  `MaPhieuHoaDon` int(11) NOT NULL,
  `MaSach` varchar(20) NOT NULL,
  `SoLuongBan` int(11) NOT NULL,
  PRIMARY KEY (`MaPhieuHoaDon`,`MaSach`),
  KEY `FK_CTHD_SACH` (`MaSach`),
  CONSTRAINT `FK_CTHD_HD` FOREIGN KEY (`MaPhieuHoaDon`) REFERENCES `PHIEUHOADON` (`MaPhieuHoaDon`) ON DELETE CASCADE,
  CONSTRAINT `FK_CTHD_SACH` FOREIGN KEY (`MaSach`) REFERENCES `SACH` (`MaSach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng PHIEUTHUTIEN
CREATE TABLE `PHIEUTHUTIEN` (
  `MaPhieuThu` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` varchar(20) NOT NULL,
  `NgayThuTien` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SoTienThu` decimal(15,2) NOT NULL,
  PRIMARY KEY (`MaPhieuThu`),
  KEY `FK_PT_KH` (`MaKhachHang`),
  CONSTRAINT `FK_PT_KH` FOREIGN KEY (`MaKhachHang`) REFERENCES `KHACHHANG` (`MaKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DỮ LIỆU KHỞI TẠO MẪU (SEED DATA)
-- ============================================

-- Thêm Tham số mặc định (QĐ1, QĐ2, QĐ3, QĐ4)
INSERT INTO `THAMSO` (`SoLuongNhapItNhat`, `SoLuongTonToiDaTruocNhap`, `SoTienNoToiDa`, `SoLuongTonSauToiThieu`, `DonGiaBanYeuCau`, `QuyDinh`) 
VALUES (150, 300, 20000.00, 20, 1.05, 1);

-- Thêm Tài khoản đăng nhập:
-- admin / admin (MD5: 21232f297a57a5a743894a0e4a801fc3)
-- nhanvien / 123456 (MD5: e10adc3949ba59abbe56e057f20f883e)
INSERT INTO `TAIKHOAN` (`Username`, `Password`, `FullName`, `Role`) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'Quản Trị Viên', 'Admin'),
('nhanvien', 'e10adc3949ba59abbe56e057f20f883e', 'Trần Văn An', 'User');

-- Thêm Sách mẫu
INSERT INTO `SACH` (`MaSach`, `TenSach`, `TheLoai`, `TacGia`, `SoLuongTon`, `DonGia`) VALUES
('S001', 'Lập Trình Hướng Đối Tượng Java', 'Công nghệ thông tin', 'Đoàn Văn Ban', 120, 85000.00),
('S002', 'Cấu Trúc Dữ Liệu và Giải Thuật', 'Công nghệ thông tin', 'Đinh Mạnh Tường', 95, 75000.00),
('S003', 'Đắc Nhân Tâm', 'Kỹ năng sống', 'Dale Carnegie', 250, 68000.00),
('S004', 'Nhà Giả Kim', 'Tiểu thuyết văn học', 'Paulo Coelho', 180, 59000.00),
('S005', 'Dế Mèn Phiêu Lưu Ký', 'Thiếu nhi', 'Tô Hoài', 320, 45000.00),
('S006', 'Thiết Kế Cơ Sở Dữ Liệu', 'Công nghệ thông tin', 'Nguyễn Kim Anh', 80, 92000.00);

-- Thêm Khách hàng mẫu
INSERT INTO `KHACHHANG` (`MaKhachHang`, `HoTenKhachHang`, `DiaChi`, `DienThoai`, `Email`, `SoTienNo`) VALUES
('KH001', 'Nguyễn Văn Bình', '123 Lê Lợi, Quận 1, TP.HCM', '0901234567', 'binh.nguyen@gmail.com', 0.00),
('KH002', 'Trần Thị Mai', '45 Hùng Vương, Quận 5, TP.HCM', '0912345678', 'mai.tran@gmail.com', 15000.00),
('KH003', 'Lê Hoàng Nam', '78 CMT8, Quận 3, TP.HCM', '0987654321', 'nam.le@gmail.com', 0.00),
('KH004', 'Phạm Minh Đức', '12 Điện Biên Phủ, Bình Thạnh', '0933445566', 'duc.pm@gmail.com', 18000.00);
