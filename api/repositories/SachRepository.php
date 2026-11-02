<?php
require_once __DIR__ . '/../models/Sach.php';

/**
 * Repository: SachRepository
 * Layer: Data Access Layer (Chỉ giao tiếp với PDO Database & Model Sach)
 */
class SachRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findAll(string $search = ''): array {
        $sql = "SELECT * FROM SACH";
        if ($search !== '') {
            $sql .= " WHERE TenSach LIKE :search OR TacGia LIKE :search OR TheLoai LIKE :search OR MaSach LIKE :search";
        }
        $sql .= " ORDER BY TenSach ASC";
        $stmt = $this->conn->prepare($sql);
        if ($search !== '') {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function findById(string $maSach, bool $forUpdate = false): ?array {
        $sql = "SELECT * FROM SACH WHERE MaSach = ?";
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maSach]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(Sach $sach): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO SACH (MaSach, TenSach, TheLoai, TacGia, SoLuongTon, DonGia) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $sach->MaSach,
            $sach->TenSach,
            $sach->TheLoai,
            $sach->TacGia,
            $sach->SoLuongTon,
            $sach->DonGia
        ]);
    }

    public function update(Sach $sach): int {
        $stmt = $this->conn->prepare(
            "UPDATE SACH SET TenSach = ?, TheLoai = ?, TacGia = ?, SoLuongTon = ?, DonGia = ? WHERE MaSach = ?"
        );
        $stmt->execute([
            $sach->TenSach,
            $sach->TheLoai,
            $sach->TacGia,
            $sach->SoLuongTon,
            $sach->DonGia,
            $sach->MaSach
        ]);
        return $stmt->rowCount();
    }

    public function increaseStockAndPrice(string $maSach, int $soLuongNhap, float $donGiaNhap): bool {
        $stmt = $this->conn->prepare(
            "UPDATE SACH SET SoLuongTon = SoLuongTon + ?, DonGia = ? WHERE MaSach = ?"
        );
        return $stmt->execute([$soLuongNhap, $donGiaNhap, $maSach]);
    }

    public function decreaseStock(string $maSach, int $soLuongBan): bool {
        $stmt = $this->conn->prepare(
            "UPDATE SACH SET SoLuongTon = SoLuongTon - ? WHERE MaSach = ?"
        );
        return $stmt->execute([$soLuongBan, $maSach]);
    }

    public function hasRelatedTransactions(string $maSach): bool {
        $stmtHD = $this->conn->prepare("SELECT COUNT(*) as cnt FROM CHITIETPHIEUHOADON WHERE MaSach = ?");
        $stmtHD->execute([$maSach]);
        if ((int)$stmtHD->fetch()['cnt'] > 0) {
            return true;
        }

        $stmtPN = $this->conn->prepare("SELECT COUNT(*) as cnt FROM CHITIETPHIEUNHAP WHERE MaSach = ?");
        $stmtPN->execute([$maSach]);
        return (int)$stmtPN->fetch()['cnt'] > 0;
    }

    public function delete(string $maSach): int {
        $stmt = $this->conn->prepare("DELETE FROM SACH WHERE MaSach = ?");
        $stmt->execute([$maSach]);
        return $stmt->rowCount();
    }
}
?>

