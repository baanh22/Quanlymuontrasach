<?php
/**
 * Domain Model: ThamSo (Quy định nghiệp vụ QĐ1 - QĐ4)
 * Layer: Models (Pure Data Object - Không phụ thuộc tầng trên)
 */
class ThamSo {
    public int $SoLuongNhapItNhat;
    public int $SoLuongTonToiDaTruocNhap;
    public float $SoTienNoToiDa;
    public int $SoLuongTonSauToiThieu;
    public float $DonGiaBanYeuCau;
    public int $QuyDinh;

    public function __construct(array $data = []) {
        $this->SoLuongNhapItNhat        = (int)($data['SoLuongNhapItNhat'] ?? 150);
        $this->SoLuongTonToiDaTruocNhap = (int)($data['SoLuongTonToiDaTruocNhap'] ?? 300);
        $this->SoTienNoToiDa            = (float)($data['SoTienNoToiDa'] ?? 20000.0);
        $this->SoLuongTonSauToiThieu    = (int)($data['SoLuongTonSauToiThieu'] ?? 20);
        $this->DonGiaBanYeuCau          = (float)($data['DonGiaBanYeuCau'] ?? 1.05);
        $this->QuyDinh                  = isset($data['QuyDinh']) ? (int)$data['QuyDinh'] : 1;
    }

    public function toArray(): array {
        return [
            'SoLuongNhapItNhat'        => $this->SoLuongNhapItNhat,
            'SoLuongTonToiDaTruocNhap' => $this->SoLuongTonToiDaTruocNhap,
            'SoTienNoToiDa'            => $this->SoTienNoToiDa,
            'SoLuongTonSauToiThieu'    => $this->SoLuongTonSauToiThieu,
            'DonGiaBanYeuCau'          => $this->DonGiaBanYeuCau,
            'QuyDinh'                  => $this->QuyDinh,
        ];
    }
}
?>

