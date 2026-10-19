async function renderDashboard() {
    setPageTitle('Tổng quan');
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Vận hành nhà sách</p><h1>Tổng quan</h1></div><p class="heading-note">Bức tranh nhanh về tồn kho, khách hàng và hoạt động giao dịch.</p></header>
        <div id="dashboardError" class="alert error" hidden></div>
        <section id="metricsGrid" class="metrics-grid"><div class="panel panel-pad">Đang tải thống kê...</div></section>
        <div class="content-grid">
            <section class="panel panel-pad"><h2 class="panel-title">Sách cần chú ý</h2><div class="table-wrap"><table><thead><tr><th>Mã sách</th><th>Tên sách</th><th>Thể loại</th><th>Tồn kho</th></tr></thead><tbody id="lowStockBody"><tr><td colspan="4" class="empty-state">Đang tải...</td></tr></tbody></table></div></section>
            <section class="panel panel-pad"><h2 class="panel-title">Sách bán chạy</h2><div class="table-wrap"><table><thead><tr><th>#</th><th>Tên sách</th><th>Đã bán</th></tr></thead><tbody id="topBooksBody"><tr><td colspan="3" class="empty-state">Đang tải...</td></tr></tbody></table></div></section>
        </div>
    `);

    showLoading(true);
    const response = await api.getThongKe();
    showLoading(false);
    if (!response?.success) {
        const error = document.getElementById('dashboardError');
        error.hidden = false;
        error.textContent = response?.message || 'Không tải được thống kê.';
        document.getElementById('metricsGrid').innerHTML = '';
        document.getElementById('lowStockBody').innerHTML = '<tr><td colspan="4" class="empty-state">Không có dữ liệu.</td></tr>';
        document.getElementById('topBooksBody').innerHTML = '<tr><td colspan="3" class="empty-state">Không có dữ liệu.</td></tr>';
        return;
    }

    const data = response.data;
    const metrics = [
        ['Tổng lượng sách', numberFormat(data.tongSach), 'Cuốn đang có trong kho'],
        ['Đầu sách', numberFormat(data.soDauSach), 'Đầu sách trong danh mục'],
        ['Khách hàng', numberFormat(data.soKhachHang), 'Hồ sơ khách hàng'],
        ['Tổng công nợ', money(data.soTienNo), 'Số dư hiện tại'],
        ['Hóa đơn', numberFormat(data.soHoaDon), 'Tổng số phiếu bán'],
        ['Phiếu nhập', numberFormat(data.soPhieuNhap), 'Tổng số lần nhập kho']
    ];
    document.getElementById('metricsGrid').innerHTML = metrics.map(([label, value, note]) => `
        <article class="metric"><span class="metric-label">${escapeHtml(label)}</span><strong class="metric-value">${escapeHtml(value)}</strong><span class="metric-note">${escapeHtml(note)}</span></article>
    `).join('');

    const lowStock = data.sachSapHet || [];
    document.getElementById('lowStockBody').innerHTML = lowStock.length ? lowStock.map((book) => `
        <tr><td>${escapeHtml(book.MaSach)}</td><td>${escapeHtml(book.TenSach)}</td><td>${escapeHtml(book.TheLoai || '—')}</td><td><span class="pill ${Number(book.SoLuongTon) < 20 ? 'alert' : ''}">${numberFormat(book.SoLuongTon)}</span></td></tr>
    `).join('') : '<tr><td colspan="4" class="empty-state">Không có sách dưới ngưỡng cảnh báo.</td></tr>';

    const topBooks = data.topSach || [];
    document.getElementById('topBooksBody').innerHTML = topBooks.length ? topBooks.map((book, index) => `
        <tr><td>${String(index + 1).padStart(2, '0')}</td><td>${escapeHtml(book.TenSach)}</td><td><span class="pill good">${numberFormat(book.TongBan)} cuốn</span></td></tr>
    `).join('') : '<tr><td colspan="3" class="empty-state">Chưa có dữ liệu bán sách.</td></tr>';
}
