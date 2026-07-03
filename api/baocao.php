<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') json_error('Phương thức không hợp lệ', 405);

$type  = $_GET['type'] ?? '';
$thang = (int)($_GET['thang'] ?? date('m'));
$nam   = (int)($_GET['nam'] ?? date('Y'));

if ($type === 'tonkho') {
    getBaoCaoTon($thang, $nam);
} elseif ($type === 'congno') {
    getBaoCaoCongNo($thang, $nam);
} else {
    json_error('Loại báo cáo không hợp lệ.', 400);
}

function getBaoCaoTon($thang, $nam) {
    global $conn;
    // Thời hạn: từ đầu thời gian đến đầu tháng đang chọn
    $firstDayOfMonth = "$nam-" . str_pad($thang, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
    // Thời hạn: hết tháng đang chọn
    $firstDayOfNextMonth = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($firstDayOfMonth)));

    $sql = "
        SELECT 
            s.MaSach, s.TenSach,
            
            -- Tính nhập đầu kỳ
            COALESCE((SELECT SUM(ct.SoLuongNhap) FROM CHITIETPHIEUNHAP ct JOIN PHIEUNHAP pn ON ct.MaPhieuNhap = pn.MaPhieuNhap WHERE ct.MaSach = s.MaSach AND pn.NgayNhap < :start1), 0) as NhapDauKy,
            
            -- Tính bán đầu kỳ
            COALESCE((SELECT SUM(ct.SoLuongBan) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON pn ON ct.MaPhieuHoaDon = pn.MaPhieuHoaDon WHERE ct.MaSach = s.MaSach AND pn.NgayLapHoaDon < :start2), 0) as BanDauKy,
            
            -- Tính nhập trong kỳ
            COALESCE((SELECT SUM(ct.SoLuongNhap) FROM CHITIETPHIEUNHAP ct JOIN PHIEUNHAP pn ON ct.MaPhieuNhap = pn.MaPhieuNhap WHERE ct.MaSach = s.MaSach AND pn.NgayNhap >= :start3 AND pn.NgayNhap < :end1), 0) as NhapTrongKy,
            
            -- Tính bán trong kỳ
            COALESCE((SELECT SUM(ct.SoLuongBan) FROM CHITIETPHIEUHOADON ct JOIN PHIEUHOADON pn ON ct.MaPhieuHoaDon = pn.MaPhieuHoaDon WHERE ct.MaSach = s.MaSach AND pn.NgayLapHoaDon >= :start4 AND pn.NgayLapHoaDon < :end2), 0) as BanTrongKy
            
        FROM SACH s
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'start1' => $firstDayOfMonth,
        'start2' => $firstDayOfMonth,
        'start3' => $firstDayOfMonth, 'end1' => $firstDayOfNextMonth,
        'start4' => $firstDayOfMonth, 'end2' => $firstDayOfNextMonth
    ]);
    
    $sachData = $stmt->fetchAll();
    $result = [];
    foreach($sachData as $s) {
        $tonDau = $s['NhapDauKy'] - $s['BanDauKy'];
        $tonPhatSinh = $s['NhapTrongKy'] - $s['BanTrongKy'];
        $tonCuoi = $tonDau + $tonPhatSinh;
        
        // Chỉ hiện những sách có giao dịch hoặc có tồn
        if ($tonDau > 0 || $s['NhapTrongKy'] > 0 || $s['BanTrongKy'] > 0 || $tonCuoi > 0) {
            $result[] = [
                'MaSach' => $s['MaSach'],
                'TenSach' => $s['TenSach'],
                'TonDau' => $tonDau,
                'Nhap' => $s['NhapTrongKy'],
                'Ban' => $s['BanTrongKy'],
                'PhatSinh' => $tonPhatSinh,
                'TonCuoi' => $tonCuoi
            ];
        }
    }
    
    json_success($result);
}

