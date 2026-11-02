<?php
require_once __DIR__ . '/BusinessException.php';
require_once __DIR__ . '/../repositories/ThamSoRepository.php';
require_once __DIR__ . '/../repositories/TaiKhoanRepository.php';
require_once __DIR__ . '/../repositories/BaoCaoRepository.php';

/**
 * Service: HeThongBaoCaoService
 * Quản lý Quy định 4 (QĐ4 - Thay đổi tham số), Quản lý Nhân viên (RBAC + Bcrypt) và Báo cáo thống kê
 */
class HeThongBaoCaoService {
    private ThamSoRepository $thamSoRepo;
    private TaiKhoanRepository $taiKhoanRepo;
    private BaoCaoRepository $baoCaoRepo;

    public function __construct(PDO $conn) {
        $this->thamSoRepo   = new ThamSoRepository($conn);
        $this->taiKhoanRepo = new TaiKhoanRepository($conn);
        $this->baoCaoRepo   = new BaoCaoRepository($conn);
    }

    // ==================== CẤU HÌNH QUY ĐỊNH (QĐ4) ====================
    public function getCauHinh(): array {
        return $this->thamSoRepo->getThamSo()->toArray();
    }

    public function updateCauHinh(array $body): void {
        $current = $this->thamSoRepo->getThamSo();

        $soLuongNhapItNhat        = (int)($body['SoLuongNhapItNhat'] ?? 0);
        $soLuongTonToiDaTruocNhap = (int)($body['SoLuongTonToiDaTruocNhap'] ?? 0);
        $soTienNoToiDa            = (float)($body['SoTienNoToiDa'] ?? -1);
        $soLuongTonSauToiThieu    = (int)($body['SoLuongTonSauToiThieu'] ?? -1);
        $donGiaBanYeuCau          = isset($body['DonGiaBanYeuCau']) ? (float)$body['DonGiaBanYeuCau'] : $current->DonGiaBanYeuCau;
        $quyDinh                  = isset($body['QuyDinh']) ? (int)$body['QuyDinh'] : $current->QuyDinh;

        if ($soLuongNhapItNhat <= 0 || $soLuongTonToiDaTruocNhap <= 0 || $soTienNoToiDa < 0 || $soLuongTonSauToiThieu < 0 || $donGiaBanYeuCau <= 0) {
            throw new BusinessException('Các tham số phải là số hợp lệ và lớn hơn 0.');
        }

        $newThamSo = new ThamSo([
            'SoLuongNhapItNhat'        => $soLuongNhapItNhat,
            'SoLuongTonToiDaTruocNhap' => $soLuongTonToiDaTruocNhap,
            'SoTienNoToiDa'            => $soTienNoToiDa,
            'SoLuongTonSauToiThieu'    => $soLuongTonSauToiThieu,
            'DonGiaBanYeuCau'          => $donGiaBanYeuCau,
            'QuyDinh'                  => $quyDinh ? 1 : 0,
        ]);

        $this->thamSoRepo->updateThamSo($newThamSo);
    }

    // ==================== QUẢN LÝ NHÂN VIÊN ====================
    public function getAllNhanVien(): array {
        return $this->taiKhoanRepo->findAllSafe();
    }

    public function createNhanVien(array $body): void {
        $username = trim((string)($body['Username'] ?? ''));
        $password = trim((string)($body['Password'] ?? ''));
        $fullName = trim((string)($body['FullName'] ?? ''));
        $role     = trim((string)($body['Role'] ?? 'User'));

        if ($username === '' || $password === '' || $fullName === '') {
            throw new BusinessException('Vui lòng nhập đầy đủ Username, Password và Họ Tên.');
        }

        if (!in_array($role, ['Admin', 'User'], true)) {
            $role = 'User';
        }

        if ($this->taiKhoanRepo->findByUsername($username)) {
            throw new BusinessException('Tên đăng nhập (Username) này đã tồn tại trong hệ thống.');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $tk = new TaiKhoan([
            'Username' => $username,
            'Password' => $hashedPassword,
            'FullName' => $fullName,
            'Role'     => $role,
        ]);

        $this->taiKhoanRepo->insert($tk);
    }

    public function updateNhanVien(string $username, array $body): array {
        $username = trim($username);
        if ($username === '') {
            throw new BusinessException('Thiếu mã Username.');
        }

        $existing = $this->taiKhoanRepo->findByUsername($username);
        if (!$existing) {
            throw new BusinessException('Không tìm thấy tài khoản nhân viên.', 404);
        }

        $fullName = trim((string)($body['FullName'] ?? ''));
        $role     = trim((string)($body['Role'] ?? 'User'));
        $password = trim((string)($body['Password'] ?? ''));

        if ($fullName === '') {
            throw new BusinessException('Họ tên không được để trống.');
        }

        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $this->taiKhoanRepo->updateWithPassword($username, $fullName, $role, $hashed);
        } else {
            $this->taiKhoanRepo->updateInfo($username, $fullName, $role);
        }

        return ['Username' => $username, 'FullName' => $fullName, 'Role' => $role];
    }

