<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') json_error('Hay dung PUT de cap nhat tai khoan.', 405);
$body = read_json_body();
$fullName = trim($body['FullName'] ?? '');
$currentPassword = $body['CurrentPassword'] ?? '';
$newPassword = $body['NewPassword'] ?? '';
if ($fullName === '') json_error('Ho ten khong duoc de trong.');
if (!is_string($currentPassword) || !is_string($newPassword)) json_error('Mat khau khong hop le.');

$stmt = $conn->prepare('SELECT Password FROM TAIKHOAN WHERE Username = ?');
$stmt->execute([$_SESSION['user_id']]);
$storedPassword = $stmt->fetchColumn();
if ($storedPassword === false) json_error('Tai khoan khong con ton tai.', 404);

if ($newPassword !== '') {
    if ($currentPassword === '') json_error('Nhap mat khau hien tai de doi mat khau.');
    $valid = password_verify($currentPassword, $storedPassword);
    if (!$valid && preg_match('/^[a-f0-9]{32}$/i', $storedPassword) === 1) {
        $valid = hash_equals(strtolower($storedPassword), md5($currentPassword));
    }
    if (!$valid) json_error('Mat khau hien tai khong dung.', 401);
    if (strlen($newPassword) < 8) json_error('Mat khau moi phai co it nhat 8 ky tu.');

    $update = $conn->prepare('UPDATE TAIKHOAN SET FullName = ?, Password = ? WHERE Username = ?');
    $update->execute([$fullName, password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]);
} else {
    $update = $conn->prepare('UPDATE TAIKHOAN SET FullName = ? WHERE Username = ?');
    $update->execute([$fullName, $_SESSION['user_id']]);
}

$_SESSION['user_name'] = $fullName;
json_success(['fullName' => $fullName], 'Cap nhat tai khoan thanh cong.');
