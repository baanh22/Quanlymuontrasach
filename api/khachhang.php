<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':    getKhachHang();    break;
    case 'POST':   addKhachHang();    break;
    case 'PUT':    updateKhachHang(); break;
    case 'DELETE': deleteKhachHang(); break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getKhachHang() {
    global $conn;
    $search = $_GET['search'] ?? '';
    $sql = "SELECT * FROM KHACHHANG";
    if ($search) {
        $sql .= " WHERE HoTenKhachHang LIKE :search OR DienThoai LIKE :search OR MaKhachHang LIKE :search";
    }
    $sql .= " ORDER BY HoTenKhachHang ASC";
    $stmt = $conn->prepare($sql);
    if ($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    json_success($stmt->fetchAll());
}

function addKhachHang() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);

    $maKH    = trim($body['MaKhachHang']    ?? '');
    $hoTen   = trim($body['HoTenKhachHang'] ?? '');
    $diaChi  = trim($body['DiaChi']         ?? '');
    $dienThoai = trim($body['DienThoai']    ?? '');
    $email   = trim($body['Email']          ?? '');

    if (!$maKH || !$hoTen) {
        json_error('Mã khách hàng và Họ tên không được để trống.');
    }

    $check = $conn->prepare("SELECT MaKhachHang FROM KHACHHANG WHERE MaKhachHang = ?");
    $check->execute([$maKH]);
    if ($check->fetch()) {
        json_error("Mã khách hàng '$maKH' đã tồn tại.");
    }

    $stmt = $conn->prepare("INSERT INTO KHACHHANG (MaKhachHang, HoTenKhachHang, DiaChi, DienThoai, Email, SoTienNo) VALUES (?,?,?,?,?,0)");
    $stmt->execute([$maKH, $hoTen, $diaChi, $dienThoai, $email]);

    json_success(['MaKhachHang' => $maKH], 'Thêm khách hàng thành công!');
}

function updateKhachHang() {
    global $conn;
    $id = $_GET['id'] ?? null;
    if (!$id) json_error('Thiếu mã khách hàng (id).');

    $body      = json_decode(file_get_contents('php://input'), true);
    $hoTen     = trim($body['HoTenKhachHang'] ?? '');
    $diaChi    = trim($body['DiaChi']         ?? '');
    $dienThoai = trim($body['DienThoai']      ?? '');
    $email     = trim($body['Email']          ?? '');

    if (!$hoTen) json_error('Họ tên không được để trống.');

    $stmt = $conn->prepare("UPDATE KHACHHANG SET HoTenKhachHang=?, DiaChi=?, DienThoai=?, Email=? WHERE MaKhachHang=?");
    $stmt->execute([$hoTen, $diaChi, $dienThoai, $email, $id]);

    if ($stmt->rowCount() === 0) {
        json_error("Không tìm thấy khách hàng '$id'.", 404);
    }
    json_success([], 'Cập nhật khách hàng thành công!');
}

function deleteKhachHang() {
    global $conn;
    $id = $_GET['id'] ?? null;
    if (!$id) json_error('Thiếu mã khách hàng (id).');

    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM PHIEUHOADON WHERE MaKhachHang = ?");
    $check->execute([$id]);
    if ($check->fetch()['cnt'] > 0) {
        json_error('Không thể xóa khách hàng đã có hóa đơn liên quan.');
    }

    $stmt = $conn->prepare("DELETE FROM KHACHHANG WHERE MaKhachHang = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        json_error("Không tìm thấy khách hàng '$id'.", 404);
    }
    json_success([], 'Xóa khách hàng thành công!');
}
?>