function getBaoCaoCongNo($thang, $nam) {
    global $conn;
    $firstDayOfMonth = "$nam-" . str_pad($thang, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
    $firstDayOfNextMonth = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($firstDayOfMonth)));
    
    // Tham số giá bán (Bảng CHITIETPHIEUHOADON lưu SoLuongBan, không lưu trực tiếp giá bán ở quá khứ, 
    // nhưng để gọn nhẹ ta lấy DonGia hiện tại * DonGiaBanYeuCau cho báo cáo On-the-fly. 
    // Trong thực tế cần lưu giá bán trực tiếp ở chi tiết hóa đơn).
    // Ở đây ta JOIN với SACH để lấy giá.
    $thamso = $conn->query("SELECT DonGiaBanYeuCau FROM THAMSO LIMIT 1")->fetch();
    $tiLe = $thamso['DonGiaBanYeuCau'];

    $sql = "
        SELECT 
            kh.MaKhachHang, kh.HoTenKhachHang,
            
            -- Tính Hóa đơn (Nợ tăng) đầu kỳ
            (SELECT COALESCE(SUM(ct.SoLuongBan * s.DonGia * :tile1), 0) 
             FROM CHITIETPHIEUHOADON ct 
             JOIN PHIEUHOADON hd ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon 
             JOIN SACH s ON s.MaSach = ct.MaSach 
             WHERE hd.MaKhachHang = kh.MaKhachHang AND hd.NgayLapHoaDon < :start1
            ) as NoTangDauKy,
            
            -- Tính Phiếu thu (Nợ giảm) đầu kỳ
            (SELECT COALESCE(SUM(pt.SoTienThu), 0) 
             FROM PHIEUTHUTIEN pt 
             WHERE pt.MaKhachHang = kh.MaKhachHang AND pt.NgayThuTien < :start2
            ) as NoGiamDauKy,
            
            -- Tính Hóa đơn (Nợ tăng) trong kỳ
            (SELECT COALESCE(SUM(ct.SoLuongBan * s.DonGia * :tile2), 0) 
             FROM CHITIETPHIEUHOADON ct 
             JOIN PHIEUHOADON hd ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon 
             JOIN SACH s ON s.MaSach = ct.MaSach 
             WHERE hd.MaKhachHang = kh.MaKhachHang AND hd.NgayLapHoaDon >= :start3 AND hd.NgayLapHoaDon < :end1
            ) as NoTangTrongKy,
            
            -- Tính Phiếu thu (Nợ giảm) trong kỳ
            (SELECT COALESCE(SUM(pt.SoTienThu), 0) 
             FROM PHIEUTHUTIEN pt 
             WHERE pt.MaKhachHang = kh.MaKhachHang AND pt.NgayThuTien >= :start4 AND pt.NgayThuTien < :end2
            ) as NoGiamTrongKy
            
        FROM KHACHHANG kh
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'tile1' => $tiLe, 'tile2' => $tiLe,
        'start1' => $firstDayOfMonth, 'start2' => $firstDayOfMonth,
        'start3' => $firstDayOfMonth, 'end1' => $firstDayOfNextMonth,
        'start4' => $firstDayOfMonth, 'end2' => $firstDayOfNextMonth
    ]);
    
    $khData = $stmt->fetchAll();
    $result = [];
    foreach($khData as $k) {
        $noDau = $k['NoTangDauKy'] - $k['NoGiamDauKy'];
        $noPhatSinh = $k['NoTangTrongKy']; // Nợ mới phát sinh
        $daThu = $k['NoGiamTrongKy']; // Tiền khách trả trong tháng
        $noCuoi = $noDau + $noPhatSinh - $daThu;
        
        // Theo DB BAOCAOCONGNO, NoPhatSinh có thể là sự chênh lệch ròng (hoặc chỉ Hóa đơn). Ta xuất Hóa đơn (Nợ tăng) làm NoPhatSinh
        if ($noDau > 0 || $noPhatSinh > 0 || $daThu > 0 || $noCuoi > 0) {
            $result[] = [
                'MaKhachHang' => $k['MaKhachHang'],
                'HoTenKhachHang' => $k['HoTenKhachHang'],
                'NoDau' => round($noDau),
                'PhatSinh' => round($noPhatSinh),
                'DaThu' => round($daThu),
                'NoCuoi' => round($noCuoi)
            ];
        }
    }
    
    // Sort báo cáo nợ giảm dần để dễ xem (người nợ nhiều lên trên)
    usort($result, function($a, $b) {
        return $b['NoCuoi'] <=> $a['NoCuoi'];
    });
    
    json_success($result);
}
?>
