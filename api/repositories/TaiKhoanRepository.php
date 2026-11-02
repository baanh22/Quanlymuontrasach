<?php
require_once __DIR__ . '/../models/TaiKhoan.php';

/**
 * Repository: TaiKhoanRepository
 * Layer: Data Access Layer (Quản lý bảng TAIKHOAN)
 */
class TaiKhoanRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findAllSafe(): array {
        $sql = "SELECT Username, FullName, Role FROM TAIKHOAN ORDER BY Role ASC, Username ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM TAIKHOAN WHERE Username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(TaiKhoan $tk): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO TAIKHOAN (Username, Password, FullName, Role) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$tk->Username, $tk->Password, $tk->FullName, $tk->Role]);
    }

    public function updateInfo(string $username, string $fullName, string $role): int {
        $stmt = $this->conn->prepare("UPDATE TAIKHOAN SET FullName = ?, Role = ? WHERE Username = ?");
        $stmt->execute([$fullName, $role, $username]);
        return $stmt->rowCount();
    }

    public function updateWithPassword(string $username, string $fullName, string $role, string $hashedPassword): int {
        $stmt = $this->conn->prepare(
            "UPDATE TAIKHOAN SET FullName = ?, Role = ?, Password = ? WHERE Username = ?"
        );
        $stmt->execute([$fullName, $role, $hashedPassword, $username]);
        return $stmt->rowCount();
    }

    public function upgradePasswordHash(string $username, string $bcryptHash): bool {
        $stmt = $this->conn->prepare("UPDATE TAIKHOAN SET Password = ? WHERE Username = ?");
        return $stmt->execute([$bcryptHash, $username]);
    }

    public function delete(string $username): int {
        $stmt = $this->conn->prepare("DELETE FROM TAIKHOAN WHERE Username = ?");
        $stmt->execute([$username]);
        return $stmt->rowCount();
    }
}
?>

