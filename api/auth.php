<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'me':
        handleMe();
        break;
    default:
        json_error('Action không hợp lệ', 404);
}

function handleLogin() {
    global $conn;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Phương thức không hợp lệ. Dùng POST.', 405);
    }

    $raw = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/debug_login.txt', date('Y-m-d H:i:s') . " - RAW: " . $raw . "\n", FILE_APPEND);

    $body = json_decode($raw, true);
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');

    if (!$username || !$password) {
        json_error('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
    }

    $stmt = $conn->prepare("SELECT * FROM TAIKHOAN WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // So sánh MD5 (đang dùng md5 theo database.sql)
    if (!$user || $user['Password'] !== md5($password)) {
        json_error('Tên đăng nhập hoặc mật khẩu không đúng.', 401);
    }

    $_SESSION['user_id']   = $user['Username'];
    $_SESSION['user_name'] = $user['FullName'];
    $_SESSION['user_role'] = $user['Role'];

    json_success([
        'username' => $user['Username'],
        'fullName' => $user['FullName'],
        'role'     => $user['Role'],
    ], 'Đăng nhập thành công!');
}

function handleLogout() {
    session_destroy();
    json_success([], 'Đăng xuất thành công.');
}

function handleMe() {
    if (!isset($_SESSION['user_id'])) {
        json_error('Chưa đăng nhập.', 401);
    }
    json_success([
        'username' => $_SESSION['user_id'],
        'fullName' => $_SESSION['user_name'],
        'role'     => $_SESSION['user_role'],
    ]);
}
?>
