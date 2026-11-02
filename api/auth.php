<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/AuthService.php';

/**
 * Controller: AuthController (api/auth.php)
 * Đã khắc phục lỗi OWASP A02/A09: Xóa bỏ hoàn toàn việc ghi mật khẩu plaintext ra debug_login.txt
 * và tích hợp Bcrypt Password Hashing.
 */
$action = $_GET['action'] ?? '';
$authService = new AuthService($conn);

try {
    switch ($action) {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_error('Phương thức không hợp lệ. Dùng POST.', 405);
            }
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $userData = $authService->login(
                (string)($body['username'] ?? ''),
                (string)($body['password'] ?? '')
            );

            $_SESSION['user_id']   = $userData['username'];
            $_SESSION['user_name'] = $userData['fullName'];
            $_SESSION['user_role'] = $userData['role'];

            json_success($userData, 'Đăng nhập thành công!');
            break;

        case 'logout':
            session_destroy();
            json_success([], 'Đăng xuất thành công.');
            break;

        case 'me':
            if (!isset($_SESSION['user_id'])) {
                json_error('Chưa đăng nhập.', 401);
            }
            json_success([
                'username' => $_SESSION['user_id'],
                'fullName' => $_SESSION['user_name'],
                'role'     => $_SESSION['user_role'],
            ]);
            break;

        default:
            json_error('Action không hợp lệ', 404);
    }
} catch (BusinessException $be) {
    json_error($be->getMessage(), $be->getStatusCode());
} catch (Exception $e) {
    json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
}
?>
