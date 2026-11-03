<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getSach();
        break;
    case 'POST':
        addSach();
        break;
    case 'PUT':
        updateSach();
        break;
    case 'DELETE':
        deleteSach();
        break;
    default:
        json_error('Phương thức không hợp lệ.', 405);
}

function getSach() {
    global $conn;
    $search = trim($_GET['search'] ?? '');
    $sql = 'SELECT MaSach, TenSach, TheLoai, TacGia, SoLuongTon, DonGia FROM SACH';
    if ($search !== '') {
        $sql .= ' WHERE MaSach LIKE :code OR TenSach LIKE :title OR TacGia LIKE :author OR TheLoai LIKE :category';
    }
    $sql .= ' ORDER BY TenSach ASC';

    $stmt = $conn->prepare($sql);
    $pattern = "%$search%";
    $stmt->execute($search !== '' ? [
        'code' => $pattern,
        'title' => $pattern,
        'author' => $pattern,
        'category' => $pattern,
    ] : []);
    json_success($stmt->fetchAll());
}

function addSach() {
    global $conn;
    $body = read_json_body();
    $maSach = trim($body['MaSach'] ?? '');
    $tenSach = trim($body['TenSach'] ?? '');
    $theLoai = trim($body['TheLoai'] ?? '');
    $tacGia = trim($body['TacGia'] ?? '');
    $soLuongTon = filter_var($body['SoLuongTon'] ?? 0, FILTER_VALIDATE_INT);
    $donGia = $body['DonGia'] ?? 0;

    if ($maSach === '' || $tenSach === '') {
        json_error('Mã sách và tên sách không được để trống.');
    }
    if ($soLuongTon === false || $soLuongTon < 0 || !is_numeric($donGia) || !is_finite((float)$donGia) || (float)$donGia < 0) {
        json_error('Số lượng tồn và đơn giá phải là số không âm hợp lệ.');
    }

    $stmt = $conn->prepare(
        'INSERT INTO SACH (MaSach, TenSach, TheLoai, TacGia, SoLuongTon, DonGia) VALUES (?, ?, ?, ?, ?, ?)'
    );
    try {
        $stmt->execute([$maSach, $tenSach, $theLoai, $tacGia, $soLuongTon, $donGia]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            json_error('Mã sách đã tồn tại.', 409);
        }
        error_log('Unable to add book: ' . $e->getMessage());
        json_error('Không thể thêm sách vào hệ thống.', 500);
    }

    json_success(['MaSach' => $maSach], 'Thêm sách thành công.');
}

function updateSach() {
    global $conn;
    $id = trim($_GET['id'] ?? '');
    if ($id === '') {
        json_error('Thiếu mã sách (id).');
    }

    $body = read_json_body();
    $tenSach = trim($body['TenSach'] ?? '');
    $theLoai = trim($body['TheLoai'] ?? '');
    $tacGia = trim($body['TacGia'] ?? '');
    $donGia = $body['DonGia'] ?? null;
    if ($tenSach === '' || !is_numeric($donGia) || !is_finite((float)$donGia) || (float)$donGia < 0) {
        json_error('Tên sách và đơn giá hợp lệ là bắt buộc.');
    }

    $exists = $conn->prepare('SELECT 1 FROM SACH WHERE MaSach = ?');
    $exists->execute([$id]);
    if (!$exists->fetchColumn()) {
        json_error('Không tìm thấy sách cần cập nhật.', 404);
    }

    $stmt = $conn->prepare('UPDATE SACH SET TenSach = ?, TheLoai = ?, TacGia = ?, DonGia = ? WHERE MaSach = ?');
    $stmt->execute([$tenSach, $theLoai, $tacGia, $donGia, $id]);
    json_success([], 'Cập nhật sách thành công.');
}

function deleteSach() {
    global $conn;
    $id = trim($_GET['id'] ?? '');
    if ($id === '') {
        json_error('Thiếu mã sách (id).');
    }

    $stmt = $conn->prepare('SELECT 1 FROM SACH WHERE MaSach = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetchColumn()) {
        json_error('Không tìm thấy sách cần xóa.', 404);
    }

    $stmt = $conn->prepare('SELECT COUNT(*) FROM CHITIETPHIEUNHAP WHERE MaSach = ?');
    $stmt->execute([$id]);
    if ((int)$stmt->fetchColumn() > 0) {
        json_error('Không thể xóa sách đã có lịch sử nhập kho.');
    }

    $stmt = $conn->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute(['CHITIETPHIEUHOADON']);
    if ((int)$stmt->fetchColumn() > 0) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM CHITIETPHIEUHOADON WHERE MaSach = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            json_error('Không thể xóa sách đã có hóa đơn liên quan.');
        }
    }

    $stmt = $conn->prepare('DELETE FROM SACH WHERE MaSach = ?');
    try {
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Unable to delete book: ' . $e->getMessage());
        json_error('Không thể xóa sách đang được giao dịch khác sử dụng.', 409);
    }
    json_success([], 'Xóa sách thành công.');
}
