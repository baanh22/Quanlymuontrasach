<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/HeThongBaoCaoService.php';

/**
 * Controller: ThongKeController (api/thongke.php)
 * Layer: Presentation / Controller -> gọi HeThongBaoCaoService
 */
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Phương thức không hợp lệ', 405);
}

$service = new HeThongBaoCaoService($conn);

try {
    json_success($service->getDashboardStats());
} catch (Exception $e) {
    json_error('Lỗi tải thống kê: ' . $e->getMessage(), 500);
}
?>
