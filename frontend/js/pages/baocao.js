function reportPeriodOptions(monthId, yearId, month, year) {
    const months = Array.from({ length: 12 }, (_, index) => index + 1)
        .map((value) => `<option value="${value}" ${value === month ? 'selected' : ''}>Tháng ${value}</option>`).join('');
    const years = Array.from({ length: 7 }, (_, index) => new Date().getFullYear() - 3 + index)
        .map((value) => `<option value="${value}" ${value === year ? 'selected' : ''}>${value}</option>`).join('');
    return `<select id="${monthId}" class="filter-select" aria-label="Chọn tháng">${months}</select><select id="${yearId}" class="filter-select" aria-label="Chọn năm">${years}</select>`;
}

async function renderBaoCaoTon() {
    setPageTitle('Báo cáo tồn kho');
    const month = window.inventoryMonth || new Date().getMonth() + 1;
    const year = window.inventoryYear || new Date().getFullYear();
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Báo cáo định kỳ</p><h1>Tồn kho</h1></div><p class="heading-note">Đối chiếu tồn đầu kỳ, số nhập, số bán và tồn cuối kỳ.</p></header>
        <div class="toolbar">${reportPeriodOptions('inventoryMonth', 'inventoryYear', month, year)}<button class="btn btn-primary" onclick="loadBaoCaoTon()">Lập báo cáo</button><button class="btn btn-quiet" onclick="exportTableToCsv('inventoryReportTable','Bao_cao_ton_kho_${month}_${year}')">Tải CSV</button></div>
        <section class="panel"><div class="table-wrap"><table id="inventoryReportTable"><thead><tr><th>Mã sách</th><th>Tên sách</th><th>Tồn đầu</th><th>Nhập</th><th>Đã bán</th><th>Phát sinh</th><th>Tồn cuối</th></tr></thead><tbody id="inventoryReportBody"><tr><td colspan="7" class="empty-state">Đang tải báo cáo...</td></tr></tbody></table></div></section>
    `);
    await loadBaoCaoTon();
}

async function loadBaoCaoTon() {
    const body = document.getElementById('inventoryReportBody');
    if (!body) return;
    const month = Number(document.getElementById('inventoryMonth').value);
    const year = Number(document.getElementById('inventoryYear').value);
    window.inventoryMonth = month;
    window.inventoryYear = year;
    showLoading(true);
    const response = await api.getBaoCaoTon(month, year);
    showLoading(false);
    if (!response?.success) {
        body.innerHTML = `<tr><td colspan="7" class="empty-state">${escapeHtml(response?.message || 'Không tạo được báo cáo.')}</td></tr>`;
        return;
    }
    if (!response.data.length) {
        body.innerHTML = '<tr><td colspan="7" class="empty-state">Không có biến động tồn kho trong kỳ.</td></tr>';
        return;
    }
    body.innerHTML = response.data.map((row) => `
        <tr><td>${escapeHtml(row.MaSach)}</td><td>${escapeHtml(row.TenSach)}</td><td>${numberFormat(row.TonDau)}</td><td>+${numberFormat(row.Nhap)}</td><td>-${numberFormat(row.Ban)}</td><td><span class="pill ${Number(row.PhatSinh) < 0 ? 'alert' : 'good'}">${Number(row.PhatSinh) > 0 ? '+' : ''}${numberFormat(row.PhatSinh)}</span></td><td><strong>${numberFormat(row.TonCuoi)}</strong></td></tr>
    `).join('');
}

async function renderBaoCaoCongNo() {
    setPageTitle('Báo cáo công nợ');
    const month = window.debtMonth || new Date().getMonth() + 1;
    const year = window.debtYear || new Date().getFullYear();
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Báo cáo định kỳ</p><h1>Công nợ</h1></div><p class="heading-note">Theo dõi số dư đầu kỳ, hóa đơn phát sinh, khoản đã thu và dư nợ cuối kỳ.</p></header>
        <div class="toolbar">${reportPeriodOptions('debtMonth', 'debtYear', month, year)}<button class="btn btn-primary" onclick="loadBaoCaoCongNo()">Lập báo cáo</button><button class="btn btn-quiet" onclick="exportTableToCsv('debtReportTable','Bao_cao_cong_no_${month}_${year}')">Tải CSV</button></div>
        <section class="panel"><div class="table-wrap"><table id="debtReportTable"><thead><tr><th>Mã khách</th><th>Khách hàng</th><th>Nợ đầu kỳ</th><th>Hóa đơn</th><th>Đã thu</th><th>Nợ cuối kỳ</th></tr></thead><tbody id="debtReportBody"><tr><td colspan="6" class="empty-state">Đang tải báo cáo...</td></tr></tbody></table></div></section>
    `);
    await loadBaoCaoCongNo();
}

async function loadBaoCaoCongNo() {
    const body = document.getElementById('debtReportBody');
    if (!body) return;
    const month = Number(document.getElementById('debtMonth').value);
    const year = Number(document.getElementById('debtYear').value);
    window.debtMonth = month;
    window.debtYear = year;
    showLoading(true);
    const response = await api.getBaoCaoCongNo(month, year);
    showLoading(false);
    if (!response?.success) {
        body.innerHTML = `<tr><td colspan="6" class="empty-state">${escapeHtml(response?.message || 'Không tạo được báo cáo.')}</td></tr>`;
        return;
    }
    if (!response.data.length) {
        body.innerHTML = '<tr><td colspan="6" class="empty-state">Không có khoản nợ hay giao dịch công nợ trong kỳ.</td></tr>';
        return;
    }
    body.innerHTML = response.data.map((row) => `
        <tr><td>${escapeHtml(row.MaKhachHang)}</td><td>${escapeHtml(row.HoTenKhachHang)}</td><td>${money(row.NoDau)}</td><td>+${money(row.PhatSinh)}</td><td>-${money(row.DaThu)}</td><td><span class="pill ${Number(row.NoCuoi) > 0 ? 'alert' : 'good'}">${money(row.NoCuoi)}</span></td></tr>
    `).join('');
}
