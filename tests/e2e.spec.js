/**
 * ============================================================================
 * PLAYWRIGHT E2E AUTOMATION TEST SPECIFICATION
 * Dự án: Hệ thống Quản lý Nhà sách (Quanlymuontrasach)
 * Chuyển đổi từ Gherkin User Stories (PRD.md) sang kịch bản kiểm thử trình duyệt
 * ============================================================================
 */
const { test, expect } = require('@playwright/test');

const BASE_URL = process.env.BASE_URL || 'http://localhost/Quanlymuontrasach/frontend';

test.describe('Hệ Thống Quản Lý Nhà Sách - E2E Test Suite (QĐ1 - QĐ4)', () => {

    test.beforeEach(async ({ page }) => {
        // Given: Người dùng đăng nhập với tài khoản Quản trị viên (admin / admin)
        await page.goto(`${BASE_URL}/login.html`);
        await page.fill('#username', 'admin');
        await page.fill('#password', 'admin');
        await page.click('#btnLogin');
        await expect(page).toHaveURL(/index\.html#\/dashboard/);
    });

    test('E2E-01 [US-01 / QĐ1]: Chặn lập phiếu nhập khi số lượng nhập < 150 cuốn', async ({ page }) => {
        await page.goto(`${BASE_URL}/index.html#/nhapsach`);
        await page.selectOption('#pnSach', 'S001');
        await page.fill('#pnSoLuong', '100'); // Vi phạm QĐ1 (< 150)
        await page.fill('#pnDonGia', '85000');
        await page.click('#frmNhapSach button[type="submit"]');

        const alertBox = page.locator('#msgNhapSach');
        await expect(alertBox).toHaveClass(/error/);
        await expect(alertBox).toContainText('phải lớn hơn hoặc bằng 150 cuốn');
    });

    test('E2E-02 [US-01 / QĐ1]: Chặn lập phiếu nhập đối với sách có lượng tồn >= 300 cuốn', async ({ page }) => {
        await page.goto(`${BASE_URL}/index.html#/nhapsach`);
        // S005 (Dế Mèn Phiêu Lưu Ký) có tồn kho mặc định = 320 cuốn (>= 300)
        await page.selectOption('#pnSach', 'S005');
        await page.fill('#pnSoLuong', '150');
        await page.fill('#pnDonGia', '45000');
        await page.click('#frmNhapSach button[type="submit"]');

        const alertBox = page.locator('#msgNhapSach');
        await expect(alertBox).toHaveClass(/error/);
        await expect(alertBox).toContainText('nhỏ hơn 300 cuốn');
    });

    test('E2E-03 [US-02 / QĐ2]: Chặn bán sách nếu lượng tồn sau khi bán < 20 cuốn', async ({ page }) => {
        await page.goto(`${BASE_URL}/index.html#/hoadon`);
        await page.selectOption('#hdKhachHang', 'KH001');
        // S006 có tồn 80 cuốn -> Bán 65 cuốn còn 15 cuốn (< 20 cuốn tối thiểu)
        await page.selectOption('#hdSach', 'S006');
        await page.fill('#hdSoLuong', '65');
        await page.click('#frmHoaDon button[type="submit"]');

        const alertBox = page.locator('#msgHoaDon');
        await expect(alertBox).toHaveClass(/error/);
        await expect(alertBox).toContainText('nhỏ hơn mức tối thiểu quy định');
    });

    test('E2E-04 [US-03 / QĐ3]: Chặn thu tiền vượt quá số nợ hiện tại của khách hàng', async ({ page }) => {
        await page.goto(`${BASE_URL}/index.html#/phieuthu`);
        await page.selectOption('#ptKhachHang', 'KH002'); // KH002 đang nợ 15,000đ
        // Bỏ thuộc tính max ở client để test chặn ở Backend API
        await page.evaluate(() => document.getElementById('ptSoTien').removeAttribute('max'));
        await page.fill('#ptSoTien', '50000');
        await page.click('#frmPhieuThu button[type="submit"]');

        const alertBox = page.locator('#msgPhieuThu');
        await expect(alertBox).toHaveClass(/error/);
        await expect(alertBox).toContainText('không được phép vượt quá số tiền khách hàng đang nợ');
    });
});

