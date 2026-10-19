<?php
header('Content-Type: application/json; charset=UTF-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'QuanLyNhaSach_Nguoi1';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Khong the ket noi database.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = [], $message = 'Thanh cong') {
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

function json_error($message = 'Co loi xay ra', $status = 400) {
    json_response(['success' => false, 'message' => $message], $status);
}

function read_json_body() {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) json_error('Du lieu gui len khong hop le.');
    return $body;
}
