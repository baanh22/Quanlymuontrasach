<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Phương thức không hợp lệ', 405);
}

$tongSach    = $conn->query("SELECT SUM(SoLuongTon) as total FROM SACH")->fetch()['total'] ?? 0;
$soDauSach   = $conn->query("SELECT COUNT(*) as total FROM SACH")->fetch()['total'] ?? 0;
$soKhachHang = $conn->query("SELECT COUNT(*) as total FROM KHACHHANG")->fetch()['total'] ?? 0;
$soTienNo    = $conn->query("SELECT SUM(SoTienNo) as total FROM KHACHHANG")->fetch()['total'] ?? 0;
$soHoaDon    = $conn->query("SELECT COUNT(*) as total FROM PHIEUHOADON")->fetch()['total'] ?? 0;
$soPhieuNhap = $conn->query("SELECT COUNT(*) as total FROM PHIEUNHAP")->fetch()['total'] ?? 0;

$sachSapHet = $conn->query("SELECT MaSach, TenSach, TheLoai, SoLuongTon FROM SACH WHERE SoLuongTon < 100 ORDER BY SoLuongTon ASC LIMIT 5")->fetchAll();

// Top 5 sách bán nhiều nhất
$topSach = $conn->query("
    SELECT s.TenSach, SUM(ct.SoLuongBan) as TongBan
    FROM CHITIETPHIEUHOADON ct
    JOIN SACH s ON s.MaSach = ct.MaSach
    GROUP BY ct.MaSach
    ORDER BY TongBan DESC
    LIMIT 5
")->fetchAll();

json_success([
    'tongSach'    => (int)$tongSach,
    'soDauSach'   => (int)$soDauSach,
    'soKhachHang' => (int)$soKhachHang,
    'soTienNo'    => (float)$soTienNo,
    'soHoaDon'    => (int)$soHoaDon,
    'soPhieuNhap' => (int)$soPhieuNhap,
    'sachSapHet'  => $sachSapHet,
    'topSach'     => $topSach,
]);
?>
