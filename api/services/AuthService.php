<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/TaiKhoanRepository.php';

/**
 * Service: AuthService
 * Xử lý đăng nhập an toàn (hỗ trợ cả Bcrypt và tự động nâng cấp từ MD5 cũ lên Bcrypt)
 */
class AuthService {
    private TaiKhoanRepository $taiKhoanRepo;

    public function __construct(PDO $conn) {
        $this->taiKhoanRepo = new TaiKhoanRepository($conn);
    }

    /**
     * Kiểm tra mật khẩu hỗ trợ Bcrypt + fallback MD5 tương thích ngược
     */
    public static function verifyPassword(string $plainPassword, string $storedHash): bool {
        if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$')) {
            return password_verify($plainPassword, $storedHash);
        }
        return hash_equals($storedHash, md5($plainPassword));
    }

    public function login(string $username, string $password): array {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            throw new BusinessException('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.', 400);
        }

        $user = $this->taiKhoanRepo->findByUsername($username);
        if (!$user || !self::verifyPassword($password, $user['Password'])) {
            throw new BusinessException('Tên đăng nhập hoặc mật khẩu không đúng.', 401);
        }

        // Tự động nâng cấp (Transparent Migration) từ MD5 lên Bcrypt nếu tài khoản vẫn đang lưu MD5
        if (!str_starts_with($user['Password'], '$2y$')) {
            $newBcryptHash = password_hash($password, PASSWORD_BCRYPT);
            $this->taiKhoanRepo->upgradePasswordHash($username, $newBcryptHash);
        }

        return [
            'username' => $user['Username'],
            'fullName' => $user['FullName'],
            'role'     => $user['Role'],
        ];
    }
}
?>

