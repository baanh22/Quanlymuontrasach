<?php
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function require_admin() {
    require_auth();
    if ($_SESSION['user_role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền thực hiện thao tác này.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
