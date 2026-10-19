<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Phuong thuc khong hop le.', 405);

try {
    $metrics = [
        'tongSach' => (int)$conn->query('SELECT COALESCE(SUM(SoLuongTon), 0) FROM SACH')->fetchColumn(),
        'soDauSach' => (int)$conn->query('SELECT COUNT(*) FROM SACH')->fetchColumn(),
        'soKhachHang' => (int)$conn->query('SELECT COUNT(*) FROM KHACHHANG')->fetchColumn(),
        'soTienNo' => (float)$conn->query('SELECT COALESCE(SUM(SoTienNo), 0) FROM KHACHHANG')->fetchColumn(),
        'soHoaDon' => (int)$conn->query('SELECT COUNT(*) FROM PHIEUHOADON')->fetchColumn(),
        'soPhieuNhap' => (int)$conn->query('SELECT COUNT(*) FROM PHIEUNHAP')->fetchColumn(),
    ];

    $metrics['sachSapHet'] = $conn->query('SELECT MaSach, TenSach, TheLoai, SoLuongTon FROM SACH WHERE SoLuongTon < 100 ORDER BY SoLuongTon ASC, TenSach ASC LIMIT 5')->fetchAll();
    $metrics['topSach'] = $conn->query("SELECT s.MaSach, s.TenSach, SUM(ct.SoLuongBan) AS TongBan
        FROM CHITIETPHIEUHOADON ct
        JOIN SACH s ON s.MaSach = ct.MaSach
        GROUP BY s.MaSach, s.TenSach
        ORDER BY TongBan DESC, s.TenSach ASC
        LIMIT 5")->fetchAll();

    json_success($metrics);
} catch (PDOException $e) {
    json_error('Khong the tai thong ke. Hay kiem tra schema database.', 500);
}
