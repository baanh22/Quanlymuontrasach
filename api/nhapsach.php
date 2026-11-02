<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/NhapSachService.php';

/**
 * Controller: NhapSachController (api/nhapsach.php)
 * Layer: Presentation / Controller -> gọi NhapSachService (Thực thi QĐ1)
 */
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$nhapSachService = new NhapSachService($conn);

try {
    switch ($method) {
        case 'GET':
            json_success($nhapSachService->getHistory());
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $res  = $nhapSachService->createPhieuNhap($body);
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
