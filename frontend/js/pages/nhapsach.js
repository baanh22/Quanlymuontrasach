// ===== pages/nhapsach.js =====

async function renderNhapSach() {
    setPageTitle('Nhập Sách');
    
    // Load rules, Sach, Receipts history
    const [rulesRes, sachRes, pnRes] = await Promise.all([
        api.getCauHinh(),
        api.getSach(),
        api.getNhapSach()
    ]);
    
    const r = rulesRes?.data || {};
    const sachs = sachRes?.data || [];
    const pns = pnRes?.data || [];
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    
    const sachOpts = sachs.map(s => `<option value="${s.MaSach}">${s.TenSach} (Tồn: ${s.SoLuongTon})</option>`).join('');
    
    const pnRows = pns.length === 0 
        ? `<tr><td colspan="5" style="text-align:center">Chưa có phiếu nhập nào</td></tr>`
        : pns.map(p => `
            <tr>
                <td><span class="badge">PN${p.MaPhieuNhap}</span></td>
                <td>${p.NgayNhap}</td>
                <td style="font-weight:500">${escapeHtml(p.DanhSachSach)}</td>
                <td><span class="badge info">${p.TongSoLuong} cuốn</span></td>
                <td style="color:var(--danger);font-weight:600">-${fmt(p.TongTien)}đ</td>
            </tr>
        `).join('');

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Nhập Sách Mới</h1>
        </div>
        
        <div class="form-grid">
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-truck-fast"></i> Tạo Phiếu Nhập</h3>
                <div id="msgNhapSach" class="alert alert-hidden"></div>
                <form id="frmNhapSach" onsubmit="event.preventDefault(); submitNhapSach()">
                    <div class="form-group">
                        <label>Chọn sách</label>
                        <select id="pnSach" class="form-control" required>
                            <option value="">-- Chọn sách --</option>
                            ${sachOpts}
                        </select>
                    </div>
                    <div class="form-grid" style="margin-bottom:0">
                        <div class="form-group">
                            <label>Số lượng nhập</label>
                            <input type="number" id="pnSoLuong" class="form-control" min="1" value="${r.SoLuongNhapItNhat || 1}" required>
                        </div>
                        <div class="form-group">
                            <label>Đơn giá nhập mới</label>
                            <input type="number" id="pnDonGia" class="form-control" min="1" required placeholder="VD: 50000">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:10px;width:100%;justify-content:center"><i class="fa-solid fa-save"></i> Lưu Phiếu Nhập</button>
                </form>
            </div>
            
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-scale-balanced"></i> Quy Định Nhập Sách</h3>
                <div class="rule-card" style="background:rgba(0,0,0,0.2);border-radius:12px">
                    <div class="rule-item">
                        <i class="fa-solid fa-box-open"></i>
                        <div><b>Số lượng nhập tối thiểu:</b> ${r.SoLuongNhapItNhat} cuốn<br><span style="color:var(--text-muted)">Không được nhập lắt nhắt ít hơn quy định.</span></div>
                    </div>
                    <div class="rule-item">
                        <i class="fa-solid fa-ban"></i>
                        <div><b>Tồn tối đa trước nhập:</b> ${r.SoLuongTonToiDaTruocNhap} cuốn<br><span style="color:var(--text-muted)">Kho còn nhiều hơn mức này thì không cho nhập thêm.</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="glass-panel" style="padding: 24px; margin-top:24px;">
            <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;">Lịch sử nhập kho</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã PN</th>
                            <th>Ngày nhập</th>
                            <th>Mặt hàng</th>
                            <th>Tổng SL</th>
                            <th>Tổng Tiền Nhập</th>
                        </tr>
                    </thead>
                    <tbody>${pnRows}</tbody>
                </table>
            </div>
        </div>
    `);
}

async function submitNhapSach() {
    const data = {
        MaSach: document.getElementById('pnSach').value,
        SoLuong: parseInt(document.getElementById('pnSoLuong').value),
        DonGia: parseFloat(document.getElementById('pnDonGia').value)
    };
    
    showLoading(true);
    const res = await api.addNhapSach(data);
    showLoading(false);
    
    const msg = document.getElementById('msgNhapSach');
    msg.classList.remove('alert-hidden', 'success', 'error');
    
    if(res.success) {
        msg.classList.add('success');
        msg.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${res.message}`;
        setTimeout(() => renderNhapSach(), 2000);
    } else {
        msg.classList.add('error');
        msg.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${res.message}`;
    }
}
