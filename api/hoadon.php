<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':  getHoaDon();  break;
    case 'POST': addHoaDon();  break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getHoaDon() {
    global $conn;
    $sql = "SELECT hd.MaPhieuHoaDon, hd.NgayLapHoaDon, hd.MaKhachHang,
                   kh.HoTenKhachHang,
                   GROUP_CONCAT(s.TenSach SEPARATOR ', ') as DanhSachSach,
                   SUM(ct.SoLuongBan) as TongSoLuong
            FROM PHIEUHOADON hd
            LEFT JOIN KHACHHANG kh ON kh.MaKhachHang = hd.MaKhachHang
            LEFT JOIN CHITIETPHIEUHOADON ct ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon
            LEFT JOIN SACH s ON s.MaSach = ct.MaSach
            GROUP BY hd.MaPhieuHoaDon
            ORDER BY hd.NgayLapHoaDon DESC";
    $stmt = $conn->query($sql);
    json_success($stmt->fetchAll());
}

function addHoaDon() {
    global $conn;
    $body     = json_decode(file_get_contents('php://input'), true);
    $maKH     = trim($body['MaKhachHang'] ?? '');
    $maSach   = trim($body['MaSach']      ?? '');
    $soLuong  = (int)($body['SoLuong']    ?? 0);

    if (!$maKH || !$maSach || $soLuong <= 0) {
        json_error('Vui lòng điền đầy đủ thông tin hóa đơn.');
    }

    // Lấy tham số quy định
    $thamso = $conn->query("SELECT * FROM THAMSO LIMIT 1")->fetch();

    // Kiểm tra khách hàng
    $kh = $conn->prepare("SELECT SoTienNo FROM KHACHHANG WHERE MaKhachHang = ?");
    $kh->execute([$maKH]);
    $khData = $kh->fetch();
    if (!$khData) json_error('Khách hàng không tồn tại.', 404);

    // Kiểm tra sách
    $sach = $conn->prepare("SELECT SoLuongTon, DonGia FROM SACH WHERE MaSach = ?");
    $sach->execute([$maSach]);
    $sachData = $sach->fetch();
    if (!$sachData) json_error('Sách không tồn tại.', 404);

    // Kiểm tra quy định nợ
    if ($khData['SoTienNo'] > $thamso['SoTienNoToiDa']) {
        json_error(
            "Khách hàng đang nợ " . number_format($khData['SoTienNo']) . "đ, vượt quá định mức " . number_format($thamso['SoTienNoToiDa']) . "đ. Vui lòng thanh toán trước."
        );
    }

    // Kiểm tra quy định tồn kho sau bán
    $tonSauKhiBan = $sachData['SoLuongTon'] - $soLuong;
    if ($tonSauKhiBan < $thamso['SoLuongTonSauToiThieu']) {
        json_error(
            "Tồn sau khi bán ($tonSauKhiBan cuốn) nhỏ hơn mức tối thiểu quy định ({$thamso['SoLuongTonSauToiThieu']} cuốn)."
        );
    }

    if ($soLuong > $sachData['SoLuongTon']) {
        json_error("Số lượng bán ($soLuong) vượt quá tồn kho ({$sachData['SoLuongTon']}).");
    }

    // Tính tiền
    $giaBan   = $sachData['DonGia'] * $thamso['DonGiaBanYeuCau'];
    $tongTien = $giaBan * $soLuong;

    // Tạo hóa đơn (transaction)
    $conn->beginTransaction();
    try {
        // Tạo phiếu hóa đơn
        $hd = $conn->prepare("INSERT INTO PHIEUHOADON (NgayLapHoaDon, MaKhachHang) VALUES (NOW(), ?)");
        $hd->execute([$maKH]);
        $maHD = $conn->lastInsertId();

        // Chi tiết
        $ct = $conn->prepare("INSERT INTO CHITIETPHIEUHOADON (MaPhieuHoaDon, MaSach, SoLuongBan) VALUES (?,?,?)");
        $ct->execute([$maHD, $maSach, $soLuong]);

        // Cập nhật tồn kho
        $upSach = $conn->prepare("UPDATE SACH SET SoLuongTon = SoLuongTon - ? WHERE MaSach = ?");
        $upSach->execute([$soLuong, $maSach]);

        // Cập nhật nợ khách hàng
        $upKH = $conn->prepare("UPDATE KHACHHANG SET SoTienNo = SoTienNo + ? WHERE MaKhachHang = ?");
        $upKH->execute([$tongTien, $maKH]);

        $conn->commit();
        json_success([
            'MaPhieuHoaDon' => $maHD,
            'TongTien'      => $tongTien,
            'GiaBan'        => $giaBan,
        ], "Lập hóa đơn thành công! Tổng tiền: " . number_format($tongTien) . "đ");

    } catch (Exception $e) {
        $conn->rollBack();
        json_error('Lỗi tạo hóa đơn: ' . $e->getMessage(), 500);
    }
}
?>
