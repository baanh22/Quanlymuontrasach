<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/SachRepository.php';

/**
 * Service: SachService
 * Xử lý nghiệp vụ Danh mục Sách & kiểm tra ràng buộc toàn vẹn trước khi xóa
 */
class SachService {
    private SachRepository $sachRepo;

    public function __construct(PDO $conn) {
        $this->sachRepo = new SachRepository($conn);
    }

    public function getAllSach(string $search = ''): array {
        return $this->sachRepo->findAll(trim($search));
    }

    public function createSach(array $body): array {
        $sach = new Sach($body);

        if ($sach->MaSach === '' || $sach->TenSach === '') {
            throw new BusinessException('Mã sách và Tên sách không được để trống.', 400);
        }
        if ($sach->SoLuongTon < 0 || $sach->DonGia < 0) {
            throw new BusinessException('Số lượng tồn và Đơn giá không được là số âm.', 400);
        }

        if ($this->sachRepo->findById($sach->MaSach)) {
            throw new BusinessException("Mã sách '{$sach->MaSach}' đã tồn tại trong hệ thống.", 400);
        }

        $this->sachRepo->insert($sach);
        return ['MaSach' => $sach->MaSach];
    }

    public function updateSach(string $id, array $body): void {
        $id = trim($id);
        if ($id === '') {
            throw new BusinessException('Thiếu mã sách (id).', 400);
        }

        $existing = $this->sachRepo->findById($id);
        if (!$existing) {
            throw new BusinessException("Không tìm thấy sách có mã '$id'.", 404);
        }

        $body['MaSach'] = $id;
        $sach = new Sach($body);

        if ($sach->TenSach === '') {
            throw new BusinessException('Tên sách không được để trống.', 400);
        }
        if ($sach->SoLuongTon < 0 || $sach->DonGia < 0) {
            throw new BusinessException('Số lượng tồn và Đơn giá không được là số âm.', 400);
        }

        $this->sachRepo->update($sach);
    }

    public function deleteSach(string $id): void {
        $id = trim($id);
        if ($id === '') {
            throw new BusinessException('Thiếu mã sách (id).', 400);
        }

        $existing = $this->sachRepo->findById($id);
        if (!$existing) {
            throw new BusinessException("Không tìm thấy sách có mã '$id'.", 404);
        }

        if ($this->sachRepo->hasRelatedTransactions($id)) {
            throw new BusinessException('Không thể xóa sách đã phát sinh Phiếu nhập hoặc Hóa đơn liên quan.', 400);
        }

        $this->sachRepo->delete($id);
    }
}
?>

