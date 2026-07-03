// ===== pages/hoadon.js =====

async function renderHoaDon() {
    setPageTitle('Lập Hóa Đơn');
    
    // Load rules, KH, Sach for form
    const [rulesRes, khRes, sachRes, hdRes] = await Promise.all([
        api.getCauHinh(),
        api.getKhachHang(),
        api.getSach(),
        api.getHoaDon()
    ]);
    
    const r = rulesRes?.data || {};
    const khs = khRes?.data || [];
    const sachs = sachRes?.data || [];
    const hds = hdRes?.data || [];
    
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    
    // Build options
    const khOpts = khs.map(k => `<option value="${k.MaKhachHang}">${k.HoTenKhachHang} (Nợ: ${fmt(k.SoTienNo)}đ)</option>`).join('');
    const sachOpts = sachs.map(s => `<option value="${s.MaSach}">${s.TenSach} (Tồn: ${s.SoLuongTon} | Giá mới: ${fmt(s.DonGia * r.DonGiaBanYeuCau)}đ)</option>`).join('');
    
    // Build HD history table
    const hdRows = hds.length === 0 
        ? `<tr><td colspan="5" style="text-align:center">Chưa có hóa đơn nào</td></tr>`
        : hds.map(h => `
            <tr>
                <td><span class="badge">HD${h.MaPhieuHoaDon}</span></td>
                <td>${h.NgayLapHoaDon}</td>
                <td style="font-weight:500">${escapeHtml(h.HoTenKhachHang)}</td>
                <td>${escapeHtml(h.DanhSachSach)}</td>
                <td><span class="badge info">${h.TongSoLuong} cuốn</span></td>
            </tr>
        `).join('');

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Lập Hóa Đơn Bán Sách</h1>
        </div>
        
        <div class="form-grid">
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-file-invoice"></i> Tạo Hóa Đơn Mới</h3>
                <div id="msgHoaDon" class="alert alert-hidden"></div>
                <form id="frmHoaDon" onsubmit="event.preventDefault(); submitHoaDon()">
                    <div class="form-group">
                        <label>Khách hàng</label>
                        <select id="hdKhachHang" class="form-control" required>
                            <option value="">-- Chọn khách hàng --</option>
                            ${khOpts}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sách bán</label>
                        <select id="hdSach" class="form-control" required>
                            <option value="">-- Chọn sách --</option>
                            ${sachOpts}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lượng bán</label>
                        <input type="number" id="hdSoLuong" class="form-control" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:10px;width:100%;justify-content:center"><i class="fa-solid fa-cart-shopping"></i> Xác nhận Hóa Đơn</button>
                </form>
            </div>
            
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-scale-balanced"></i> Quy Định Bán Hàng</h3>
                <div class="rule-card" style="background:rgba(0,0,0,0.2);border-radius:12px">
                    <div class="rule-item">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <div><b>Nợ tối đa:</b> ${fmt(r.SoTienNoToiDa)}đ<br><span style="color:var(--text-muted)">Khách nợ quá định mức sẽ không được mua thêm.</span></div>
                    </div>
                    <div class="rule-item">
                        <i class="fa-solid fa-cubes"></i>
                        <div><b>Tồn kho tối thiểu:</b> ${r.SoLuongTonSauToiThieu} cuốn<br><span style="color:var(--text-muted)">Không bán nếu tồn kho sau khi bán chạm mức này.</span></div>
                    </div>
                    <div class="rule-item">
                        <i class="fa-solid fa-tags"></i>
                        <div><b>Đơn giá bán:</b> Bằng Giá nhập x ${r.DonGiaBanYeuCau * 100}%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="glass-panel" style="padding: 24px; margin-top:24px;">
            <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;">Lịch sử hóa đơn</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Ngày lập</th>
                            <th>Khách hàng</th>
                            <th>Mặt hàng</th>
                            <th>Tổng SL</th>
                        </tr>
                    </thead>
                    <tbody>${hdRows}</tbody>
                </table>
            </div>
        </div>
    `);
}

async function submitHoaDon() {
    const data = {
        MaKhachHang: document.getElementById('hdKhachHang').value,
        MaSach: document.getElementById('hdSach').value,
        SoLuong: parseInt(document.getElementById('hdSoLuong').value)
    };
    
    showLoading(true);
    const res = await api.addHoaDon(data);
    showLoading(false);
    
    const msg = document.getElementById('msgHoaDon');
    msg.classList.remove('alert-hidden', 'success', 'error');
    
    if(res.success) {
        msg.classList.add('success');
        msg.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${res.message}`;
        setTimeout(() => renderHoaDon(), 2000);
    } else {
        msg.classList.add('error');
        msg.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${res.message}`;
    }
}
