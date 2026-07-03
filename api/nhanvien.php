<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':  getNhanVien();  break;
    case 'POST': addNhanVien();  break;
    case 'PUT':  updateNhanVien(); break;
    case 'DELETE': deleteNhanVien(); break;
    default: json_error('Phương thức không hợp lệ', 405);
}

function getNhanVien() {
    global $conn;
    // Không trả về Password ra ngoài Front-end
    $sql = "SELECT Username, FullName, Role FROM TAIKHOAN ORDER BY Role ASC, Username ASC";
    $stmt = $conn->query($sql);
    json_success($stmt->fetchAll());
}

function addNhanVien() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);
    
    $username = trim($body['Username'] ?? '');
    $password = trim($body['Password'] ?? '');
    $fullName = trim($body['FullName'] ?? '');
    $role     = trim($body['Role'] ?? 'User');

    if (!$username || !$password || !$fullName) {
        json_error('Vui lòng nhập đầy đủ Username, Password và Họ Tên.');
    }

    // Check account exists
    $check = $conn->prepare("SELECT Username FROM TAIKHOAN WHERE Username = ?");
    $check->execute([$username]);
    if ($check->rowCount() > 0) {
        json_error('Tên đăng nhập (Username) này đã tồn tại trong hệ thống.');
    }

    try {
        $stmt = $conn->prepare("INSERT INTO TAIKHOAN (Username, Password, FullName, Role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, md5($password), $fullName, $role]);
        json_success([], 'Tạo tài khoản nhân viên thành công!');
    } catch (Exception $e) {
        json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
    }
}

function updateNhanVien() {
    global $conn;
    $body = json_decode(file_get_contents('php://input'), true);
    $username = trim($_GET['id'] ?? '');
    
    if (!$username) json_error('Thiếu mã Username.');

    $fullName = trim($body['FullName'] ?? '');
    $role     = trim($body['Role'] ?? 'User');
    $password = trim($body['Password'] ?? '');

    if (!$fullName) {
        json_error('Họ tên không được để trống.');
    }

    try {
        if ($password !== '') {
            // Sửa cả password
            $stmt = $conn->prepare("UPDATE TAIKHOAN SET FullName = ?, Role = ?, Password = ? WHERE Username = ?");
            $stmt->execute([$fullName, $role, md5($password), $username]);
        } else {
            // Chỉ sửa thông tin
            $stmt = $conn->prepare("UPDATE TAIKHOAN SET FullName = ?, Role = ? WHERE Username = ?");
            $stmt->execute([$fullName, $role, $username]);
        }
        
        // Cập nhật lại session nếu người đó tự sửa chính mình
        if ($_SESSION['user_id'] === $username) {
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_role'] = $role;
        }

        json_success([], 'Cập nhật thông tin nhân viên thành công!');
    } catch (Exception $e) {
        json_error('Lỗi hệ thống: ' . $e->getMessage(), 500);
    }
}

function deleteNhanVien() {
    global $conn;
    $username = trim($_GET['id'] ?? '');
    
    if (!$username) json_error('Thiếu mã Username.');
    
    if ($username === $_SESSION['user_id']) {
        json_error('Bạn không thể tự xóa tài khoản của chính mình.');
    }

    try {
        $stmt = $conn->prepare("DELETE FROM TAIKHOAN WHERE Username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            json_success([], "Xóa tài khoản $username thành công.");
        } else {
            json_error('Không tìm thấy tài khoản để xóa.', 404);
        }
    } catch (Exception $e) {
        json_error('Lỗi hệ thống: Tài khoản này có thể đang liên kết với các dữ liệu khác.', 500);
    }
}
?>