    public function deleteNhanVien(string $username, string $currentUserId): void {
        $username = trim($username);
        if ($username === '') {
            throw new BusinessException('Thiếu mã Username.');
        }

        if ($username === $currentUserId) {
            throw new BusinessException('Bạn không thể tự xóa tài khoản của chính mình.');
        }

        $deleted = $this->taiKhoanRepo->delete($username);
        if ($deleted === 0) {
            throw new BusinessException('Không tìm thấy tài khoản để xóa.', 404);
        }
    }

    // ==================== BÁO CÁO & THỐNG KÊ ====================
    public function getBaoCaoTon(int $thang, int $nam): array {
        if ($thang < 1 || $thang > 12 || $nam < 2000 || $nam > 2100) {
            throw new BusinessException('Tháng hoặc năm báo cáo không hợp lệ.');
        }

        $firstDayOfMonth     = "$nam-" . str_pad((string)$thang, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
        $firstDayOfNextMonth = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($firstDayOfMonth)));

        $sachData = $this->baoCaoRepo->getTonKhoByPeriod($firstDayOfMonth, $firstDayOfNextMonth);
        $result   = [];

        foreach ($sachData as $s) {
            $tonDau      = (int)$s['NhapDauKy'] - (int)$s['BanDauKy'];
            $nhapTrongKy = (int)$s['NhapTrongKy'];
            $banTrongKy  = (int)$s['BanTrongKy'];
            $tonPhatSinh = $nhapTrongKy - $banTrongKy;
            $tonCuoi     = $tonDau + $tonPhatSinh;

            if ($tonDau > 0 || $nhapTrongKy > 0 || $banTrongKy > 0 || $tonCuoi > 0) {
                $result[] = [
                    'MaSach'   => $s['MaSach'],
                    'TenSach'  => $s['TenSach'],
                    'TonDau'   => $tonDau,
                    'Nhap'     => $nhapTrongKy,
                    'Ban'      => $banTrongKy,
                    'PhatSinh' => $tonPhatSinh,
                    'TonCuoi'  => $tonCuoi,
                ];
            }
        }

        return $result;
    }

    public function getBaoCaoCongNo(int $thang, int $nam): array {
        if ($thang < 1 || $thang > 12 || $nam < 2000 || $nam > 2100) {
            throw new BusinessException('Tháng hoặc năm báo cáo không hợp lệ.');
        }

        $firstDayOfMonth     = "$nam-" . str_pad((string)$thang, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
        $firstDayOfNextMonth = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($firstDayOfMonth)));

        $thamSo = $this->thamSoRepo->getThamSo();
        $khData = $this->baoCaoRepo->getCongNoByPeriod($firstDayOfMonth, $firstDayOfNextMonth, $thamSo->DonGiaBanYeuCau);
        $result = [];

        foreach ($khData as $k) {
            $noDau      = (float)$k['NoTangDauKy'] - (float)$k['NoGiamDauKy'];
            $noPhatSinh = (float)$k['NoTangTrongKy'];
            $daThu      = (float)$k['NoGiamTrongKy'];
            $noCuoi     = $noDau + $noPhatSinh - $daThu;

            if ($noDau > 0 || $noPhatSinh > 0 || $daThu > 0 || $noCuoi > 0) {
                $result[] = [
                    'MaKhachHang'    => $k['MaKhachHang'],
                    'HoTenKhachHang' => $k['HoTenKhachHang'],
                    'NoDau'          => round($noDau),
                    'PhatSinh'       => round($noPhatSinh),
                    'DaThu'          => round($daThu),
                    'NoCuoi'         => round($noCuoi),
                ];
            }
        }

        usort($result, fn($a, $b) => $b['NoCuoi'] <=> $a['NoCuoi']);
        return $result;
    }

    public function getDashboardStats(): array {
        return $this->baoCaoRepo->getDashboardStats();
    }
}
?>

