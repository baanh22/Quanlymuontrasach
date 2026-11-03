async function renderCauHinh() {
    setPageTitle('Cấu hình');
    setContent('<section class="panel panel-pad"><p class="eyebrow">Quy tắc nghiệp vụ</p><h1>Cấu hình hệ thống</h1><p class="heading-note">Đang tải các giới hạn nhập, bán và công nợ...</p></section>');
    showLoading(true);
    const response = await api.getCauHinh();
    showLoading(false);
    if (!response?.success) {
        setContent(`<div class="alert error">${escapeHtml(response?.message || 'Không tải được cấu hình.')}</div>`);
        return;
    }
    const settings = response.data;
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Quy tắc nghiệp vụ</p><h1>Cấu hình hệ thống</h1></div><p class="heading-note">Các thay đổi này ảnh hưởng trực tiếp đến thao tác nhập kho, bán sách và lập hóa đơn.</p></header>
        <section class="panel panel-pad">
            <div id="settingsMessage" class="alert" hidden></div>
            <form id="settingsForm" onsubmit="event.preventDefault();saveCauHinh()">
                <h2 class="panel-title">Quy định nhập sách</h2>
                <div class="settings-grid">
                    <div class="form-group"><label for="minImport">Số lượng nhập tối thiểu (cuốn)</label><input id="minImport" type="number" min="1" step="1" required value="${Number(settings.SoLuongNhapItNhat)}"><span class="form-help">Số lượng nhập mỗi lần phải đạt tối thiểu mức này.</span></div>
                    <div class="form-group"><label for="maxStockBeforeImport">Tồn tối đa trước khi nhập (cuốn)</label><input id="maxStockBeforeImport" type="number" min="1" step="1" required value="${Number(settings.SoLuongTonToiDaTruocNhap)}"><span class="form-help">Chỉ cho nhập khi tồn hiện tại thấp hơn ngưỡng.</span></div>
                </div>
                <h2 class="panel-title" style="margin-top:18px">Quy định bán sách</h2>
                <div class="settings-grid">
                    <div class="form-group"><label for="maxDebt">Hạn mức công nợ (VND)</label><input id="maxDebt" type="number" min="1" step="1000" required value="${Number(settings.SoTienNoToiDa)}"><span class="form-help">Ngăn lập hóa đơn bán chịu khi khách đã vượt mức nợ.</span></div>
                    <div class="form-group"><label for="minStockAfterSale">Tồn tối thiểu sau bán (cuốn)</label><input id="minStockAfterSale" type="number" min="0" step="1" required value="${Number(settings.SoLuongTonSauToiThieu)}"><span class="form-help">Tồn sau giao dịch không được thấp hơn mức này.</span></div>
                </div>
                <div class="form-group"><label>Hệ số giá bán</label><input value="${Number(settings.DonGiaBanYeuCau)} × giá nhập" disabled><span class="form-help">Hệ số đang được giữ cố định theo cấu hình database.</span></div>
                <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
            </form>
        </section>
    `);
}

async function saveCauHinh() {
    const message = document.getElementById('settingsMessage');
    const payload = {
        SoLuongNhapItNhat: Number(document.getElementById('minImport').value),
        SoLuongTonToiDaTruocNhap: Number(document.getElementById('maxStockBeforeImport').value),
        SoTienNoToiDa: Number(document.getElementById('maxDebt').value),
        SoLuongTonSauToiThieu: Number(document.getElementById('minStockAfterSale').value)
    };
    if (!Object.values(payload).every(Number.isFinite) || payload.SoLuongNhapItNhat < 1 || payload.SoLuongTonToiDaTruocNhap < 1 || payload.SoTienNoToiDa <= 0 || payload.SoLuongTonSauToiThieu < 0) {
        message.hidden = false;
        message.className = 'alert error';
        message.textContent = 'Hãy kiểm tra các giới hạn đã nhập.';
        return;
    }
    showLoading(true);
    const response = await api.updateCauHinh(payload);
    showLoading(false);
    message.hidden = false;
    message.className = `alert ${response.success ? 'success' : 'error'}`;
    message.textContent = response.message;
}
