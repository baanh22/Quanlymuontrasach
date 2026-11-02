<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/HeThongBaoCaoService.php';

/**
 * Controller: CauHinhController (api/cauhinh.php)
 * Thực thi QĐ4: Cho phép xem tham số (mọi nhân viên) và cập nhật tham số (chỉ Admin - RBAC)
 */
require_auth();

$method  = $_SERVER['REQUEST_METHOD'];
$service = new HeThongBaoCaoService($conn);

try {
    switch ($method) {
        case 'GET':
            json_success($service->getCauHinh());
            break;

        case 'PUT':
            require_admin();
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $service->updateCauHinh($body);
            json_success([], 'Cập nhật tham số quy định thành công!');
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
