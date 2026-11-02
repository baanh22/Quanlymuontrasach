<?php
require_once __DIR__ . '/../models/ThamSo.php';

/**
 * Repository: ThamSoRepository
 * Layer: Data Access Layer (Quản lý bảng THAMSO QĐ1 - QĐ4)
 */
class ThamSoRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getThamSo(): ThamSo {
        $stmt = $this->conn->query("SELECT * FROM THAMSO LIMIT 1");
        $row = $stmt ? $stmt->fetch() : false;
        return new ThamSo($row ?: []);
    }

    public function updateThamSo(ThamSo $thamSo): bool {
        $stmt = $this->conn->prepare(
            "UPDATE THAMSO SET 
                SoLuongNhapItNhat = ?, 
                SoLuongTonToiDaTruocNhap = ?, 
                SoTienNoToiDa = ?, 
                SoLuongTonSauToiThieu = ?, 
                DonGiaBanYeuCau = ?, 
                QuyDinh = ?"
        );
        return $stmt->execute([
            $thamSo->SoLuongNhapItNhat,
            $thamSo->SoLuongTonToiDaTruocNhap,
            $thamSo->SoTienNoToiDa,
            $thamSo->SoLuongTonSauToiThieu,
            $thamSo->DonGiaBanYeuCau,
            $thamSo->QuyDinh
        ]);
    }
}
?>

