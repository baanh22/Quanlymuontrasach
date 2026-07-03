// ===== pages/cauhinh.js =====

async function renderCauHinh() {
    setPageTitle('Cấu Hình Tham Số');
    showLoading(true);
    const res = await api.getCauHinh();
    showLoading(false);
    
    if(!res || !res.success) {
        setContent(`<div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> Lỗi tải cấu hình</div>`);
        return;
    }
    
    const r = res.data;
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Thay Đổi Quy Định Tham Số</h1>
        </div>
        
        <div class="glass-panel" style="padding: 30px; max-width: 600px;">
            <div id="msgCauHinh" class="alert alert-hidden"></div>
            <form id="frmCauHinh" onsubmit="event.preventDefault(); submitCauHinh()">
                
                <h4 style="margin-bottom: 20px; color: var(--primary-light); border-bottom: 1px solid var(--dark-border); padding-bottom: 10px; font-weight: 600;">
                    <i class="fa-solid fa-truck-moving"></i> Quy định Nhập Sách
                </h4>
                <div class="form-group">
                    <label>Số Lượng Nhập Tối Thiểu (cuốn)</label>
                    <input type="number" id="chSoLuongNhapItNhat" class="form-control" value="${r.SoLuongNhapItNhat}" required min="1">
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Chỉ cho phép nhập kho khi số lượng nhập >= mức này.</div>
                </div>
                <div class="form-group">
                    <label>Lượng Tồn Tối Đa Trước Nhập (cuốn)</label>
                    <input type="number" id="chSoLuongTonToiDaTruocNhap" class="form-control" value="${r.SoLuongTonToiDaTruocNhap}" required min="1">
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Không cho phép nhập kho nếu lượng tồn hiện tại của sách >= mức này.</div>
                </div>
                
                <h4 style="margin-bottom: 20px; margin-top: 30px; color: var(--secondary); border-bottom: 1px solid var(--dark-border); padding-bottom: 10px; font-weight: 600;">
                    <i class="fa-solid fa-shop"></i> Quy định Bán Sách
                </h4>
                <div class="form-group">
                    <label>Tiền Nợ Tối Đa (VND)</label>
                    <input type="number" id="chSoTienNoToiDa" class="form-control" value="${r.SoTienNoToiDa}" required min="0">
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Khách hàng có tổng nợ đang vượt quá mức này sẽ không được mua nợ.</div>
                </div>
                <div class="form-group">
                    <label>Lượng Tồn Tối Thiểu Sau Bán (cuốn)</label>
                    <input type="number" id="chSoLuongTonSauToiThieu" class="form-control" value="${r.SoLuongTonSauToiThieu}" required min="0">
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Không cho phép bán nếu lượng tồn còn lại trong kho < mức này.</div>
                </div>
                <div class="form-group">
                    <label>Hệ số giá bán (So với giá nhập)</label>
                    <input type="text" class="form-control" value="${r.DonGiaBanYeuCau} (Biên lợi nhuận ${Math.round((r.DonGiaBanYeuCau - 1)*100)}%)" disabled style="opacity:0.6">
                    <div style="font-size:12px;color:var(--warning);margin-top:4px"><i class="fa-solid fa-lock"></i> Tham số này cố định trong phiên bản hiện tại.</div>
                </div>
                
                <div style="margin-top: 30px; display:flex; gap:10px">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px;"><i class="fa-solid fa-save"></i> Cập Nhật Hệ Thống</button>
                    <button type="button" class="btn btn-secondary" onclick="renderCauHinh()" style="padding: 12px 24px; font-size: 14px;">Khôi Phục</button>
                </div>
            </form>
        </div>
    `);
}

async function submitCauHinh() {
    const data = {
        SoLuongNhapItNhat: parseInt(document.getElementById('chSoLuongNhapItNhat').value),
        SoLuongTonToiDaTruocNhap: parseInt(document.getElementById('chSoLuongTonToiDaTruocNhap').value),
        SoTienNoToiDa: parseFloat(document.getElementById('chSoTienNoToiDa').value),
        SoLuongTonSauToiThieu: parseInt(document.getElementById('chSoLuongTonSauToiThieu').value)
    };
    
    showLoading(true);
    const res = await api.updateCauHinh(data);
    showLoading(false);
    
    const msg = document.getElementById('msgCauHinh');
    msg.classList.remove('alert-hidden', 'success', 'error');
    
    if(res.success) {
        msg.classList.add('success');
        msg.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${res.message}`;
        setTimeout(() => msg.classList.add('alert-hidden'), 3000);
    } else {
        msg.classList.add('error');
        msg.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${res.message}`;
    }
}
