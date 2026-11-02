<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/PhieuThuService.php';

/**
 * Controller: PhieuThuController (api/phieuthu.php)
 * Layer: Presentation / Controller -> gọi PhieuThuService (Thực thi QĐ3)
 */
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$phieuThuService = new PhieuThuService($conn);

try {
    switch ($method) {
        case 'GET':
            json_success($phieuThuService->getHistory());
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $res  = $phieuThuService->createPhieuThu($body);
            json_success($res['data'], $res['message']);
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
