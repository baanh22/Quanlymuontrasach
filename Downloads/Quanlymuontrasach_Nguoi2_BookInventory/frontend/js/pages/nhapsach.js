async function renderNhapSach() {
    setPageTitle('Nhập kho');
    setContent(`
        <header class="page-heading">
            <div><p class="eyebrow">Điều phối kho</p><h1>Nhập kho</h1></div>
            <p class="heading-note">Lập phiếu nhập để ghi nhận hàng về và cập nhật tồn kho. Mỗi phiếu nhập một đầu sách.</p>
        </header>
        <div class="workspace-grid">
            <section class="panel section-panel">
                <h2 class="section-title">Lập phiếu nhập</h2>
                <div id="receiptAlert" class="alert error" hidden></div>
                <form id="frmNhapSach">
                    <div class="form-group"><label for="pnSach">Đầu sách</label><select id="pnSach" required><option value="">Đang tải danh sách sách...</option></select></div>
                    <div class="form-row">
                        <div class="form-group"><label for="pnSoLuong">Số lượng nhập</label><input id="pnSoLuong" type="number" min="1" step="1" required /></div>
                        <div class="form-group"><label for="pnDonGia">Đơn giá nhập</label><input id="pnDonGia" type="number" min="0.01" step="0.01" required placeholder="VD: 50000" /></div>
                    </div>
                    <button class="btn btn-primary" type="submit">Lưu phiếu nhập</button>
                </form>
            </section>
            <section class="panel section-panel">
                <h2 class="section-title">Quy định nhập sách</h2>
                <div id="ruleContent" class="empty-state">Đang tải quy định...</div>
            </section>
        </div>
        <section class="panel">
            <div class="section-panel"><h2 class="section-title">Lịch sử nhập kho</h2></div>
            <div id="receiptAlertHistory" class="alert error" hidden></div>
            <div class="table-wrap"><table>
                <thead><tr><th>Mã phiếu</th><th>Ngày nhập</th><th>Mặt hàng</th><th>Tổng số lượng</th><th>Tổng tiền</th></tr></thead>
                <tbody id="tblNhapSachBody"><tr><td colspan="5" class="empty-state">Đang tải dữ liệu...</td></tr></tbody>
            </table></div>
        </section>
    `);
    document.getElementById('frmNhapSach').addEventListener('submit', submitNhapSach);
    document.getElementById('pnSach').addEventListener('change', updateStockPrice);
    await Promise.all([loadReceiptBooks(), loadReceiptRules(), loadReceiptHistory()]);
}

async function loadReceiptBooks() {
    const select = document.getElementById('pnSach');
    const response = await api.getSach();
    if (!response.success) {
        select.innerHTML = '<option value="">Không tải được danh sách sách</option>';
        showReceiptError(response.message);
        return;
    }
    window.receiptBooks = response.data;
    select.innerHTML = '<option value="">-- Chọn sách --</option>' + response.data.map((book) =>
        `<option value="${escapeHtml(book.MaSach)}" data-price="${escapeHtml(book.DonGia)}">${escapeHtml(book.TenSach)} — tồn ${Number(book.SoLuongTon)}</option>`
    ).join('');
    if (!response.data.length) select.innerHTML = '<option value="">Chưa có sách trong danh mục</option>';
}

async function loadReceiptRules() {
    const container = document.getElementById('ruleContent');
    const response = await api.getCauHinh();
    if (!response.success) {
        container.className = 'alert error';
        container.textContent = response.message || 'Không tải được quy định nhập.';
        return;
    }
    container.className = '';
    container.innerHTML = `
        <ul class="rule-list">
            <li><span>Số lượng nhập tối thiểu</span><strong>${Number(response.data.SoLuongNhapItNhat)} cuốn</strong></li>
            <li><span>Tồn tối đa trước khi nhập</span><strong>${Number(response.data.SoLuongTonToiDaTruocNhap)} cuốn</strong></li>
        </ul>
        <p class="sub-cell">Chỉ lập phiếu khi tồn kho hiện tại thấp hơn ngưỡng cho phép.</p>`;
    window.minimumReceiptQuantity = Number(response.data.SoLuongNhapItNhat);
    document.getElementById('pnSoLuong').min = window.minimumReceiptQuantity;
    document.getElementById('pnSoLuong').value = window.minimumReceiptQuantity;
}

async function loadReceiptHistory() {
    const body = document.getElementById('tblNhapSachBody');
    const alert = document.getElementById('receiptAlertHistory');
    const response = await api.getNhapSach();
    if (!response.success) {
        body.innerHTML = '';
        alert.hidden = false;
        alert.textContent = response.message || 'Không tải được lịch sử nhập kho.';
        return;
    }
    if (!response.data.length) {
        body.innerHTML = '<tr><td colspan="5" class="empty-state">Chưa có phiếu nhập nào.</td></tr>';
        return;
    }
    body.innerHTML = response.data.map((receipt) => `
        <tr>
            <td><span class="pill">PN${Number(receipt.MaPhieuNhap)}</span></td>
            <td>${escapeHtml(receipt.NgayNhap)}</td>
            <td class="primary-cell">${escapeHtml(receipt.DanhSachSach)}</td>
            <td>${Number(receipt.TongSoLuong)} cuốn</td>
            <td>${money(receipt.TongTien)}</td>
        </tr>`).join('');
}

function updateStockPrice() {
    const selected = document.getElementById('pnSach').selectedOptions[0];
    if (selected?.dataset.price) document.getElementById('pnDonGia').value = selected.dataset.price;
}

function showReceiptError(message, success = false) {
    const alert = document.getElementById('receiptAlert');
    alert.hidden = false;
    alert.className = `alert ${success ? 'success' : 'error'}`;
    alert.textContent = message;
}

async function submitNhapSach(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const data = {
        MaSach: document.getElementById('pnSach').value,
        SoLuong: Number(document.getElementById('pnSoLuong').value),
        DonGia: Number(document.getElementById('pnDonGia').value)
    };
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    showLoading(true);
    const response = await api.addNhapSach(data);
    showLoading(false);
    button.disabled = false;

    if (!response.success) {
        showReceiptError(response.message || 'Không thể lập phiếu nhập.');
        return;
    }
    showReceiptError(response.message, true);
    form.reset();
    document.getElementById('pnSoLuong').value = window.minimumReceiptQuantity || '';
    await Promise.all([loadReceiptBooks(), loadReceiptHistory()]);
}
