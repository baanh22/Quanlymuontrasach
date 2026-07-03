<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':    getSach();    break;
    case 'POST':   addSach();    break;
    case 'PUT':    updateSach(); break;
    case 'DELETE': deleteSach(); break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getSach() {
    global $conn;
    $search = $_GET['search'] ?? '';
    $sql = "SELECT * FROM SACH";
    if ($search) {
        $sql .= " WHERE TenSach LIKE :search OR TacGia LIKE :search OR TheLoai LIKE :search";
    }
    $sql .= " ORDER BY TenSach ASC";
    $stmt = $conn->prepare($sql);
    if ($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    json_success($stmt->fetchAll());
}

function addSach() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);

    $maSach     = trim($body['MaSach']     ?? '');
    $tenSach    = trim($body['TenSach']    ?? '');
    $theLoai    = trim($body['TheLoai']    ?? '');
    $tacGia     = trim($body['TacGia']     ?? '');
    $soLuongTon = (int)($body['SoLuongTon'] ?? 0);
    $donGia     = (float)($body['DonGia']  ?? 0);

    if (!$maSach || !$tenSach) {
        json_error('Mã sách và Tên sách không được để trống.');
    }

    // Kiểm tra trùng MaSach
    $check = $conn->prepare("SELECT MaSach FROM SACH WHERE MaSach = ?");
    $check->execute([$maSach]);
    if ($check->fetch()) {
        json_error("Mã sách '$maSach' đã tồn tại trong hệ thống.");
    }

    $stmt = $conn->prepare("INSERT INTO SACH (MaSach, TenSach, TheLoai, TacGia, SoLuongTon, DonGia) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$maSach, $tenSach, $theLoai, $tacGia, $soLuongTon, $donGia]);

    json_success(['MaSach' => $maSach], 'Thêm sách mới thành công!');
}

function updateSach() {
    global $conn;
    $id   = $_GET['id'] ?? null;
    if (!$id) json_error('Thiếu mã sách (id).');

    $body       = json_decode(file_get_contents('php://input'), true);
    $tenSach    = trim($body['TenSach']    ?? '');
    $theLoai    = trim($body['TheLoai']    ?? '');
    $tacGia     = trim($body['TacGia']     ?? '');
    $soLuongTon = (int)($body['SoLuongTon'] ?? 0);
    $donGia     = (float)($body['DonGia']  ?? 0);

    if (!$tenSach) json_error('Tên sách không được để trống.');

    $stmt = $conn->prepare("UPDATE SACH SET TenSach=?, TheLoai=?, TacGia=?, SoLuongTon=?, DonGia=? WHERE MaSach=?");
    $stmt->execute([$tenSach, $theLoai, $tacGia, $soLuongTon, $donGia, $id]);

    if ($stmt->rowCount() === 0) {
        json_error("Không tìm thấy sách có mã '$id'.", 404);
    }
    json_success([], 'Cập nhật sách thành công!');
}

function deleteSach() {
    global $conn;
    $id = $_GET['id'] ?? null;
    if (!$id) json_error('Thiếu mã sách (id).');

    // Kiểm tra có hóa đơn liên quan không
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM CHITIETPHIEUHOADON WHERE MaSach = ?");
    $check->execute([$id]);
    if ($check->fetch()['cnt'] > 0) {
        json_error('Không thể xóa sách đã có hóa đơn liên quan.');
    }

    $stmt = $conn->prepare("DELETE FROM SACH WHERE MaSach = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        json_error("Không tìm thấy sách có mã '$id'.", 404);
    }
    json_success([], 'Xóa sách thành công!');
}
?>
