<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Phương thức không hợp lệ.', 405);
}

$rules = $conn->query('SELECT SoLuongNhapItNhat, SoLuongTonToiDaTruocNhap FROM THAMSO WHERE MaThamSo = 1')->fetch();
if (!$rules) {
    json_error('Chưa có cấu hình quy định nhập sách.', 404);
}
json_success($rules);
