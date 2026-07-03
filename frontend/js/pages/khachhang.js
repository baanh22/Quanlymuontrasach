// ===== pages/khachhang.js =====

async function renderKhachHang() {
    setPageTitle('Quản lý Khách hàng');
    
    setContent(`
        <div class="page-header">
            <h1 class="page-title">Khách Hàng</h1>
            <button class="btn btn-primary" onclick="showAddKhachHangModal()"><i class="fa-solid fa-user-plus"></i> Thêm Khách Hàng</button>
        </div>
        <div class="glass-panel" style="padding: 24px; margin-bottom: 24px">
            <div class="search-bar">
                <input type="text" id="inpSearchKH" class="form-control" placeholder="Tên khách, SĐT, Mã KH..." onkeyup="if(event.key==='Enter') loadKhachHang()">
                <button class="btn btn-primary" onclick="loadKhachHang()"><i class="fa-solid fa-search"></i> Tìm Kiếm</button>
                <button class="btn btn-secondary" onclick="document.getElementById('inpSearchKH').value='';loadKhachHang()">Hủy</button>
            </div>
        </div>
        <div class="glass-panel">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Họ Tên</th>
                            <th>Điện Thoại</th>
                            <th>Email</th>
                            <th>Địa Chỉ</th>
                            <th>Số Tiền Nợ</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="tblKHBody">
                        <tr><td colspan="7" style="text-align:center"><div class="spinner" style="margin:20px auto"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    
    window.KHDataMap = {};
    loadKhachHang();
}

async function loadKhachHang() {
    const search = document.getElementById('inpSearchKH').value;
    const res = await api.getKhachHang(search);
    const tbody = document.getElementById('tblKHBody');
    if (!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger)">Lỗi tải dữ liệu</td></tr>`;
        return;
    }
    
    if (res.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center">Không tìm thấy khách hàng!</td></tr>`;
        return;
    }
    
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    let html = '';
    window.KHDataMap = {};
    
    res.data.forEach(kh => {
        window.KHDataMap[kh.MaKhachHang] = kh;
        html += `
            <tr>
                <td><span class="badge w-bg">${kh.MaKhachHang}</span></td>
                <td style="font-weight:500">${escapeHtml(kh.HoTenKhachHang)}</td>
                <td>${escapeHtml(kh.DienThoai)}</td>
                <td>${escapeHtml(kh.Email)}</td>
                <td>${escapeHtml(kh.DiaChi)}</td>
                <td><span class="badge ${kh.SoTienNo > 0 ? 'warning' : 'success'}">${fmt(kh.SoTienNo)}đ</span></td>
                <td class="td-actions">
                    <button class="btn btn-sm btn-warning" onclick="showEditKhachHangModal('${kh.MaKhachHang}')"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteKhachHang('${kh.MaKhachHang}')"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function showAddKhachHangModal() {
    const html = `
        <form id="frmKH">
            <div id="frmKHErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Mã Khách Hàng</label>
                <input type="text" id="khMa" class="form-control" required placeholder="VD: KH099">
            </div>
            <div class="form-group">
                <label>Họ Tên</label>
                <input type="text" id="khTen" class="form-control" required>
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Điện Thoại</label>
                    <input type="text" id="khDT" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="khMail" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Địa Chỉ</label>
                <input type="text" id="khDiaChi" class="form-control">
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-primary" onclick="submitKhachHang('add')"><i class="fa-solid fa-user-plus"></i> Tạo</button>
    `;
    showModal('Thêm Khách Hàng', html, ft);
}

function showEditKhachHangModal(id) {
    const kh = window.KHDataMap[id];
    if(!kh) return;
    
    const html = `
        <form id="frmKH">
            <div id="frmKHErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Mã Khách Hàng</label>
                <input type="text" id="khMa" class="form-control" value="${kh.MaKhachHang}" disabled style="opacity:0.6">
            </div>
            <div class="form-group">
                <label>Họ Tên</label>
                <input type="text" id="khTen" class="form-control" required value="${escapeHtml(kh.HoTenKhachHang)}">
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Điện Thoại</label>
                    <input type="text" id="khDT" class="form-control" value="${escapeHtml(kh.DienThoai)}">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="khMail" class="form-control" value="${escapeHtml(kh.Email)}">
                </div>
            </div>
            <div class="form-group">
                <label>Địa Chỉ</label>
                <input type="text" id="khDiaChi" class="form-control" value="${escapeHtml(kh.DiaChi)}">
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-warning" onclick="submitKhachHang('edit', '${kh.MaKhachHang}')"><i class="fa-solid fa-pen"></i> Lưu</button>
    `;
    showModal('Sửa Khách Hàng', html, ft);
}

async function submitKhachHang(mode, id = '') {
    const err = document.getElementById('frmKHErr');
    const data = {
        MaKhachHang: document.getElementById('khMa').value,
        HoTenKhachHang: document.getElementById('khTen').value,
        DienThoai: document.getElementById('khDT').value,
        Email: document.getElementById('khMail').value,
        DiaChi: document.getElementById('khDiaChi').value
    };
    
    if(!data.MaKhachHang || !data.HoTenKhachHang) {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Vui lòng nhập Mã và Họ tên.';
        return;
    }
    
    const req = mode === 'add' ? api.addKhachHang(data) : api.updateKhachHang(id, data);
    const res = await req;
    
    if (res.success) {
        closeModal();
        loadKhachHang();
    } else {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + res.message;
    }
}

async function deleteKhachHang(id) {
    if(!confirm(`Xóa khách hàng ${id}? Không thể xóa nếu họ đã có lịch sử hóa đơn.`)) return;
    const res = await api.deleteKhachHang(id);
    if(res.success) {
        loadKhachHang();
    } else {
        alert(res.message);
    }
}
