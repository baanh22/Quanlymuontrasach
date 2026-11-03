<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'QuanLyNhaSach_Nguoi2';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Book inventory database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = [], $message = 'Thành công') {
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

function json_error($message = 'Có lỗi xảy ra', $status = 400) {
    json_response(['success' => false, 'message' => $message], $status);
}

function read_json_body() {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        json_error('Dữ liệu gửi lên không hợp lệ.');
    }
    return $body;
}
