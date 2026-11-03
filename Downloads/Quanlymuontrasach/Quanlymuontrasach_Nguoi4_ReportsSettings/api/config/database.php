<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'QuanLyNhaSach_Nguoi4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Loi ket noi database.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = [], $message = 'Thanh cong') {
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

function json_error($message = 'Co loi xay ra', $code = 400) {
    json_response(['success' => false, 'message' => $message], $code);
}
