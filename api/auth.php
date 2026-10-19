<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Hay dung POST de dang nhap.', 405);
        handle_login();
        break;
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Hay dung POST de dang xuat.', 405);
        handle_logout();
        break;
    case 'me':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Hay dung GET de lay tai khoan.', 405);
        handle_me();
        break;
    default:
        json_error('Action khong hop le.', 404);
}

function handle_login() {
    global $conn;
    $body = read_json_body();
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';
    if ($username === '' || !is_string($password) || $password === '') {
        json_error('Hay nhap ten dang nhap va mat khau.');
    }

    $stmt = $conn->prepare('SELECT Username, Password, FullName, Role FROM TAIKHOAN WHERE Username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user) json_error('Ten dang nhap hoac mat khau khong dung.', 401);

    $storedPassword = (string)$user['Password'];
    $valid = password_verify($password, $storedPassword);
    $isLegacyMd5 = preg_match('/^[a-f0-9]{32}$/i', $storedPassword) === 1;
    if (!$valid && $isLegacyMd5) {
        $valid = hash_equals(strtolower($storedPassword), md5($password));
        if ($valid) {
            $upgrade = $conn->prepare('UPDATE TAIKHOAN SET Password = ? WHERE Username = ?');
            $upgrade->execute([password_hash($password, PASSWORD_DEFAULT), $username]);
        }
    }
    if (!$valid) json_error('Ten dang nhap hoac mat khau khong dung.', 401);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['Username'];
    $_SESSION['user_name'] = $user['FullName'];
    $_SESSION['user_role'] = $user['Role'];
    json_success(public_user($user), 'Dang nhap thanh cong.');
}

function handle_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
    json_success([], 'Dang xuat thanh cong.');
}

function handle_me() {
    require_auth();
    json_success([
        'username' => $_SESSION['user_id'],
        'fullName' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ]);
}

function public_user($user) {
    return ['username' => $user['Username'], 'fullName' => $user['FullName'], 'role' => $user['Role']];
}
