<?php
/**
 * Repository: BaoCaoRepository
 * Layer: Data Access Layer (Tối ưu hóa truy vấn bằng CTE / Derived Table JOIN tránh lỗi N+1 Subqueries)
 */
class BaoCaoRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Truy vấn Báo cáo Tồn kho sử dụng LEFT JOIN Derived Tables thay vì 4 Correlated Subqueries trên mỗi dòng SACH
     */
    public function getTonKhoByPeriod(string $firstDayOfMonth, string $firstDayOfNextMonth): array {
        $sql = "
            SELECT 
                s.MaSach, 
                s.TenSach,
                COALESCE(pn_agg.NhapDauKy, 0) as NhapDauKy,
                COALESCE(hd_agg.BanDauKy, 0) as BanDauKy,
                COALESCE(pn_agg.NhapTrongKy, 0) as NhapTrongKy,
                COALESCE(hd_agg.BanTrongKy, 0) as BanTrongKy
            FROM SACH s
            LEFT JOIN (
                SELECT 
                    ct.MaSach,
                    SUM(CASE WHEN pn.NgayNhap < :start1 THEN ct.SoLuongNhap ELSE 0 END) as NhapDauKy,
                    SUM(CASE WHEN pn.NgayNhap >= :start2 AND pn.NgayNhap < :end1 THEN ct.SoLuongNhap ELSE 0 END) as NhapTrongKy
                FROM CHITIETPHIEUNHAP ct
                JOIN PHIEUNHAP pn ON ct.MaPhieuNhap = pn.MaPhieuNhap
                WHERE pn.NgayNhap < :end2
                GROUP BY ct.MaSach
            ) pn_agg ON pn_agg.MaSach = s.MaSach
            LEFT JOIN (
                SELECT 
                    ct.MaSach,
                    SUM(CASE WHEN hd.NgayLapHoaDon < :start3 THEN ct.SoLuongBan ELSE 0 END) as BanDauKy,
                    SUM(CASE WHEN hd.NgayLapHoaDon >= :start4 AND hd.NgayLapHoaDon < :end3 THEN ct.SoLuongBan ELSE 0 END) as BanTrongKy
                FROM CHITIETPHIEUHOADON ct
                JOIN PHIEUHOADON hd ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon
                WHERE hd.NgayLapHoaDon < :end4
                GROUP BY ct.MaSach
            ) hd_agg ON hd_agg.MaSach = s.MaSach
            ORDER BY s.MaSach ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'start1' => $firstDayOfMonth,
            'start2' => $firstDayOfMonth,
            'end1'   => $firstDayOfNextMonth,
            'end2'   => $firstDayOfNextMonth,
            'start3' => $firstDayOfMonth,
            'start4' => $firstDayOfMonth,
            'end3'   => $firstDayOfNextMonth,
            'end4'   => $firstDayOfNextMonth,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Truy vấn Báo cáo Công nợ ưu tiên lấy DonGiaBan lịch sử lưu trong CHITIETPHIEUHOADON
     */
    public function getCongNoByPeriod(string $firstDayOfMonth, string $firstDayOfNextMonth, float $defaultRate): array {
        // Kiểm tra cột DonGiaBan có tồn tại không
        $hasDonGiaBan = false;
        try {
            $chk = $this->conn->query("SHOW COLUMNS FROM CHITIETPHIEUHOADON LIKE 'DonGiaBan'");
            $hasDonGiaBan = ($chk && $chk->fetch());
        } catch (Exception $e) {
            $hasDonGiaBan = false;
        }

        $priceExpr1 = $hasDonGiaBan ? "IF(ct.DonGiaBan > 0, ct.DonGiaBan, s.DonGia * :tile1)" : "(s.DonGia * :tile1)";
        $priceExpr2 = $hasDonGiaBan ? "IF(ct.DonGiaBan > 0, ct.DonGiaBan, s.DonGia * :tile2)" : "(s.DonGia * :tile2)";

        $sql = "
            SELECT 
                kh.MaKhachHang, 
                kh.HoTenKhachHang,
                COALESCE(hd_agg.NoTangDauKy, 0) as NoTangDauKy,
                COALESCE(pt_agg.NoGiamDauKy, 0) as NoGiamDauKy,
                COALESCE(hd_agg.NoTangTrongKy, 0) as NoTangTrongKy,
                COALESCE(pt_agg.NoGiamTrongKy, 0) as NoGiamTrongKy
            FROM KHACHHANG kh
            LEFT JOIN (
                SELECT 
                    hd.MaKhachHang,
                    SUM(CASE WHEN hd.NgayLapHoaDon < :start1 THEN ct.SoLuongBan * $priceExpr1 ELSE 0 END) as NoTangDauKy,
                    SUM(CASE WHEN hd.NgayLapHoaDon >= :start2 AND hd.NgayLapHoaDon < :end1 THEN ct.SoLuongBan * $priceExpr2 ELSE 0 END) as NoTangTrongKy
                FROM CHITIETPHIEUHOADON ct
                JOIN PHIEUHOADON hd ON ct.MaPhieuHoaDon = hd.MaPhieuHoaDon
                JOIN SACH s ON s.MaSach = ct.MaSach
                WHERE hd.NgayLapHoaDon < :end2
                GROUP BY hd.MaKhachHang
            ) hd_agg ON hd_agg.MaKhachHang = kh.MaKhachHang
            LEFT JOIN (
                SELECT 
                    pt.MaKhachHang,
                    SUM(CASE WHEN pt.NgayThuTien < :start3 THEN pt.SoTienThu ELSE 0 END) as NoGiamDauKy,
                    SUM(CASE WHEN pt.NgayThuTien >= :start4 AND pt.NgayThuTien < :end3 THEN pt.SoTienThu ELSE 0 END) as NoGiamTrongKy
                FROM PHIEUTHUTIEN pt
                WHERE pt.NgayThuTien < :end4
                GROUP BY pt.MaKhachHang
            ) pt_agg ON pt_agg.MaKhachHang = kh.MaKhachHang
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'tile1'  => $defaultRate,
            'tile2'  => $defaultRate,
            'start1' => $firstDayOfMonth,
            'start2' => $firstDayOfMonth,
            'end1'   => $firstDayOfNextMonth,
            'end2'   => $firstDayOfNextMonth,
            'start3' => $firstDayOfMonth,
            'start4' => $firstDayOfMonth,
            'end3'   => $firstDayOfNextMonth,
            'end4'   => $firstDayOfNextMonth,
        ]);

        return $stmt->fetchAll();
    }

    public function getDashboardStats(): array {
        $tongSach    = $this->conn->query("SELECT SUM(SoLuongTon) as total FROM SACH")->fetch()['total'] ?? 0;
        $soDauSach   = $this->conn->query("SELECT COUNT(*) as total FROM SACH")->fetch()['total'] ?? 0;
        $soKhachHang = $this->conn->query("SELECT COUNT(*) as total FROM KHACHHANG")->fetch()['total'] ?? 0;
        $soTienNo    = $this->conn->query("SELECT SUM(SoTienNo) as total FROM KHACHHANG")->fetch()['total'] ?? 0;
        $soHoaDon    = $this->conn->query("SELECT COUNT(*) as total FROM PHIEUHOADON")->fetch()['total'] ?? 0;
        $soPhieuNhap = $this->conn->query("SELECT COUNT(*) as total FROM PHIEUNHAP")->fetch()['total'] ?? 0;

        $sachSapHet = $this->conn->query(
            "SELECT MaSach, TenSach, TheLoai, SoLuongTon FROM SACH WHERE SoLuongTon < 100 ORDER BY SoLuongTon ASC LIMIT 5"
        )->fetchAll();

        $topSach = $this->conn->query("
            SELECT s.TenSach, SUM(ct.SoLuongBan) as TongBan
            FROM CHITIETPHIEUHOADON ct
            JOIN SACH s ON s.MaSach = ct.MaSach
            GROUP BY ct.MaSach
            ORDER BY TongBan DESC
            LIMIT 5
        ")->fetchAll();

        return [
            'tongSach'    => (int)$tongSach,
            'soDauSach'   => (int)$soDauSach,
            'soKhachHang' => (int)$soKhachHang,
            'soTienNo'    => (float)$soTienNo,
            'soHoaDon'    => (int)$soHoaDon,
            'soPhieuNhap' => (int)$soPhieuNhap,
            'sachSapHet'  => $sachSapHet,
            'topSach'     => $topSach,
        ];
    }
}
?>

