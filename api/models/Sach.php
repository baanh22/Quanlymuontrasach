<?php
/**
 * Domain Model: Sach (Đầu sách)
 * Layer: Models (Pure Data Object - Không phụ thuộc tầng trên)
 */
class Sach {
    public string $MaSach;
    public string $TenSach;
    public string $TheLoai;
    public string $TacGia;
    public int $SoLuongTon;
    public float $DonGia;

    public function __construct(array $data = []) {
        $this->MaSach     = trim((string)($data['MaSach'] ?? ''));
        $this->TenSach    = trim((string)($data['TenSach'] ?? ''));
        $this->TheLoai    = trim((string)($data['TheLoai'] ?? ''));
        $this->TacGia     = trim((string)($data['TacGia'] ?? ''));
        $this->SoLuongTon = (int)($data['SoLuongTon'] ?? 0);
        $this->DonGia     = (float)($data['DonGia'] ?? 0.0);
    }

    public function toArray(): array {
        return [
            'MaSach'     => $this->MaSach,
            'TenSach'    => $this->TenSach,
            'TheLoai'    => $this->TheLoai,
            'TacGia'     => $this->TacGia,
            'SoLuongTon' => $this->SoLuongTon,
            'DonGia'     => $this->DonGia,
        ];
    }
}
?>

