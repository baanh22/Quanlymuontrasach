<?php
require_once __DIR__ . '/api/config/database.php';

$username = 'admin';
$password = 'admin';

$stmt = $conn->prepare("SELECT * FROM TAIKHOAN WHERE Username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found\n";
} else {
    echo "User found: " . print_r($user, true) . "\n";
    echo "DB Password: " . $user['Password'] . "\n";
    echo "Input MD5: " . md5($password) . "\n";
    if ($user['Password'] !== md5($password)) {
        echo "Password mismatch!\n";
    } else {
        echo "Password Match!\n";
    }
}
