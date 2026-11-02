<?php
/**
 * Repository: PhieuThuRepository
 * Layer: Data Access Layer (Quản lý bảng PHIEUTHUTIEN)
 */
class PhieuThuRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findAllHistory(): array {
        $sql = "SELECT pt.MaPhieuThu, pt.NgayThuTien, pt.SoTienThu, pt.MaKhachHang, 
                       kh.HoTenKhachHang, kh.SoTienNo 
                FROM PHIEUTHUTIEN pt
                JOIN KHACHHANG kh ON pt.MaKhachHang = kh.MaKhachHang
                ORDER BY pt.NgayThuTien DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function createPhieuThu(string $maKH, float $soTienThu): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO PHIEUTHUTIEN (MaKhachHang, NgayThuTien, SoTienThu) VALUES (?, NOW(), ?)"
        );
        $stmt->execute([$maKH, $soTienThu]);
        return (int)$this->conn->lastInsertId();
    }
}
?>

