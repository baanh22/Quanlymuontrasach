<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getNhapSach();
        break;
    case 'POST':
        addNhapSach();
        break;
    default:
        json_error('Phương thức không hợp lệ', 405);
}

function getNhapSach() {
    global $conn;

    $sql = "SELECT pn.MaPhieuNhap, pn.NgayNhap,
                   GROUP_CONCAT(s.TenSach SEPARATOR ', ') AS DanhSachSach,
                   SUM(ct.SoLuongNhap) AS TongSoLuong,
                   SUM(ct.SoLuongNhap * ct.DonGiaNhap) AS TongTien
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
    $body = json_decode(file_get_contents('php://input'), true);

    $maSach = trim($body['MaSach'] ?? '');
    $soLuong = (int)($body['SoLuong'] ?? 0);
    $donGia = (float)($body['DonGia'] ?? 0);

    if (!$maSach || $soLuong <= 0 || $donGia <= 0) {
        json_error('Vui lòng điền đầy đủ thông tin phiếu nhập');
    }

    $thamso = $conn->query('SELECT * FROM THAMSO LIMIT 1')->fetch();
    if (!$thamso) {
        json_error('Chưa có dữ liệu quy định nhập sách');
    }

    if ($soLuong < $thamso['SoLuongNhapItNhat']) {
        json_error("Số lượng nhập ({$soLuong}) phải lớn hơn hoặc bằng {$thamso['SoLuongNhapItNhat']} cuốn");
    }

    $sach = $conn->prepare('SELECT SoLuongTon, TenSach FROM SACH WHERE MaSach = ?');
    $sach->execute([$maSach]);
    $sachData = $sach->fetch();
    if (!$sachData) {
        json_error('Sách không tồn tại', 404);
    }

    if ($sachData['SoLuongTon'] >= $thamso['SoLuongTonToiDaTruocNhap']) {
        json_error("Chỉ nhập sách khi tồn kho thấp hơn {$thamso['SoLuongTonToiDaTruocNhap']} cuốn. Hiện tại: {$sachData['SoLuongTon']} cuốn");
    }

    $conn->beginTransaction();
    try {
        $pn = $conn->prepare('INSERT INTO PHIEUNHAP (NgayNhap) VALUES (NOW())');
        $pn->execute();
        $maPhieuNhap = $conn->lastInsertId();

        $ct = $conn->prepare('INSERT INTO CHITIETPHIEUNHAP (MaPhieuNhap, MaSach, SoLuongNhap, DonGiaNhap) VALUES (?,?,?,?)');
        $ct->execute([$maPhieuNhap, $maSach, $soLuong, $donGia]);

        $upSach = $conn->prepare('UPDATE SACH SET SoLuongTon = SoLuongTon + ?, DonGia = ? WHERE MaSach = ?');
        $upSach->execute([$soLuong, $donGia, $maSach]);

        $conn->commit();
        json_success([
            'MaPhieuNhap' => $maPhieuNhap,
            'TonMoi' => $sachData['SoLuongTon'] + $soLuong,
        ], 'Lập phiếu nhập thành công');
    } catch (Exception $e) {
        $conn->rollBack();
        json_error('Lỗi tạo phiếu nhập: ' . $e->getMessage(), 500);
    }
}
