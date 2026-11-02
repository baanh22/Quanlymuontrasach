<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/services/HeThongBaoCaoService.php';

/**
 * Controller: BaoCaoController (api/baocao.php)
 * Layer: Presentation / Controller -> gọi HeThongBaoCaoService
 */
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Phương thức không hợp lệ', 405);
}

$type  = $_GET['type'] ?? '';
$thang = (int)($_GET['thang'] ?? date('m'));
$nam   = (int)($_GET['nam'] ?? date('Y'));

$service = new HeThongBaoCaoService($conn);

try {
    if ($type === 'tonkho') {
        json_success($service->getBaoCaoTon($thang, $nam));
    } elseif ($type === 'congno') {
        json_success($service->getBaoCaoCongNo($thang, $nam));
    } else {
        json_error('Loại báo cáo không hợp lệ.', 400);
    }
} catch (BusinessException $be) {
    json_error($be->getMessage(), $be->getStatusCode());
} catch (Exception $e) {
    json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
}
?>
