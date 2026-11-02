<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/SachRepository.php';
require_once __DIR__ . '/../repositories/ThamSoRepository.php';
require_once __DIR__ . '/../repositories/PhieuNhapRepository.php';

/**
 * Service: NhapSachService
 * Thực thi Quy định 1 (QĐ1):
 * - Số lượng nhập >= SoLuongNhapItNhat (Mặc định: 150)
 * - Lượng tồn trước khi nhập < SoLuongTonToiDaTruocNhap (Mặc định: 300)
 */
class NhapSachService {
    private PDO $conn;
    private SachRepository $sachRepo;
    private ThamSoRepository $thamSoRepo;
    private PhieuNhapRepository $phieuNhapRepo;

    public function __construct(PDO $conn) {
        $this->conn          = $conn;
        $this->sachRepo      = new SachRepository($conn);
        $this->thamSoRepo    = new ThamSoRepository($conn);
        $this->phieuNhapRepo = new PhieuNhapRepository($conn);
    }

    /**
     * Hàm thuần (Pure Validator) phục vụ kiểm thử đơn vị (Unit Test) cho QĐ1
     */
    public static function validateQuyDinh1(int $soLuongNhap, int $tonHienTai, ThamSo $thamSo): void {
        if ($soLuongNhap < $thamSo->SoLuongNhapItNhat) {
            throw new BusinessException(
                "Số lượng nhập ({$soLuongNhap}) phải lớn hơn hoặc bằng {$thamSo->SoLuongNhapItNhat} cuốn theo quy định."
            );
        }

        if ($tonHienTai >= $thamSo->SoLuongTonToiDaTruocNhap) {
            throw new BusinessException(
                "Chỉ nhập sách có lượng tồn nhỏ hơn {$thamSo->SoLuongTonToiDaTruocNhap} cuốn. Hiện tồn: {$tonHienTai} cuốn."
            );
        }
    }

    public function getHistory(): array {
        return $this->phieuNhapRepo->findAllHistory();
    }

    public function createPhieuNhap(array $body): array {
        $maSach  = trim((string)($body['MaSach'] ?? ''));
        $soLuong = (int)($body['SoLuong'] ?? 0);
        $donGia  = (float)($body['DonGia'] ?? 0);

        if ($maSach === '' || $soLuong <= 0 || $donGia <= 0) {
            throw new BusinessException('Vui lòng điền đầy đủ thông tin phiếu nhập.');
        }

        $thamSo = $this->thamSoRepo->getThamSo();

        $this->conn->beginTransaction();
        try {
            // Khóa dòng sách (FOR UPDATE) để chống Race Condition khi 2 thủ kho nhập cùng lúc
            $sachData = $this->sachRepo->findById($maSach, true);
            if (!$sachData) {
                throw new BusinessException('Sách không tồn tại.', 404);
            }

            // Kiểm tra QĐ1
            self::validateQuyDinh1($soLuong, (int)$sachData['SoLuongTon'], $thamSo);

            // Lưu phiếu nhập & chi tiết
            $maPhieuNhap = $this->phieuNhapRepo->createPhieuNhap($maSach, $soLuong, $donGia);

            // Cập nhật tồn kho và đơn giá nhập mới nhất
            $this->sachRepo->increaseStockAndPrice($maSach, $soLuong, $donGia);

            $tonMoi = (int)$sachData['SoLuongTon'] + $soLuong;
            $this->conn->commit();

            return [
                'data' => [
                    'MaPhieuNhap' => $maPhieuNhap,
                    'TonMoi'      => $tonMoi,
                ],
                'message' => "Lập phiếu nhập thành công! Tồn kho mới: {$tonMoi} cuốn."
            ];
        } catch (BusinessException $be) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $be;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new BusinessException('Lỗi tạo phiếu nhập: ' . $e->getMessage(), 500);
        }
    }
}
?>

