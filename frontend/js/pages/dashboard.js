// ===== pages/dashboard.js =====

async function renderDashboard() {
    setPageTitle('Tổng quan hệ thống');
    showLoading(true);

    const res = await api.getThongKe();
    showLoading(false);

    if (!res || !res.success) {
        setContent(`<div class="alert error"><i class="fa-solid fa-circle-exclamation"></i>${res?.message || 'Lỗi tải dữ liệu'}</div>`);
        return;
    }

    const d = res.data;
    const fmt = (n) => new Intl.NumberFormat('vi-VN').format(n || 0);

    const sachSapHetRows = d.sachSapHet?.length
        ? d.sachSapHet.map(s => `
            <tr>
                <td style="font-weight:500">${s.TenSach}</td>
                <td><span class="badge">${s.TheLoai || '—'}</span></td>
                <td><span class="badge danger">${s.SoLuongTon} cuốn</span></td>
            </tr>`).join('')
        : `<tr><td colspan="3" style="text-align:center;color:var(--text-muted)">Kho hiện tại ổn định 🎉</td></tr>`;

    const topSachRows = d.topSach?.length
        ? d.topSach.map((s, i) => `
            <tr>
                <td><span class="badge info">#${i + 1}</span></td>
                <td style="font-weight:500">${s.TenSach}</td>
                <td><span class="badge success">${fmt(s.TongBan)} cuốn</span></td>
            </tr>`).join('')
        : `<tr><td colspan="3" style="text-align:center;color:var(--text-muted)">Chưa có dữ liệu bán hàng</td></tr>`;

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Tổng quan hệ thống</h1>
            <span class="topbar-date"><i class="fa-regular fa-calendar"></i>${new Date().toLocaleDateString('vi-VN', {weekday:'long',year:'numeric',month:'long',day:'numeric'})}</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card glass-panel" style="border-left:4px solid var(--primary)">
                <div class="stat-icon" style="background:rgba(79,70,229,0.12);color:var(--primary)"><i class="fa-solid fa-book-open"></i></div>
                <div class="stat-info"><h3>${fmt(d.tongSach)}</h3><p>Tổng sách trong kho</p></div>
            </div>
            <div class="stat-card glass-panel" style="border-left:4px solid var(--secondary)">
                <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:var(--secondary)"><i class="fa-solid fa-list-ol"></i></div>
                <div class="stat-info"><h3>${fmt(d.soDauSach)}</h3><p>Số lượng đầu sách</p></div>
            </div>
            <div class="stat-card glass-panel" style="border-left:4px solid var(--warning)">
                <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:var(--warning)"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info"><h3>${fmt(d.soKhachHang)}</h3><p>Tổng khách hàng</p></div>
            </div>
            <div class="stat-card glass-panel" style="border-left:4px solid var(--danger)">
                <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:var(--danger)"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                <div class="stat-info"><h3>${fmt(d.soTienNo)}đ</h3><p>Tổng tiền khách nợ</p></div>
            </div>
            <div class="stat-card glass-panel" style="border-left:4px solid #8b5cf6">
                <div class="stat-icon" style="background:rgba(139,92,246,0.12);color:#8b5cf6"><i class="fa-solid fa-receipt"></i></div>
                <div class="stat-info"><h3>${fmt(d.soHoaDon)}</h3><p>Hóa đơn bán hàng</p></div>
            </div>
            <div class="stat-card glass-panel" style="border-left:4px solid #06b6d4">
                <div class="stat-icon" style="background:rgba(6,182,212,0.12);color:#06b6d4"><i class="fa-solid fa-truck-ramp-box"></i></div>
                <div class="stat-info"><h3>${fmt(d.soPhieuNhap)}</h3><p>Phiếu nhập sách</p></div>
            </div>
        </div>

        <div class="form-grid">
            <div class="glass-panel" style="padding:24px">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning)"></i> Sách sắp hết trong kho
                </h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Tên sách</th><th>Thể loại</th><th>Tồn kho</th></tr></thead>
                        <tbody>${sachSapHetRows}</tbody>
                    </table>
                </div>
            </div>
            <div class="glass-panel" style="padding:24px">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-trophy" style="color:var(--warning)"></i> Top sách bán chạy
                </h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>#</th><th>Tên sách</th><th>Tổng bán</th></tr></thead>
                        <tbody>${topSachRows}</tbody>
                    </table>
                </div>
            </div>
        </div>
    `);
}
