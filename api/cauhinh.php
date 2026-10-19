<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $settings = $conn->query('SELECT * FROM THAMSO LIMIT 1')->fetch();
        if (!$settings) json_error('Chua co cau hinh trong database.', 404);
        json_success($settings);
        break;

    case 'PUT':
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) json_error('Du lieu gui len khong hop le.');

        $minImport = filter_var($body['SoLuongNhapItNhat'] ?? null, FILTER_VALIDATE_INT);
        $maxStockBeforeImport = filter_var($body['SoLuongTonToiDaTruocNhap'] ?? null, FILTER_VALIDATE_INT);
        $maxDebt = filter_var($body['SoTienNoToiDa'] ?? null, FILTER_VALIDATE_FLOAT);
        $minStockAfterSale = filter_var($body['SoLuongTonSauToiThieu'] ?? null, FILTER_VALIDATE_INT);

        if ($minImport === false || $minImport < 1 ||
            $maxStockBeforeImport === false || $maxStockBeforeImport < 1 ||
            $maxDebt === false || $maxDebt <= 0 ||
            $minStockAfterSale === false || $minStockAfterSale < 0) {
            json_error('So luong toi thieu, ton truoc nhap va han muc no phai hop le; ton sau ban khong duoc am.');
        }

        $stmt = $conn->prepare('UPDATE THAMSO SET SoLuongNhapItNhat = ?, SoLuongTonToiDaTruocNhap = ?, SoTienNoToiDa = ?, SoLuongTonSauToiThieu = ?');
        $stmt->execute([$minImport, $maxStockBeforeImport, $maxDebt, $minStockAfterSale]);
        if ($stmt->rowCount() === 0 && (int)$conn->query('SELECT COUNT(*) FROM THAMSO')->fetchColumn() === 0) {
            $insert = $conn->prepare('INSERT INTO THAMSO (SoLuongNhapItNhat, SoLuongTonToiDaTruocNhap, SoTienNoToiDa, SoLuongTonSauToiThieu) VALUES (?, ?, ?, ?)');
            $insert->execute([$minImport, $maxStockBeforeImport, $maxDebt, $minStockAfterSale]);
        }
        json_success([], 'Cap nhat quy dinh thanh cong.');
        break;

    default:
        json_error('Phuong thuc khong hop le.', 405);
}
