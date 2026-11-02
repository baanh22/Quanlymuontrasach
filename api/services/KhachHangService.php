<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/KhachHangRepository.php';

/**
 * Service: KhachHangService
 * Xử lý nghiệp vụ Quản lý Khách hàng & Kiểm tra ràng buộc Hóa đơn/Phiếu thu trước khi xóa
 */
class KhachHangService {
    private KhachHangRepository $khRepo;

    public function __construct(PDO $conn) {
        $this->khRepo = new KhachHangRepository($conn);
    }

    public function getAllKhachHang(string $search = ''): array {
        return $this->khRepo->findAll(trim($search));
    }

    public function createKhachHang(array $body): array {
        $kh = new KhachHang($body);

        if ($kh->MaKhachHang === '' || $kh->HoTenKhachHang === '') {
            throw new BusinessException('Mã khách hàng và Họ tên không được để trống.', 400);
        }

        if ($this->khRepo->findById($kh->MaKhachHang)) {
            throw new BusinessException("Mã khách hàng '{$kh->MaKhachHang}' đã tồn tại.", 400);
        }

        $this->khRepo->insert($kh);
        return ['MaKhachHang' => $kh->MaKhachHang];
    }

    public function updateKhachHang(string $id, array $body): void {
        $id = trim($id);
        if ($id === '') {
            throw new BusinessException('Thiếu mã khách hàng (id).', 400);
        }

        $existing = $this->khRepo->findById($id);
        if (!$existing) {
            throw new BusinessException("Không tìm thấy khách hàng '$id'.", 404);
        }

        $body['MaKhachHang'] = $id;
        $kh = new KhachHang($body);

        if ($kh->HoTenKhachHang === '') {
            throw new BusinessException('Họ tên không được để trống.', 400);
        }

        $this->khRepo->update($kh);
    }

    public function deleteKhachHang(string $id): void {
        $id = trim($id);
        if ($id === '') {
            throw new BusinessException('Thiếu mã khách hàng (id).', 400);
        }

        $existing = $this->khRepo->findById($id);
        if (!$existing) {
            throw new BusinessException("Không tìm thấy khách hàng '$id'.", 404);
        }

        if ($this->khRepo->hasRelatedTransactions($id)) {
            throw new BusinessException('Không thể xóa khách hàng đã có lịch sử Hóa đơn hoặc Phiếu thu liên quan.', 400);
        }

        $this->khRepo->delete($id);
    }
}
?>

