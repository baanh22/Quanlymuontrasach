<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/SachService.php';

/**
 * Controller: SachController (api/sach.php)
 * Layer: Presentation / Controller -> gọi SachService
 */
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? '';
$sachService = new SachService($conn);

try {
    switch ($method) {
        case 'GET':
            $search = $_GET['search'] ?? '';
            json_success($sachService->getAllSach($search));
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $res  = $sachService->createSach($body);
            json_success($res, 'Thêm sách mới thành công!');
            break;

        case 'PUT':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $sachService->updateSach((string)$id, $body);
            json_success([], 'Cập nhật sách thành công!');
            break;

        case 'DELETE':
            $sachService->deleteSach((string)$id);
            json_success([], 'Xóa sách thành công!');
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
