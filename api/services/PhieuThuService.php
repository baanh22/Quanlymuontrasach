<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/KhachHangRepository.php';
require_once __DIR__ . '/../repositories/ThamSoRepository.php';
require_once __DIR__ . '/../repositories/PhieuThuRepository.php';

/**
 * Service: PhieuThuService
 * Thực thi Quy định 3 (QĐ3):
 * - Nếu QuyDinh == 1: Số tiền thu không được vượt quá số tiền khách hàng đang nợ
 */
class PhieuThuService {
    private PDO $conn;
    private KhachHangRepository $khRepo;
    private ThamSoRepository $thamSoRepo;
    private PhieuThuRepository $phieuThuRepo;

    public function __construct(PDO $conn) {
        $this->conn         = $conn;
        $this->khRepo       = new KhachHangRepository($conn);
        $this->thamSoRepo   = new ThamSoRepository($conn);
        $this->phieuThuRepo = new PhieuThuRepository($conn);
    }

    /**
     * Hàm thuần (Pure Validator) phục vụ kiểm thử đơn vị (Unit Test) cho QĐ3
     */
    public static function validateQuyDinh3(float $soTienThu, float $noHienTai, ThamSo $thamSo): void {
        if ($soTienThu <= 0) {
            throw new BusinessException('Vui lòng nhập số tiền thu lớn hơn 0.');
        }

        if ($thamSo->QuyDinh === 1 && $soTienThu > $noHienTai) {
            throw new BusinessException(
                "Theo quy định hiện hành, số tiền thu (" . number_format($soTienThu) . "đ) không được phép vượt quá số tiền khách hàng đang nợ (" . number_format($noHienTai) . "đ)."
            );
        }
    }

    public function getHistory(): array {
        return $this->phieuThuRepo->findAllHistory();
    }

    public function createPhieuThu(array $body): array {
        $maKH   = trim((string)($body['MaKhachHang'] ?? ''));
        $soTien = (float)($body['SoTienThu'] ?? 0);

        if ($maKH === '' || $soTien <= 0) {
            throw new BusinessException('Vui lòng chọn khách hàng và nhập số tiền thu (lớn hơn 0).');
        }

        $thamSo = $this->thamSoRepo->getThamSo();

        $this->conn->beginTransaction();
        try {
            // Khóa dòng khách hàng (FOR UPDATE) chống thu trùng vượt nợ
            $khData = $this->khRepo->findById($maKH, true);
            if (!$khData) {
                throw new BusinessException('Khách hàng không tồn tại.', 404);
            }

            $noHienTai = (float)$khData['SoTienNo'];
            self::validateQuyDinh3($soTien, $noHienTai, $thamSo);

            $maPT = $this->phieuThuRepo->createPhieuThu($maKH, $soTien);
            $this->khRepo->decreaseDebt($maKH, $soTien);

            $noConLai = $noHienTai - $soTien;
            $this->conn->commit();

            return [
                'data' => [
                    'MaPhieuThu' => $maPT,
                    'NoConLai'   => $noConLai,
                ],
                'message' => "Lập phiếu thu tiền thành công! Nợ còn lại: " . number_format($noConLai) . "đ"
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
            throw new BusinessException('Lỗi hệ thống: ' . $e->getMessage(), 500);
        }
    }
}
?>

