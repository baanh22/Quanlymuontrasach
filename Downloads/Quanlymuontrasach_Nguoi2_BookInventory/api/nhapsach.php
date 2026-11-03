<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getNhapSach();
        break;
    case 'POST':
        addNhapSach();
        break;
    default:
        json_error('Phương thức không hợp lệ.', 405);
}

function getNhapSach() {
    global $conn;
    $sql = 'SELECT pn.MaPhieuNhap, pn.NgayNhap,
                   GROUP_CONCAT(CONCAT(s.TenSach, " (", ct.SoLuongNhap, ")") ORDER BY s.TenSach SEPARATOR ", ") AS DanhSachSach,
                   SUM(ct.SoLuongNhap) AS TongSoLuong,
                   SUM(ct.SoLuongNhap * ct.DonGiaNhap) AS TongTien
            FROM PHIEUNHAP pn
            INNER JOIN CHITIETPHIEUNHAP ct ON ct.MaPhieuNhap = pn.MaPhieuNhap
            INNER JOIN SACH s ON s.MaSach = ct.MaSach
            GROUP BY pn.MaPhieuNhap, pn.NgayNhap
            ORDER BY pn.NgayNhap DESC, pn.MaPhieuNhap DESC';
    json_success($conn->query($sql)->fetchAll());
}

function addNhapSach() {
    global $conn;
    $body = read_json_body();
    $maSach = trim($body['MaSach'] ?? '');
    $soLuong = filter_var($body['SoLuong'] ?? null, FILTER_VALIDATE_INT);
    $donGia = $body['DonGia'] ?? null;

    if ($maSach === '' || $soLuong === false || $soLuong === null || $soLuong <= 0 ||
        !is_numeric($donGia) || !is_finite((float)$donGia) || (float)$donGia <= 0) {
        json_error('Mã sách, số lượng và đơn giá nhập phải hợp lệ.');
    }

    try {
        $conn->beginTransaction();
        $rules = $conn->query(
            'SELECT SoLuongNhapItNhat, SoLuongTonToiDaTruocNhap FROM THAMSO WHERE MaThamSo = 1'
        )->fetch();
        if (!$rules) {
            $conn->rollBack();
            json_error('Chưa có cấu hình quy định nhập sách.', 500);
        }
        if ($soLuong < (int)$rules['SoLuongNhapItNhat']) {
            $conn->rollBack();
            json_error('Số lượng nhập tối thiểu là ' . (int)$rules['SoLuongNhapItNhat'] . ' cuốn.');
        }

        $stmt = $conn->prepare('SELECT TenSach, SoLuongTon FROM SACH WHERE MaSach = ? FOR UPDATE');
        $stmt->execute([$maSach]);
        $book = $stmt->fetch();
        if (!$book) {
            $conn->rollBack();
            json_error('Sách không tồn tại.', 404);
        }

        $stockBefore = (int)$book['SoLuongTon'];
        $stockLimit = (int)$rules['SoLuongTonToiDaTruocNhap'];
        if ($stockBefore >= $stockLimit) {
            $conn->rollBack();
            json_error("Chỉ được nhập khi tồn kho dưới $stockLimit cuốn. Tồn hiện tại: $stockBefore cuốn.");
        }

        $stmt = $conn->prepare('INSERT INTO PHIEUNHAP (NgayNhap) VALUES (CURRENT_TIMESTAMP)');
        $stmt->execute();
        $receiptId = (int)$conn->lastInsertId();

        $stmt = $conn->prepare(
            'INSERT INTO CHITIETPHIEUNHAP (MaPhieuNhap, MaSach, SoLuongNhap, DonGiaNhap) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$receiptId, $maSach, $soLuong, $donGia]);

        $stmt = $conn->prepare('UPDATE SACH SET SoLuongTon = SoLuongTon + ?, DonGia = ? WHERE MaSach = ?');
        $stmt->execute([$soLuong, $donGia, $maSach]);
        $conn->commit();

        $stockAfter = $stockBefore + $soLuong;
        json_success(
            ['MaPhieuNhap' => $receiptId, 'SoLuongTon' => $stockAfter],
            "Lập phiếu nhập thành công. Tồn kho mới: $stockAfter cuốn."
        );
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Unable to create stock receipt: ' . $e->getMessage());
        json_error('Không thể lập phiếu nhập. Vui lòng thử lại.', 500);
    }
}
