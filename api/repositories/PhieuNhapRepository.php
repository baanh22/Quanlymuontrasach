<?php
/**
 * Repository: PhieuNhapRepository
 * Layer: Data Access Layer (Quản lý bảng PHIEUNHAP và CHITIETPHIEUNHAP)
 */
class PhieuNhapRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findAllHistory(): array {
        $sql = "SELECT pn.MaPhieuNhap, pn.NgayNhap,
                       GROUP_CONCAT(s.TenSach SEPARATOR ', ') as DanhSachSach,
                       SUM(ct.SoLuongNhap) as TongSoLuong,
                       SUM(ct.SoLuongNhap * ct.DonGiaNhap) as TongTien
                FROM PHIEUNHAP pn
                LEFT JOIN CHITIETPHIEUNHAP ct ON ct.MaPhieuNhap = pn.MaPhieuNhap
                LEFT JOIN SACH s ON s.MaSach = ct.MaSach
                GROUP BY pn.MaPhieuNhap
                ORDER BY pn.NgayNhap DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function createPhieuNhap(string $maSach, int $soLuong, float $donGia): int {
        $pn = $this->conn->prepare("INSERT INTO PHIEUNHAP (NgayNhap) VALUES (NOW())");
        $pn->execute();
        $maPhieuNhap = (int)$this->conn->lastInsertId();

        $ct = $this->conn->prepare(
            "INSERT INTO CHITIETPHIEUNHAP (MaPhieuNhap, MaSach, SoLuongNhap, DonGiaNhap) VALUES (?, ?, ?, ?)"
        );
        $ct->execute([$maPhieuNhap, $maSach, $soLuong, $donGia]);

        return $maPhieuNhap;
    }
}
?>

