<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':  getNhapSach();  break;
    case 'POST': addNhapSach();  break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getNhapSach() {
    global $conn;
    $sql = "SELECT pn.MaPhieuNhap, pn.NgayNhap,
                   GROUP_CONCAT(s.TenSach SEPARATOR ', ') as DanhSachSach,
                   SUM(ct.SoLuongNhap) as TongSoLuong,
                   SUM(ct.SoLuongNhap * ct.DonGiaNhap) as TongTien
            FROM PHIEUNHAP pn
            LEFT JOIN CHITIETPHIEUNHAP ct ON ct.MaPhieuNhap = pn.MaPhieuNhap
            LEFT JOIN SACH s ON s.MaSach = ct.MaSach
            GROUP BY pn.MaPhieuNhap
            ORDER BY pn.NgayNhap DESC";
    $stmt = $conn->query($sql);
    json_success($stmt->fetchAll());
}

function addNhapSach() {
    global $conn;
    $body    = json_decode(file_get_contents('php://input'), true);
    $maSach  = trim($body['MaSach']   ?? '');
    $soLuong = (int)($body['SoLuong'] ?? 0);
    $donGia  = (float)($body['DonGia'] ?? 0);

    if (!$maSach || $soLuong <= 0 || $donGia <= 0) {
        json_error('Vui lòng điền đầy đủ thông tin phiếu nhập.');
    }

    // Lấy tham số quy định
    $thamso = $conn->query("SELECT * FROM THAMSO LIMIT 1")->fetch();

    // Kiểm tra số lượng nhập tối thiểu
    if ($soLuong < $thamso['SoLuongNhapItNhat']) {
        json_error("Số lượng nhập ({$soLuong}) phải lớn hơn hoặc bằng {$thamso['SoLuongNhapItNhat']} cuốn theo quy định.");
    }

    // Kiểm tra tồn kho hiện tại
    $sach = $conn->prepare("SELECT SoLuongTon, TenSach FROM SACH WHERE MaSach = ?");
    $sach->execute([$maSach]);
    $sachData = $sach->fetch();
    if (!$sachData) json_error('Sách không tồn tại.', 404);

    if ($sachData['SoLuongTon'] >= $thamso['SoLuongTonToiDaTruocNhap']) {
        json_error(
            "Chỉ nhập sách có lượng tồn nhỏ hơn {$thamso['SoLuongTonToiDaTruocNhap']} cuốn. Hiện tồn: {$sachData['SoLuongTon']} cuốn."
        );
    }

    // Tạo phiếu nhập (transaction)
    $conn->beginTransaction();
    try {
        $pn = $conn->prepare("INSERT INTO PHIEUNHAP (NgayNhap) VALUES (NOW())");
        $pn->execute();
        $maPieuNhap = $conn->lastInsertId();

        $ct = $conn->prepare("INSERT INTO CHITIETPHIEUNHAP (MaPhieuNhap, MaSach, SoLuongNhap, DonGiaNhap) VALUES (?,?,?,?)");
        $ct->execute([$maPieuNhap, $maSach, $soLuong, $donGia]);

        // Cập nhật tồn kho
        $upSach = $conn->prepare("UPDATE SACH SET SoLuongTon = SoLuongTon + ?, DonGia = ? WHERE MaSach = ?");
        $upSach->execute([$soLuong, $donGia, $maSach]);

        $conn->commit();
        json_success([
            'MaPhieuNhap' => $maPieuNhap,
            'TonMoi'      => $sachData['SoLuongTon'] + $soLuong,
        ], "Lập phiếu nhập thành công! Tồn kho mới: " . ($sachData['SoLuongTon'] + $soLuong) . " cuốn.");

    } catch (Exception $e) {
        $conn->rollBack();
        json_error('Lỗi tạo phiếu nhập: ' . $e->getMessage(), 500);
    }
}
?>
