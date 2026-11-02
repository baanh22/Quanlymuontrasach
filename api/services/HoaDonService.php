<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/SachRepository.php';
require_once __DIR__ . '/../repositories/KhachHangRepository.php';
require_once __DIR__ . '/../repositories/ThamSoRepository.php';
require_once __DIR__ . '/../repositories/HoaDonRepository.php';

/**
 * Service: HoaDonService
 * Thực thi Quy định 2 (QĐ2):
 * - Chỉ bán cho khách hàng có nợ hiện tại <= SoTienNoToiDa (Mặc định: 20,000đ)
 * - Lượng tồn sau khi bán (SoLuongTon - SoLuongBan) >= SoLuongTonSauToiThieu (Mặc định: 20 cuốn)
 * - Đơn giá bán = DonGia * DonGiaBanYeuCau (Mặc định: 105%)
 */
class HoaDonService {
    private PDO $conn;
    private SachRepository $sachRepo;
    private KhachHangRepository $khRepo;
    private ThamSoRepository $thamSoRepo;
    private HoaDonRepository $hoaDonRepo;

    public function __construct(PDO $conn) {
        $this->conn       = $conn;
        $this->sachRepo   = new SachRepository($conn);
        $this->khRepo     = new KhachHangRepository($conn);
        $this->thamSoRepo = new ThamSoRepository($conn);
        $this->hoaDonRepo = new HoaDonRepository($conn);
    }

    /**
     * Hàm thuần (Pure Validator & Calculator) phục vụ kiểm thử đơn vị (Unit Test) cho QĐ2
     */
    public static function validateAndCalculateQuyDinh2(
        float $noHienTai,
        int $tonHienTai,
        float $donGiaNhap,
        int $soLuongBan,
        ThamSo $thamSo
    ): array {
        if ($noHienTai > $thamSo->SoTienNoToiDa) {
            throw new BusinessException(
                "Khách hàng đang nợ " . number_format($noHienTai) . "đ, vượt quá định mức " . number_format($thamSo->SoTienNoToiDa) . "đ. Vui lòng thanh toán trước."
            );
        }

        if ($soLuongBan > $tonHienTai) {
            throw new BusinessException("Số lượng bán ($soLuongBan) vượt quá tồn kho ($tonHienTai).");
        }

        $tonSauKhiBan = $tonHienTai - $soLuongBan;
        if ($tonSauKhiBan < $thamSo->SoLuongTonSauToiThieu) {
            throw new BusinessException(
                "Tồn sau khi bán ($tonSauKhiBan cuốn) nhỏ hơn mức tối thiểu quy định ({$thamSo->SoLuongTonSauToiThieu} cuốn)."
            );
        }

        $giaBan   = round($donGiaNhap * $thamSo->DonGiaBanYeuCau, 2);
        $tongTien = round($giaBan * $soLuongBan, 2);

        return [
            'GiaBan'       => $giaBan,
            'TongTien'     => $tongTien,
            'TonSauKhiBan' => $tonSauKhiBan
        ];
    }

    public function getHistory(): array {
        return $this->hoaDonRepo->findAllHistory();
    }

    public function createHoaDon(array $body): array {
        $maKH    = trim((string)($body['MaKhachHang'] ?? ''));
        $maSach  = trim((string)($body['MaSach'] ?? ''));
        $soLuong = (int)($body['SoLuong'] ?? 0);

        if ($maKH === '' || $maSach === '' || $soLuong <= 0) {
            throw new BusinessException('Vui lòng điền đầy đủ thông tin hóa đơn.');
        }

        // Đảm bảo bảng CHITIETPHIEUHOADON có cột DonGiaBan trước khi mở Transaction (vì DDL gây implicit commit trong MySQL)
        $this->hoaDonRepo->ensureDonGiaBanColumn();
        $thamSo = $this->thamSoRepo->getThamSo();

        $this->conn->beginTransaction();
        try {
            // Khóa dòng Khách hàng & Sách (FOR UPDATE) chống Race Condition
            $khData = $this->khRepo->findById($maKH, true);
            if (!$khData) {
                throw new BusinessException('Khách hàng không tồn tại.', 404);
            }

            $sachData = $this->sachRepo->findById($maSach, true);
            if (!$sachData) {
                throw new BusinessException('Sách không tồn tại.', 404);
            }

            // Kiểm tra QĐ2 và tính toán giá bán
            $calc = self::validateAndCalculateQuyDinh2(
                (float)$khData['SoTienNo'],
                (int)$sachData['SoLuongTon'],
                (float)$sachData['DonGia'],
                $soLuong,
                $thamSo
            );

            $giaBan   = $calc['GiaBan'];
            $tongTien = $calc['TongTien'];

            // Tạo phiếu hóa đơn & chi tiết (kèm DonGiaBan lịch sử)
            $maHD = $this->hoaDonRepo->createHoaDon($maKH, $maSach, $soLuong, $giaBan);

            // Trừ tồn kho sách
            $this->sachRepo->decreaseStock($maSach, $soLuong);

            // Cộng nợ khách hàng
            $this->khRepo->increaseDebt($maKH, $tongTien);

            $this->conn->commit();

            return [
                'data' => [
                    'MaPhieuHoaDon' => $maHD,
                    'TongTien'      => $tongTien,
                    'GiaBan'        => $giaBan,
                ],
                'message' => "Lập hóa đơn thành công! Tổng tiền: " . number_format($tongTien) . "đ"
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
            throw new BusinessException('Lỗi tạo hóa đơn: ' . $e->getMessage(), 500);
        }
    }
}
?>

