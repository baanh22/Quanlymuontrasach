<?php
/**
 * Repository: HoaDonRepository
 * Layer: Data Access Layer (Quản lý bảng PHIEUHOADON và CHITIETPHIEUHOADON)
 */
class HoaDonRepository {
    private PDO $conn;
    private ?bool $hasDonGiaBanCol = null;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Tự động kiểm tra và bổ sung cột DonGiaBan nếu CSDL cũ chưa migrate (Self-healing schema)
     */
    public function ensureDonGiaBanColumn(): bool {
        if ($this->hasDonGiaBanCol !== null) {
            return $this->hasDonGiaBanCol;
        }
        try {
            $stmt = $this->conn->query("SHOW COLUMNS FROM CHITIETPHIEUHOADON LIKE 'DonGiaBan'");
            if ($stmt && $stmt->fetch()) {
                $this->hasDonGiaBanCol = true;
                return true;
            }
            $this->conn->exec("ALTER TABLE CHITIETPHIEUHOADON ADD COLUMN DonGiaBan DECIMAL(15,2) NOT NULL DEFAULT 0.00");
            $this->hasDonGiaBanCol = true;
            return true;
        } catch (Exception $e) {
            $this->hasDonGiaBanCol = false;
            return false;
        }
    }

    public function findAllHistory(): array {
        $hasCol = $this->ensureDonGiaBanColumn();
        $tongTienExpr = $hasCol
            ? "SUM(ct.SoLuongBan * IF(ct.DonGiaBan > 0, ct.DonGiaBan, s.DonGia * (SELECT DonGiaBanYeuCau FROM THAMSO LIMIT 1)))"
            : "SUM(ct.SoLuongBan * s.DonGia * (SELECT DonGiaBanYeuCau FROM THAMSO LIMIT 1))";

        $sql = "SELECT hd.MaPhieuHoaDon, hd.NgayLapHoaDon, hd.MaKhachHang,
                       kh.HoTenKhachHang,
                       GROUP_CONCAT(s.TenSach SEPARATOR ', ') as DanhSachSach,
                       SUM(ct.SoLuongBan) as TongSoLuong,
                       $tongTienExpr as TongTien
                FROM PHIEUHOADON hd
                LEFT JOIN KHACHHANG kh ON kh.MaKhachHang = hd.MaKhachHang
                LEFT JOIN CHITIETPHIEUHOADON ct ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon
                LEFT JOIN SACH s ON s.MaSach = ct.MaSach
                GROUP BY hd.MaPhieuHoaDon
                ORDER BY hd.NgayLapHoaDon DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function createHoaDon(string $maKH, string $maSach, int $soLuong, float $donGiaBan): int {
        $hasCol = $this->ensureDonGiaBanColumn();

        $hd = $this->conn->prepare("INSERT INTO PHIEUHOADON (NgayLapHoaDon, MaKhachHang) VALUES (NOW(), ?)");
        $hd->execute([$maKH]);
        $maHD = (int)$this->conn->lastInsertId();

        if ($hasCol) {
            $ct = $this->conn->prepare(
                "INSERT INTO CHITIETPHIEUHOADON (MaPhieuHoaDon, MaSach, SoLuongBan, DonGiaBan) VALUES (?, ?, ?, ?)"
            );
            $ct->execute([$maHD, $maSach, $soLuong, $donGiaBan]);
        } else {
            $ct = $this->conn->prepare(
                "INSERT INTO CHITIETPHIEUHOADON (MaPhieuHoaDon, MaSach, SoLuongBan) VALUES (?, ?, ?)"
            );
            $ct->execute([$maHD, $maSach, $soLuong]);
        }

        return $maHD;
    }
}
?>

