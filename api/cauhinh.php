<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getCauHinh();
        break;
    case 'PUT':
        updateCauHinh();
        break;
    default:
        json_error('Phương thức không hợp lệ', 405);
}

function getCauHinh() {
    global $conn;
    $row = $conn->query('SELECT * FROM THAMSO LIMIT 1')->fetch();
    if (!$row) {
        json_success([
            'SoLuongNhapItNhat' => 1,
            'SoLuongTonToiDaTruocNhap' => 100,
        ]);
        return;
    }
    json_success($row);
}

function updateCauHinh() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);
    $minNhap = (int)($body['SoLuongNhapItNhat'] ?? 1);
    $maxTon = (int)($body['SoLuongTonToiDaTruocNhap'] ?? 100);

    $stmt = $conn->prepare('UPDATE THAMSO SET SoLuongNhapItNhat = ?, SoLuongTonToiDaTruocNhap = ?');
    $stmt->execute([$minNhap, $maxTon]);

    if ($stmt->rowCount() === 0) {
        $insert = $conn->prepare('INSERT INTO THAMSO (SoLuongNhapItNhat, SoLuongTonToiDaTruocNhap) VALUES (?, ?)');
        $insert->execute([$minNhap, $maxTon]);
    }

    json_success([], 'Cập nhật quy định thành công');
}
