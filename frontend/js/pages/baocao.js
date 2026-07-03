// ===== pages/baocao.js =====

// ====== Báo cáo Tồn Kho ======
async function renderBaoCaoTon() {
    setPageTitle('Báo Cáo Tồn Kho');
    
    // Default current month & year
    const tkThang = window.currThang || new Date().getMonth() + 1;
    const tkNam = window.currNam || new Date().getFullYear();

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Báo Cáo Tồn Kho Sách</h1>
        </div>
        
        <div class="glass-panel" style="padding: 24px; margin-bottom: 24px">
            <div class="search-bar">
                <select id="slBaoCaoThang" class="form-control" style="width:120px">
                    ${[...Array(12).keys()].map(i => `<option value="${i+1}" ${i+1===tkThang?'selected':''}>Tháng ${i+1}</option>`).join('')}
                </select>
                <select id="slBaoCaoNam" class="form-control" style="width:120px">
                    ${[...Array(5).keys()].map(i => {
                        const y = new Date().getFullYear() - 2 + i;
                        return `<option value="${y}" ${y===tkNam?'selected':''}>Năm ${y}</option>`;
                    }).join('')}
                </select>
                <button class="btn btn-primary" onclick="loadBaoCaoTon()"><i class="fa-solid fa-filter"></i> Lập Báo Cáo</button>
                <button class="btn btn-success" onclick="exportTableToExcel('tblBaoCaoTonData', 'Bao_Cao_Ton_Kho_' + document.getElementById('slBaoCaoThang').value + '_' + document.getElementById('slBaoCaoNam').value)"><i class="fa-solid fa-file-excel"></i> Xuất Excel</button>
            </div>
        </div>
        
        <div class="glass-panel">
            <div class="table-container">
                <table id="tblBaoCaoTonData">
                    <thead>
                        <tr>
                            <th>Mã Sách</th>
                            <th>Tên Sách</th>
                            <th>Tồn Đầu Kỳ</th>
                            <th>Lượng Nhập</th>
                            <th>Lượng Xuất</th>
                            <th>Phát Sinh Ròng</th>
                            <th>Tồn Cuối Kỳ</th>
                        </tr>
                    </thead>
                    <tbody id="tblBaoCaoTon">
                        <tr><td colspan="7" style="text-align:center">Đang tải báo cáo...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    
    loadBaoCaoTon();
}

async function loadBaoCaoTon() {
    const thang = parseInt(document.getElementById('slBaoCaoThang').value);
    const nam = parseInt(document.getElementById('slBaoCaoNam').value);
    
    // Lưu lại bộ lặp để lúc F5 đổi trang quay lại ko mất
    window.currThang = thang; window.currNam = nam;
    
    showLoading(true);
    const res = await api.getBaoCaoTon(thang, nam);
    showLoading(false);
    
    const tbody = document.getElementById('tblBaoCaoTon');
    if(!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger)">Lỗi bộ xử lý máy chủ.</td></tr>`;
        return;
    }
    
    const d = res.data || [];
    if(d.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center">Không có giao dịch hay biến động tồn kho trong kỳ này.</td></tr>`;
        return;
    }
    
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    
    let html = '';
    d.forEach(r => {
        html += `
            <tr>
                <td><span class="badge" style="background:rgba(255,255,255,0.1)">${r.MaSach}</span></td>
                <td style="font-weight:500">${escapeHtml(r.TenSach)}</td>
                <td><span class="badge" style="background:var(--dark-panel)">${fmt(r.TonDau)}</span></td>
                <td style="color:var(--success)">+${fmt(r.Nhap)}</td>
                <td style="color:var(--danger)">-${fmt(r.Ban)}</td>
                <td>${r.PhatSinh > 0 ? `<span class="badge success">+${fmt(r.PhatSinh)}</span>` : `<span class="badge danger">${fmt(r.PhatSinh)}</span>`}</td>
                <td style="font-weight:700"><span class="badge warning" style="font-size:14px">${fmt(r.TonCuoi)}</span></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// ====== Báo cáo Công Nợ ======
async function renderBaoCaoCongNo() {
    setPageTitle('Báo Cáo Công Nợ Khách Hàng');
    
    const cnThang = window.currCnThang || new Date().getMonth() + 1;
    const cnNam = window.currCnNam || new Date().getFullYear();

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Báo Cáo Công Nợ</h1>
        </div>
        
        <div class="glass-panel" style="padding: 24px; margin-bottom: 24px">
            <div class="search-bar">
                <select id="slBaoCaoCnThang" class="form-control" style="width:120px">
                    ${[...Array(12).keys()].map(i => `<option value="${i+1}" ${i+1===cnThang?'selected':''}>Tháng ${i+1}</option>`).join('')}
                </select>
                <select id="slBaoCaoCnNam" class="form-control" style="width:120px">
                    ${[...Array(5).keys()].map(i => {
                        const y = new Date().getFullYear() - 2 + i;
                        return `<option value="${y}" ${y===cnNam?'selected':''}>Năm ${y}</option>`;
                    }).join('')}
                </select>
                <button class="btn btn-primary" onclick="loadBaoCaoCongNo()"><i class="fa-solid fa-filter"></i> Lập Báo Cáo</button>
                <button class="btn btn-success" onclick="exportTableToExcel('tblBaoCaoCongNoData', 'Bao_Cao_Cong_No_' + document.getElementById('slBaoCaoCnThang').value + '_' + document.getElementById('slBaoCaoCnNam').value)"><i class="fa-solid fa-file-excel"></i> Xuất Excel</button>
            </div>
        </div>
        
        <div class="glass-panel">
            <div class="table-container">
                <table id="tblBaoCaoCongNoData">
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Họ Tên Khách Hàng</th>
                            <th>Nợ Đầu Kỳ</th>
                            <th>Nợ Mới Mua (Ps)</th>
                            <th>Thanh Toán (Thu)</th>
                            <th>Dư Nợ Cuối Kỳ</th>
                        </tr>
                    </thead>
                    <tbody id="tblBaoCaoCongNo">
                        <tr><td colspan="6" style="text-align:center">Đang tải báo cáo...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    
    loadBaoCaoCongNo();
}

async function loadBaoCaoCongNo() {
    const thang = parseInt(document.getElementById('slBaoCaoCnThang').value);
    const nam = parseInt(document.getElementById('slBaoCaoCnNam').value);
    
    window.currCnThang = thang; window.currCnNam = nam;
    
    showLoading(true);
    const res = await api.getBaoCaoCongNo(thang, nam);
    showLoading(false);
    
    const tbody = document.getElementById('tblBaoCaoCongNo');
    if(!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--danger)">Lỗi máy chủ.</td></tr>`;
        return;
    }
    
    const d = res.data || [];
    if(d.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center">Không có khoản nợ hay giao dịch công nợ nào trong kỳ.</td></tr>`;
        return;
    }
    
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    let html = '';
    
    d.forEach(r => {
        html += `
            <tr>
                <td><span class="badge" style="background:rgba(255,255,255,0.1)">${r.MaKhachHang}</span></td>
                <td style="font-weight:500">${escapeHtml(r.HoTenKhachHang)}</td>
                <td><span class="badge" style="background:var(--dark-panel)">${fmt(r.NoDau)}đ</span></td>
                <td style="color:var(--danger)">+${fmt(r.PhatSinh)}đ</td>
                <td style="color:var(--success)">-${fmt(r.DaThu)}đ</td>
                <td style="font-weight:700">
                    <span class="badge ${r.NoCuoi > 0 ? 'danger' : 'success'}" style="font-size:14px">
                        ${fmt(r.NoCuoi)}đ
                    </span>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}
