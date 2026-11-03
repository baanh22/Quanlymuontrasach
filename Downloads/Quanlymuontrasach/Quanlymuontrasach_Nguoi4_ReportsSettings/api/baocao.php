<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Phuong thuc khong hop le.', 405);
$type = $_GET['type'] ?? '';
$month = filter_var($_GET['thang'] ?? date('n'), FILTER_VALIDATE_INT);
$year = filter_var($_GET['nam'] ?? date('Y'), FILTER_VALIDATE_INT);
if ($month === false || $month < 1 || $month > 12 || $year === false || $year < 2000 || $year > 2100) {
    json_error('Thang hoac nam khong hop le.');
}

$start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d H:i:s');

try {
    if ($type === 'tonkho') {
        $sql = "SELECT s.MaSach, s.TenSach,
            COALESCE((SELECT SUM(ct.SoLuongNhap) FROM CHITIETPHIEUNHAP ct JOIN PHIEUNHAP pn ON pn.MaPhieuNhap = ct.MaPhieuNhap WHERE ct.MaSach = s.MaSach AND pn.NgayNhap < :opening_import), 0) AS NhapDauKy,
            COALESCE((SELECT SUM(ct.SoLuongBan) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON hd ON hd.MaPhieuHoaDon = ct.MaPhieuHoaDon WHERE ct.MaSach = s.MaSach AND hd.NgayLapHoaDon < :opening_sale), 0) AS BanDauKy,
            COALESCE((SELECT SUM(ct.SoLuongNhap) FROM CHITIETPHIEUNHAP ct JOIN PHIEUNHAP pn ON pn.MaPhieuNhap = ct.MaPhieuNhap WHERE ct.MaSach = s.MaSach AND pn.NgayNhap >= :period_import_from AND pn.NgayNhap < :period_import_to), 0) AS NhapTrongKy,
            COALESCE((SELECT SUM(ct.SoLuongBan) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON hd ON hd.MaPhieuHoaDon = ct.MaPhieuHoaDon WHERE ct.MaSach = s.MaSach AND hd.NgayLapHoaDon >= :period_sale_from AND hd.NgayLapHoaDon < :period_sale_to), 0) AS BanTrongKy
            FROM SACH s ORDER BY s.TenSach";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'opening_import' => $start,
            'opening_sale' => $start,
            'period_import_from' => $start,
            'period_import_to' => $end,
            'period_sale_from' => $start,
            'period_sale_to' => $end,
        ]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $opening = (int)$row['NhapDauKy'] - (int)$row['BanDauKy'];
            $imported = (int)$row['NhapTrongKy'];
            $sold = (int)$row['BanTrongKy'];
            $closing = $opening + $imported - $sold;
            if ($opening !== 0 || $imported !== 0 || $sold !== 0 || $closing !== 0) {
                $rows[] = [
                    'MaSach' => $row['MaSach'], 'TenSach' => $row['TenSach'],
                    'TonDau' => $opening, 'Nhap' => $imported, 'Ban' => $sold,
                    'PhatSinh' => $imported - $sold, 'TonCuoi' => $closing,
                ];
            }
        }
        json_success($rows);
    }

    if ($type === 'congno') {
        $factor = (float)$conn->query('SELECT DonGiaBanYeuCau FROM THAMSO LIMIT 1')->fetchColumn();
        $sql = "SELECT kh.MaKhachHang, kh.HoTenKhachHang,
            (SELECT COALESCE(SUM(ct.SoLuongBan * s.DonGia * :factor_open), 0) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON hd ON hd.MaPhieuHoaDon = ct.MaPhieuHoaDon JOIN SACH s ON s.MaSach = ct.MaSach WHERE hd.MaKhachHang = kh.MaKhachHang AND hd.NgayLapHoaDon < :opening_invoice) AS NoTangDauKy,
            (SELECT COALESCE(SUM(pt.SoTienThu), 0) FROM PHIEUTHUTIEN pt WHERE pt.MaKhachHang = kh.MaKhachHang AND pt.NgayThuTien < :opening_receipt) AS NoGiamDauKy,
            (SELECT COALESCE(SUM(ct.SoLuongBan * s.DonGia * :factor_period), 0) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON hd ON hd.MaPhieuHoaDon = ct.MaPhieuHoaDon JOIN SACH s ON s.MaSach = ct.MaSach WHERE hd.MaKhachHang = kh.MaKhachHang AND hd.NgayLapHoaDon >= :period_invoice_from AND hd.NgayLapHoaDon < :period_invoice_to) AS NoTangTrongKy,
            (SELECT COALESCE(SUM(pt.SoTienThu), 0) FROM PHIEUTHUTIEN pt WHERE pt.MaKhachHang = kh.MaKhachHang AND pt.NgayThuTien >= :period_receipt_from AND pt.NgayThuTien < :period_receipt_to) AS NoGiamTrongKy
            FROM KHACHHANG kh ORDER BY kh.HoTenKhachHang";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'factor_open' => $factor, 'factor_period' => $factor,
            'opening_invoice' => $start, 'opening_receipt' => $start,
            'period_invoice_from' => $start, 'period_invoice_to' => $end,
            'period_receipt_from' => $start, 'period_receipt_to' => $end,
        ]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $opening = (float)$row['NoTangDauKy'] - (float)$row['NoGiamDauKy'];
            $invoiced = (float)$row['NoTangTrongKy'];
            $collected = (float)$row['NoGiamTrongKy'];
            $closing = $opening + $invoiced - $collected;
            if ($opening != 0.0 || $invoiced != 0.0 || $collected != 0.0 || $closing != 0.0) {
                $rows[] = [
                    'MaKhachHang' => $row['MaKhachHang'], 'HoTenKhachHang' => $row['HoTenKhachHang'],
                    'NoDau' => round($opening), 'PhatSinh' => round($invoiced),
                    'DaThu' => round($collected), 'NoCuoi' => round($closing),
                ];
            }
        }
        usort($rows, fn($left, $right) => $right['NoCuoi'] <=> $left['NoCuoi']);
        json_success($rows);
    }

    json_error('Loai bao cao khong hop le.');
} catch (PDOException $e) {
    json_error('Khong the tao bao cao. Hay kiem tra schema database.', 500);
}
