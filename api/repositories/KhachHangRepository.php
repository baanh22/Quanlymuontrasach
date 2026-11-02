<?php
require_once __DIR__ . '/../models/KhachHang.php';

/**
 * Repository: KhachHangRepository
 * Layer: Data Access Layer (Chỉ giao tiếp với PDO Database & Model KhachHang)
 */
class KhachHangRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findAll(string $search = ''): array {
        $sql = "SELECT * FROM KHACHHANG";
        if ($search !== '') {
            $sql .= " WHERE HoTenKhachHang LIKE :search OR DienThoai LIKE :search OR MaKhachHang LIKE :search";
        }
        $sql .= " ORDER BY HoTenKhachHang ASC";
        $stmt = $this->conn->prepare($sql);
        if ($search !== '') {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function findById(string $maKH, bool $forUpdate = false): ?array {
        $sql = "SELECT * FROM KHACHHANG WHERE MaKhachHang = ?";
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maKH]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(KhachHang $kh): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO KHACHHANG (MaKhachHang, HoTenKhachHang, DiaChi, DienThoai, Email, SoTienNo) VALUES (?, ?, ?, ?, ?, 0)"
        );
        return $stmt->execute([
            $kh->MaKhachHang,
            $kh->HoTenKhachHang,
            $kh->DiaChi,
            $kh->DienThoai,
            $kh->Email
        ]);
    }

    public function update(KhachHang $kh): int {
        $stmt = $this->conn->prepare(
            "UPDATE KHACHHANG SET HoTenKhachHang = ?, DiaChi = ?, DienThoai = ?, Email = ? WHERE MaKhachHang = ?"
        );
        $stmt->execute([
            $kh->HoTenKhachHang,
            $kh->DiaChi,
            $kh->DienThoai,
            $kh->Email,
            $kh->MaKhachHang
        ]);
        return $stmt->rowCount();
    }

    public function increaseDebt(string $maKH, float $amount): bool {
        $stmt = $this->conn->prepare("UPDATE KHACHHANG SET SoTienNo = SoTienNo + ? WHERE MaKhachHang = ?");
        return $stmt->execute([$amount, $maKH]);
    }

    public function decreaseDebt(string $maKH, float $amount): bool {
        $stmt = $this->conn->prepare("UPDATE KHACHHANG SET SoTienNo = SoTienNo - ? WHERE MaKhachHang = ?");
        return $stmt->execute([$amount, $maKH]);
    }

    public function hasRelatedTransactions(string $maKH): bool {
        $stmtHD = $this->conn->prepare("SELECT COUNT(*) as cnt FROM PHIEUHOADON WHERE MaKhachHang = ?");
        $stmtHD->execute([$maKH]);
        if ((int)$stmtHD->fetch()['cnt'] > 0) {
            return true;
        }

        $stmtPT = $this->conn->prepare("SELECT COUNT(*) as cnt FROM PHIEUTHUTIEN WHERE MaKhachHang = ?");
        $stmtPT->execute([$maKH]);
        return (int)$stmtPT->fetch()['cnt'] > 0;
    }

    public function delete(string $maKH): int {
        $stmt = $this->conn->prepare("DELETE FROM KHACHHANG WHERE MaKhachHang = ?");
        $stmt->execute([$maKH]);
        return $stmt->rowCount();
    }
}
?>

