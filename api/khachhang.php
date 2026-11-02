<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/KhachHangService.php';

/**
 * Controller: KhachHangController (api/khachhang.php)
 * Layer: Presentation / Controller -> gọi KhachHangService
 */
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? '';
$khService = new KhachHangService($conn);

try {
    switch ($method) {
        case 'GET':
            $search = $_GET['search'] ?? '';
            json_success($khService->getAllKhachHang($search));
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $res  = $khService->createKhachHang($body);
            json_success($res, 'Thêm khách hàng thành công!');
            break;

        case 'PUT':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $khService->updateKhachHang((string)$id, $body);
            json_success([], 'Cập nhật khách hàng thành công!');
            break;

        case 'DELETE':
            $khService->deleteKhachHang((string)$id);
            json_success([], 'Xóa khách hàng thành công!');
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
