<?php
/**
 * Domain Model: KhachHang (Khách hàng & Công nợ)
 * Layer: Models (Pure Data Object - Không phụ thuộc tầng trên)
 */
class KhachHang {
    public string $MaKhachHang;
    public string $HoTenKhachHang;
    public string $DiaChi;
    public string $DienThoai;
    public string $Email;
    public float $SoTienNo;

    public function __construct(array $data = []) {
        $this->MaKhachHang    = trim((string)($data['MaKhachHang'] ?? ''));
        $this->HoTenKhachHang = trim((string)($data['HoTenKhachHang'] ?? ''));
        $this->DiaChi         = trim((string)($data['DiaChi'] ?? ''));
        $this->DienThoai      = trim((string)($data['DienThoai'] ?? ''));
        $this->Email          = trim((string)($data['Email'] ?? ''));
        $this->SoTienNo       = (float)($data['SoTienNo'] ?? 0.0);
    }

    public function toArray(): array {
        return [
            'MaKhachHang'    => $this->MaKhachHang,
            'HoTenKhachHang' => $this->HoTenKhachHang,
            'DiaChi'         => $this->DiaChi,
            'DienThoai'      => $this->DienThoai,
            'Email'          => $this->Email,
            'SoTienNo'       => $this->SoTienNo,
        ];
    }
}
?>

