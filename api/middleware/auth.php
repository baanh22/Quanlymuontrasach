<?php
function require_auth() {
    if (empty($_SESSION['user_id'])) {
        json_error('Phien dang nhap het han. Vui long dang nhap lai.', 401);
    }
}

function require_admin() {
    require_auth();
    if (($_SESSION['user_role'] ?? '') !== 'Admin') {
        json_error('Ban khong co quyen thuc hien chuc nang nay.', 403);
    }
}
