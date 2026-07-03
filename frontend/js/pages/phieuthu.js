// ===== pages/phieuthu.js =====

async function renderPhieuThu() {
    setPageTitle('Lập Phiếu Thu Tiền');
    
    // Load KhachHang (để biết ai đang nợ) & Lịch sử phiếu thu
    const [khRes, ptRes] = await Promise.all([
        api.getKhachHang(),
        api.getPhieuThu()
    ]);
    
    const khs = khRes?.data || [];
    const pts = ptRes?.data || [];
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    
    // Build danh sách khách hàng đang có nợ
    let khOpts = '';
    const nkhNo = khs.filter(k => k.SoTienNo > 0);
    if (nkhNo.length === 0) {
        khOpts = `<option value="">-- Không có khách hàng nào đang nợ --</option>`;
    } else {
        khOpts = `<option value="">-- Chọn khách hàng --</option>` + 
                 nkhNo.map(k => `<option value="${k.MaKhachHang}" data-no="${k.SoTienNo}">${k.HoTenKhachHang} (Nợ: ${fmt(k.SoTienNo)}đ)</option>`).join('');
    }
    
    const ptRows = pts.length === 0 
        ? `<tr><td colspan="4" style="text-align:center">Chưa có phiếu thu nào</td></tr>`
        : pts.map(p => `
            <tr>
                <td><span class="badge">PT${p.MaPhieuThu}</span></td>
                <td>${p.NgayThuTien}</td>
                <td style="font-weight:500">${escapeHtml(p.HoTenKhachHang)}</td>
                <td style="color:var(--success);font-weight:600">+${fmt(p.SoTienThu)}đ</td>
            </tr>
        `).join('');

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Thanh Toán Nợ Khách Hàng</h1>
        </div>
        
        <div class="form-grid">
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-hand-holding-dollar"></i> Lập Phiếu Thu Mới</h3>
                <div id="msgPhieuThu" class="alert alert-hidden"></div>
                <form id="frmPhieuThu" onsubmit="event.preventDefault(); submitPhieuThu()">
                    <div class="form-group">
                        <label>Khách hàng trả nợ</label>
                        <select id="ptKhachHang" class="form-control" required onchange="capNhatMaxThu()">
                            ${khOpts}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số tiền thu (VND)</label>
                        <input type="number" id="ptSoTien" class="form-control" min="1" required placeholder="Nhập số tiền...">
                        <div id="ptGoiY" style="font-size:12px;color:var(--text-muted);margin-top:6px;display:none">
                            <a href="#" onclick="event.preventDefault();document.getElementById('ptSoTien').value=document.getElementById('ptKhachHang').options[document.getElementById('ptKhachHang').selectedIndex].dataset.no" style="color:var(--primary-light)">Thu tất cả nợ</a>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:10px;width:100%;justify-content:center"><i class="fa-solid fa-check"></i> Xác Nhận Thu Tiền</button>
                </form>
            </div>
            
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;"><i class="fa-solid fa-scale-balanced"></i> Quy Định Thu Tiền</h3>
                <div class="rule-card" style="background:rgba(0,0,0,0.2);border-radius:12px">
                    <div class="rule-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div><b>Không thu vượt nợ:</b> Số tiền thu không được quá số tiền khách hàng đang nợ tại thời điểm thu.</div>
                    </div>
                    <div class="rule-item">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <div><b>Giảm nợ tức thì:</b> Sau khi thu thành công, tổng nợ của khách hàng tự động khấu trừ ngay lập tức.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="glass-panel" style="padding: 24px; margin-top:24px;">
            <h3 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;">Lịch sử thu tiền</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã PT</th>
                            <th>Ngày thu</th>
                            <th>Khách hàng</th>
                            <th>Số tiền thu</th>
                        </tr>
                    </thead>
                    <tbody>${ptRows}</tbody>
                </table>
            </div>
        </div>
    `);
    
    // Gắn handler (dùng global tạm để html snippet gọi tới)
    window.capNhatMaxThu = () => {
        const sel = document.getElementById('ptKhachHang');
        const gi = document.getElementById('ptGoiY');
        const inp = document.getElementById('ptSoTien');
        if(sel.selectedIndex > 0 && sel.options[sel.selectedIndex].dataset.no) {
            gi.style.display = 'block';
            inp.max = sel.options[sel.selectedIndex].dataset.no;
        } else {
            gi.style.display = 'none';
            inp.removeAttribute('max');
        }
    }
}

async function submitPhieuThu() {
    const data = {
        MaKhachHang: document.getElementById('ptKhachHang').value,
        SoTienThu: parseFloat(document.getElementById('ptSoTien').value)
    };
    
    if(!data.MaKhachHang) return;
    
    showLoading(true);
    const res = await api.addPhieuThu(data);
    showLoading(false);
    
    const msg = document.getElementById('msgPhieuThu');
    msg.classList.remove('alert-hidden', 'success', 'error');
    
    if(res.success) {
        msg.classList.add('success');
        msg.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${res.message}`;
        setTimeout(() => renderPhieuThu(), 1500); // Tự động load lại trang
    } else {
        msg.classList.add('error');
        msg.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${res.message}`;
    }
}
