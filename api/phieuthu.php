<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':  getPhieuThu();  break;
    case 'POST': addPhieuThu();  break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getPhieuThu() {
    global $conn;
    $sql = "SELECT pt.MaPhieuThu, pt.NgayThuTien, pt.SoTienThu, pt.MaKhachHang, kh.HoTenKhachHang, kh.SoTienNo 
            FROM PHIEUTHUTIEN pt
            JOIN KHACHHANG kh ON pt.MaKhachHang = kh.MaKhachHang
            ORDER BY pt.NgayThuTien DESC";
    $stmt = $conn->query($sql);
    json_success($stmt->fetchAll());
}

function addPhieuThu() {
    global $conn;
    $body     = json_decode(file_get_contents('php://input'), true);
    $maKH     = trim($body['MaKhachHang'] ?? '');
    $soTien   = (float)($body['SoTienThu'] ?? 0);

    if (!$maKH || $soTien <= 0) {
        json_error('Vui lòng chọn khách hàng và nhập số tiền thu (lớn hơn 0).');
    }

    // Kiểm tra quy định (QuyDinh=1 -> số tiền thu không vượt quá số tiền nợ)
    $thamso = $conn->query("SELECT QuyDinh FROM THAMSO LIMIT 1")->fetch();
    $quyDinhApdung = $thamso['QuyDinh'] ?? 1;

    $kh = $conn->prepare("SELECT SoTienNo, HoTenKhachHang FROM KHACHHANG WHERE MaKhachHang = ?");
    $kh->execute([$maKH]);
    $khData = $kh->fetch();

    if (!$khData) json_error('Khách hàng không tồn tại.', 404);

    if ($quyDinhApdung == 1 && $soTien > $khData['SoTienNo']) {
        json_error(
            "Theo quy định hiện hành, số tiền thu (" . number_format($soTien) . "đ) không được phép vượt quá số tiền khách hàng đang nợ (" . number_format($khData['SoTienNo']) . "đ)."
        );
    }

    // Tạo phiếu thu (Transaction)
    $conn->beginTransaction();
    try {
        // Lưu phiếu
        $stmt = $conn->prepare("INSERT INTO PHIEUTHUTIEN (MaKhachHang, NgayThuTien, SoTienThu) VALUES (?, NOW(), ?)");
        $stmt->execute([$maKH, $soTien]);
        $maPT = $conn->lastInsertId();

        // Trừ nợ khách hàng
        $up = $conn->prepare("UPDATE KHACHHANG SET SoTienNo = SoTienNo - ? WHERE MaKhachHang = ?");
        $up->execute([$soTien, $maKH]);

        $conn->commit();
        json_success([
            'MaPhieuThu' => $maPT,
            'NoConLai'   => $khData['SoTienNo'] - $soTien
        ], "Lập phiếu thu tiền thành công! Nợ còn lại: " . number_format($khData['SoTienNo'] - $soTien) . "đ");

    } catch (Exception $e) {
        $conn->rollBack();
        json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
    }
}
?>
