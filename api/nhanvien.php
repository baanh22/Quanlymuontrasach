<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_admin();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $stmt = $conn->query('SELECT Username, FullName, Role FROM TAIKHOAN ORDER BY Role ASC, Username ASC');
        json_success($stmt->fetchAll());
        break;

    case 'POST':
        $body = read_json_body();
        $username = trim($body['Username'] ?? '');
        $password = $body['Password'] ?? '';
        $fullName = trim($body['FullName'] ?? '');
        $role = valid_role($body['Role'] ?? 'User');
        if (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/', $username)) json_error('Username can tu 3-40 ky tu, chi gom chu, so, cham, gach ngang hoac gach duoi.');
        if (!is_string($password) || strlen($password) < 8) json_error('Mat khau phai co it nhat 8 ky tu.');
        if ($fullName === '') json_error('Ho ten khong duoc de trong.');
        if (!$role) json_error('Quyen tai khoan khong hop le.');

        $stmt = $conn->prepare('INSERT INTO TAIKHOAN (Username, Password, FullName, Role) VALUES (?, ?, ?, ?)');
        try {
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullName, $role]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') json_error('Username da ton tai.');
            json_error('Khong the tao tai khoan.', 500);
        }
        json_success([], 'Tao tai khoan nhan vien thanh cong.');
        break;

    case 'PUT':
        $username = trim($_GET['id'] ?? '');
        $body = read_json_body();
        $fullName = trim($body['FullName'] ?? '');
        $role = valid_role($body['Role'] ?? 'User');
        $password = $body['Password'] ?? '';
        if ($username === '') json_error('Thieu username.');
        if ($fullName === '') json_error('Ho ten khong duoc de trong.');
        if (!$role) json_error('Quyen tai khoan khong hop le.');
        if (!is_string($password)) json_error('Mat khau khong hop le.');
        if ($password !== '' && strlen($password) < 8) json_error('Mat khau moi phai co it nhat 8 ky tu.');
        if ($username === $_SESSION['user_id'] && $role !== 'Admin') json_error('Ban khong the tu ha quyen Admin cua minh.');

        $exists = $conn->prepare('SELECT 1 FROM TAIKHOAN WHERE Username = ?');
        $exists->execute([$username]);
        if (!$exists->fetchColumn()) json_error('Khong tim thay tai khoan.', 404);
        if ($role !== 'Admin') protect_last_admin($conn, $username);

        if ($password !== '') {
            $stmt = $conn->prepare('UPDATE TAIKHOAN SET FullName = ?, Role = ?, Password = ? WHERE Username = ?');
            $stmt->execute([$fullName, $role, password_hash($password, PASSWORD_DEFAULT), $username]);
        } else {
            $stmt = $conn->prepare('UPDATE TAIKHOAN SET FullName = ?, Role = ? WHERE Username = ?');
            $stmt->execute([$fullName, $role, $username]);
        }
        if ($username === $_SESSION['user_id']) $_SESSION['user_name'] = $fullName;
        json_success([], 'Cap nhat nhan vien thanh cong.');
        break;

    case 'DELETE':
        $username = trim($_GET['id'] ?? '');
        if ($username === '') json_error('Thieu username.');
        if ($username === $_SESSION['user_id']) json_error('Ban khong the xoa tai khoan dang dang nhap.');
        $exists = $conn->prepare('SELECT Role FROM TAIKHOAN WHERE Username = ?');
        $exists->execute([$username]);
        $target = $exists->fetch();
        if (!$target) json_error('Khong tim thay tai khoan.', 404);
        if ($target['Role'] === 'Admin') protect_last_admin($conn, $username);
        $stmt = $conn->prepare('DELETE FROM TAIKHOAN WHERE Username = ?');
        $stmt->execute([$username]);
        json_success([], 'Da xoa tai khoan ' . $username . '.');
        break;

    default:
        json_error('Phuong thuc khong hop le.', 405);
}

function valid_role($role) {
    return in_array($role, ['User', 'Admin'], true) ? $role : null;
}

function protect_last_admin($conn, $username) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM TAIKHOAN WHERE Role = 'Admin' AND Username <> ?");
    $stmt->execute([$username]);
    if ((int)$stmt->fetchColumn() === 0) json_error('He thong phai co it nhat mot Admin.');
}
