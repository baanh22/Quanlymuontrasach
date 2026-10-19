<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'QuanLyNhaSach';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối database: ' . $e->getMessage()
    ]);
    exit;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = [], $message = 'Thành công') {
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

function json_error($message = 'Có lỗi xảy ra', $code = 400) {
    json_response(['success' => false, 'message' => $message], $code);
}
