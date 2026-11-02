// ===== pages/cauhinh.js =====

async function renderCauHinh() {
    setPageTitle('Cấu Hình Tham Số');
    showLoading(true);
    const res = await api.getCauHinh();
    showLoading(false);
    
    if(!res || !res.success) {
        setContent(UIComponents.ErrorPanel(res?.message || 'Lỗi tải cấu hình tham số', 'renderCauHinh()'));
        return;
    }
    
    const r = res.data;
    const isUserAdmin = (document.getElementById('userRole')?.textContent || '').toLowerCase() === 'admin';
    const disabledAttr = isUserAdmin ? '' : 'disabled style="opacity:0.6"';

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Thay Đổi Quy Định Tham Số (QĐ4)</h1>
        </div>
        
        <div class="glass-panel" style="padding: 30px; max-width: 650px;">
            ${!isUserAdmin ? `<div class="alert error" style="margin-bottom:20px"><i class="fa-solid fa-lock"></i> Chỉ Quản trị viên (Admin) mới có quyền cập nhật tham số hệ thống.</div>` : ''}
            <div id="msgCauHinh" class="alert alert-hidden"></div>
            <form id="frmCauHinh" onsubmit="event.preventDefault(); submitCauHinh()">
                
                <h4 style="margin-bottom: 20px; color: var(--primary-light); border-bottom: 1px solid var(--dark-border); padding-bottom: 10px; font-weight: 600;">
                    <i class="fa-solid fa-truck-moving"></i> Quy định 1 (QĐ1): Nhập Sách
                </h4>
                <div class="form-grid" style="margin-bottom:0">
                    <div class="form-group">
                        <label>Số Lượng Nhập Tối Thiểu (cuốn)</label>
                        <input type="number" id="chSoLuongNhapItNhat" class="form-control" value="${r.SoLuongNhapItNhat}" required min="1" ${disabledAttr}>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Chỉ nhập khi số lượng nhập >= mức này.</div>
                    </div>
                    <div class="form-group">
                        <label>Lượng Tồn Tối Đa Trước Nhập (cuốn)</label>
                        <input type="number" id="chSoLuongTonToiDaTruocNhap" class="form-control" value="${r.SoLuongTonToiDaTruocNhap}" required min="1" ${disabledAttr}>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Chỉ nhập các sách có lượng tồn < mức này.</div>
                    </div>
                </div>
                
                <h4 style="margin-bottom: 20px; margin-top: 24px; color: var(--secondary); border-bottom: 1px solid var(--dark-border); padding-bottom: 10px; font-weight: 600;">
                    <i class="fa-solid fa-shop"></i> Quy định 2 (QĐ2): Bán Sách
                </h4>
                <div class="form-grid" style="margin-bottom:0">
                    <div class="form-group">
                        <label>Tiền Nợ Tối Đa Cho Phép (VND)</label>
                        <input type="number" id="chSoTienNoToiDa" class="form-control" value="${r.SoTienNoToiDa}" required min="0" ${disabledAttr}>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Khách nợ vượt mức này không được mua thêm.</div>
                    </div>
                    <div class="form-group">
                        <label>Lượng Tồn Tối Thiểu Sau Bán (cuốn)</label>
                        <input type="number" id="chSoLuongTonSauToiThieu" class="form-control" value="${r.SoLuongTonSauToiThieu}" required min="0" ${disabledAttr}>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Tồn kho sau khi bán phải >= mức này.</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tỷ lệ Đơn Giá Bán / Đơn Giá Nhập (VD: 1.05 = 105%)</label>
                    <input type="number" step="0.01" min="0.01" id="chDonGiaBanYeuCau" class="form-control" value="${r.DonGiaBanYeuCau}" required ${disabledAttr}>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Hiện tại: Bán bằng ${Math.round(r.DonGiaBanYeuCau * 100)}% giá nhập (Biên lợi nhuận ${Math.round((r.DonGiaBanYeuCau - 1)*100)}%).</div>
                </div>

                <h4 style="margin-bottom: 20px; margin-top: 24px; color: var(--warning); border-bottom: 1px solid var(--dark-border); padding-bottom: 10px; font-weight: 600;">
                    <i class="fa-solid fa-hand-holding-dollar"></i> Quy định 3 (QĐ3): Thu Tiền Công Nợ
                </h4>
                <div class="form-group">
                    <label>Kiểm soát số tiền thu không vượt quá nợ</label>
                    <select id="chQuyDinh" class="form-control" ${disabledAttr}>
                        <option value="1" ${parseInt(r.QuyDinh) === 1 ? 'selected' : ''}>Bật (Số tiền thu <= Số tiền khách đang nợ)</option>
                        <option value="0" ${parseInt(r.QuyDinh) === 0 ? 'selected' : ''}>Tắt (Cho phép thu vượt số tiền nợ)</option>
                    </select>
                </div>
                
                ${isUserAdmin ? `
                <div style="margin-top: 30px; display:flex; gap:10px">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px;"><i class="fa-solid fa-save"></i> Cập Nhật Hệ Thống</button>
                    <button type="button" class="btn btn-secondary" onclick="renderCauHinh()" style="padding: 12px 24px; font-size: 14px;">Khôi Phục</button>
                </div>` : ''}
            </form>
        </div>
    `);
}

async function submitCauHinh() {
    const data = {
        SoLuongNhapItNhat: parseInt(document.getElementById('chSoLuongNhapItNhat').value),
        SoLuongTonToiDaTruocNhap: parseInt(document.getElementById('chSoLuongTonToiDaTruocNhap').value),
        SoTienNoToiDa: parseFloat(document.getElementById('chSoTienNoToiDa').value),
        SoLuongTonSauToiThieu: parseInt(document.getElementById('chSoLuongTonSauToiThieu').value),
        DonGiaBanYeuCau: parseFloat(document.getElementById('chDonGiaBanYeuCau').value),
        QuyDinh: parseInt(document.getElementById('chQuyDinh').value)
    };
    
    showLoading(true);
    const res = await api.updateCauHinh(data);
    showLoading(false);
    
    const msg = document.getElementById('msgCauHinh');
    msg.classList.remove('alert-hidden', 'success', 'error');
    
    if(res.success) {
        msg.classList.add('success');
        msg.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${res.message}`;
        setTimeout(() => renderCauHinh(), 1500);
    } else {
        msg.classList.add('error');
        msg.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${res.message}`;
    }
}
