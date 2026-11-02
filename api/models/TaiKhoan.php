<?php
/**
 * Domain Model: TaiKhoan (Nhân viên / Quản trị viên)
 * Layer: Models (Pure Data Object - Không phụ thuộc tầng trên)
 */
class TaiKhoan {
    public string $Username;
    public string $Password;
    public string $FullName;
    public string $Role;

    public function __construct(array $data = []) {
        $this->Username = trim((string)($data['Username'] ?? ''));
        $this->Password = (string)($data['Password'] ?? '');
        $this->FullName = trim((string)($data['FullName'] ?? ''));
        $this->Role     = trim((string)($data['Role'] ?? 'User'));
    }

    public function toSafeArray(): array {
        return [
            'Username' => $this->Username,
            'FullName' => $this->FullName,
            'Role'     => $this->Role,
        ];
    }
}
?>

