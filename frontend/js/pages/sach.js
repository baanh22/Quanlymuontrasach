// ===== pages/sach.js =====

async function renderSach() {
    setPageTitle('Danh mục Sách');
    
    setContent(`
        <div class="page-header">
            <h1 class="page-title">Danh Mục Sách</h1>
            <button class="btn btn-primary" onclick="showAddSachModal()"><i class="fa-solid fa-plus"></i> Thêm sách mới</button>
        </div>
        <div class="glass-panel" style="padding: 24px; margin-bottom: 24px">
            <div class="search-bar">
                <input type="text" id="inpSearchSach" class="form-control" placeholder="Tìm kiếm theo Tên sách, Tác giả, Thể loại..." onkeyup="if(event.key==='Enter') loadSach()">
                <button class="btn btn-primary" onclick="loadSach()"><i class="fa-solid fa-search"></i> Tìm Kiếm</button>
                <button class="btn btn-secondary" onclick="document.getElementById('inpSearchSach').value='';loadSach()">Hủy</button>
            </div>
        </div>
        <div class="glass-panel">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã Sách</th>
                            <th>Tên Sách</th>
                            <th>Thể Loại</th>
                            <th>Tác Giả</th>
                            <th>Số Lượng Tồn</th>
                            <th>Đơn Giá</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="tblSachBody">
                        <tr><td colspan="7" style="text-align:center"><div class="spinner" style="margin:20px auto"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    
    // Đính kèm dữ liệu vào window tạm để tiện cho update/delete trên table
    window.SachDataMap = {};
    loadSach();
}

async function loadSach() {
    const search = document.getElementById('inpSearchSach').value;
    const res = await api.getSach(search);
    const tbody = document.getElementById('tblSachBody');
    if (!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger)">Lỗi tải dữ liệu</td></tr>`;
        return;
    }
    
    if (res.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center">Không tìm thấy sách nào!</td></tr>`;
        return;
    }
    
    const fmt = n => new Intl.NumberFormat('vi-VN').format(n);
    let html = '';
    window.SachDataMap = {};
    
    res.data.forEach(s => {
        window.SachDataMap[s.MaSach] = s;
        html += `
            <tr>
                <td><span class="badge">${s.MaSach}</span></td>
                <td style="font-weight:500">${escapeHtml(s.TenSach)}</td>
                <td>${escapeHtml(s.TheLoai)}</td>
                <td>${escapeHtml(s.TacGia)}</td>
                <td><span class="badge ${s.SoLuongTon < 100 ? 'danger' : 'success'}">${s.SoLuongTon}</span></td>
                <td style="color:var(--secondary);font-weight:600">${fmt(s.DonGia)}đ</td>
                <td class="td-actions">
                    <button class="btn btn-sm btn-warning" onclick="showEditSachModal('${s.MaSach}')"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSach('${s.MaSach}')"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function showAddSachModal() {
    const html = `
        <form id="frmSach">
            <div id="frmSachErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Mã sách (Tự định nghĩa)</label>
                <input type="text" id="sMa" class="form-control" required placeholder="VD: S010">
            </div>
            <div class="form-group">
                <label>Tên sách</label>
                <input type="text" id="sTen" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Thể loại</label>
                <input type="text" id="sLoai" class="form-control">
            </div>
            <div class="form-group">
                <label>Tác giả</label>
                <input type="text" id="sTac" class="form-control">
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Tồn kho đầu kỳ</label>
                    <input type="number" id="sTon" class="form-control" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>Đơn giá nhập</label>
                    <input type="number" id="sGia" class="form-control" value="0" min="0">
                </div>
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-primary" onclick="submitSach('add')"><i class="fa-solid fa-save"></i> Lưu sách</button>
    `;
    showModal('Thêm Sách Mới', html, ft);
}

function showEditSachModal(maSach) {
    const s = window.SachDataMap[maSach];
    if(!s) return;
    
    const html = `
        <form id="frmSach">
            <div id="frmSachErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Mã sách</label>
                <input type="text" id="sMa" class="form-control" value="${s.MaSach}" disabled style="opacity:0.6">
            </div>
            <div class="form-group">
                <label>Tên sách</label>
                <input type="text" id="sTen" class="form-control" required value="${escapeHtml(s.TenSach)}">
            </div>
            <div class="form-group">
                <label>Thể loại</label>
                <input type="text" id="sLoai" class="form-control" value="${escapeHtml(s.TheLoai)}">
            </div>
            <div class="form-group">
                <label>Tác giả</label>
                <input type="text" id="sTac" class="form-control" value="${escapeHtml(s.TacGia)}">
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Tồn kho</label>
                    <input type="number" id="sTon" class="form-control" value="${s.SoLuongTon}" min="0">
                </div>
                <div class="form-group">
                    <label>Đơn giá nhập</label>
                    <input type="number" id="sGia" class="form-control" value="${s.DonGia}" min="0">
                </div>
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-warning" onclick="submitSach('edit', '${s.MaSach}')"><i class="fa-solid fa-pen"></i> Cập nhật</button>
    `;
    showModal('Chỉnh Sửa Sách', html, ft);
}

async function submitSach(mode, maSach = '') {
    const err = document.getElementById('frmSachErr');
    const data = {
        MaSach: document.getElementById('sMa').value,
        TenSach: document.getElementById('sTen').value,
        TheLoai: document.getElementById('sLoai').value,
        TacGia: document.getElementById('sTac').value,
        SoLuongTon: parseInt(document.getElementById('sTon').value) || 0,
        DonGia: parseFloat(document.getElementById('sGia').value) || 0
    };
    
    if(!data.MaSach || !data.TenSach) {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Vui lòng nhập đủ thông tin bắt buộc.';
        return;
    }
    
    const req = mode === 'add' ? api.addSach(data) : api.updateSach(maSach, data);
    const res = await req;
    
    if (res.success) {
        closeModal();
        loadSach();
    } else {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + res.message;
    }
}

async function deleteSach(maSach) {
    if(!confirm(`Bạn chắc chắn muốn xóa sách ${maSach}?`)) return;
    const res = await api.deleteSach(maSach);
    if(res.success) {
        loadSach();
    } else {
        alert(res.message);
    }
}
