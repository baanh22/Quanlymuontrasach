<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': getCauHinh();    break;
    case 'PUT': updateCauHinh(); break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getCauHinh() {
    global $conn;
    $thamso = $conn->query("SELECT * FROM THAMSO LIMIT 1")->fetch();
    json_success($thamso);
}

function updateCauHinh() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);

    $soLuongNhapItNhat       = (int)($body['SoLuongNhapItNhat']       ?? 0);
    $soLuongTonToiDaTruocNhap = (int)($body['SoLuongTonToiDaTruocNhap'] ?? 0);
    $soTienNoToiDa           = (float)($body['SoTienNoToiDa']          ?? 0);
    $soLuongTonSauToiThieu   = (int)($body['SoLuongTonSauToiThieu']   ?? 0);

    if ($soLuongNhapItNhat <= 0 || $soLuongTonToiDaTruocNhap <= 0 || $soTienNoToiDa <= 0 || $soLuongTonSauToiThieu < 0) {
        json_error('Các tham số phải là số hợp lệ và lớn hơn 0.');
    }

    $stmt = $conn->prepare("UPDATE THAMSO SET SoLuongNhapItNhat=?, SoLuongTonToiDaTruocNhap=?, SoTienNoToiDa=?, SoLuongTonSauToiThieu=?");
    $stmt->execute([$soLuongNhapItNhat, $soLuongTonToiDaTruocNhap, $soTienNoToiDa, $soLuongTonSauToiThieu]);

    json_success([], 'Cập nhật tham số quy định thành công!');
}
?>
