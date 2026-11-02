<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/HeThongBaoCaoService.php';

/**
 * Controller: NhanVienController (api/nhanvien.php)
 * Đã khắc phục lỗi OWASP A01 (Broken Access Control):
 * Bắt buộc quyền Admin (`require_admin()`) đối với các thao tác Thêm/Sửa/Xóa tài khoản nhân viên.
 */
require_auth();

$method  = $_SERVER['REQUEST_METHOD'];
$service = new HeThongBaoCaoService($conn);

try {
    switch ($method) {
        case 'GET':
            json_success($service->getAllNhanVien());
            break;

        case 'POST':
            require_admin();
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $service->createNhanVien($body);
            json_success([], 'Tạo tài khoản nhân viên thành công!');
            break;

        case 'PUT':
            require_admin();
            $username = (string)($_GET['id'] ?? '');
            $body     = json_decode(file_get_contents('php://input'), true) ?? [];
            $updated  = $service->updateNhanVien($username, $body);

            if (($_SESSION['user_id'] ?? '') === $username) {
                $_SESSION['user_name'] = $updated['FullName'];
                $_SESSION['user_role'] = $updated['Role'];
            }
            json_success([], 'Cập nhật thông tin nhân viên thành công!');
            break;

        case 'DELETE':
            require_admin();
            $username = (string)($_GET['id'] ?? '');
            $service->deleteNhanVien($username, (string)($_SESSION['user_id'] ?? ''));
            json_success([], "Xóa tài khoản $username thành công.");
            break;

        default:
            json_error('Phương thức không hợp lệ', 405);
    }
} catch (BusinessException $be) {
    json_error($be->getMessage(), $be->getStatusCode());
} catch (Exception $e) {
    json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
}
?>
