async function renderSach() {
    setPageTitle('Danh mục sách');
    setContent(`
        <div class="page-header">
            <h1 class="page-title">Danh mục sách</h1>
            <button class="btn btn-primary" onclick="showAddSachModal()">+ Thêm sách mới</button>
        </div>

        <div class="glass-panel">
            <div class="search-bar">
                <input type="text" id="inpSearchSach" class="form-control" placeholder="Tìm kiếm theo tên, tác giả, thể loại..." onkeyup="if(event.key === 'Enter') loadSach()" />
                <button class="btn btn-primary" onclick="loadSach()">Tìm kiếm</button>
                <button class="btn btn-secondary" onclick="document.getElementById('inpSearchSach').value=''; loadSach()">Hủy</button>
            </div>
        </div>

        <div class="glass-panel">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã sách</th>
                            <th>Tên sách</th>
                            <th>Thể loại</th>
                            <th>Tác giả</th>
                            <th>Tồn kho</th>
                            <th>Đơn giá</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="tblSachBody">
                        <tr><td colspan="7" style="text-align:center">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);

    window.SachDataMap = {};
    loadSach();
}

async function loadSach() {
    const search = document.getElementById('inpSearchSach')?.value || '';
    const res = await api.getSach(search);
    const tbody = document.getElementById('tblSachBody');

    if (!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#fca5a5">Lỗi tải dữ liệu</td></tr>`;
        return;
    }

    if (!res.data.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center">Không có sách nào.</td></tr>`;
        return;
    }

    const fmt = (n) => new Intl.NumberFormat('vi-VN').format(n);
    let html = '';
    window.SachDataMap = {};

    res.data.forEach((s) => {
        window.SachDataMap[s.MaSach] = s;
        html += `
            <tr>
                <td><span class="badge">${escapeHtml(s.MaSach)}</span></td>
                <td>${escapeHtml(s.TenSach)}</td>
                <td>${escapeHtml(s.TheLoai || '--')}</td>
                <td>${escapeHtml(s.TacGia || '--')}</td>
                <td><span class="badge ${s.SoLuongTon < 100 ? 'danger' : 'success'}">${s.SoLuongTon}</span></td>
                <td>${fmt(s.DonGia)}đ</td>
                <td>
                    <button class="btn btn-warning" onclick="showEditSachModal('${s.MaSach}')">Sửa</button>
                    <button class="btn btn-danger" onclick="deleteSach('${s.MaSach}')">Xóa</button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function showAddSachModal() {
    const html = `
        <form id="frmSach">
            <div id="frmSachErr" class="alert error hidden"></div>
            <div class="form-group">
                <label>Mã sách</label>
                <input type="text" id="sMa" class="form-control" required placeholder="VD: S001" />
            </div>
            <div class="form-group">
                <label>Tên sách</label>
                <input type="text" id="sTen" class="form-control" required />
            </div>
            <div class="form-group">
                <label>Thể loại</label>
                <input type="text" id="sLoai" class="form-control" />
            </div>
            <div class="form-group">
                <label>Tác giả</label>
                <input type="text" id="sTac" class="form-control" />
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Tồn kho</label>
                    <input type="number" id="sTon" class="form-control" value="0" min="0" />
                </div>
                <div class="form-group">
                    <label>Đơn giá</label>
                    <input type="number" id="sGia" class="form-control" value="0" min="0" />
                </div>
            </div>
        </form>
    `;

    const footer = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-primary" onclick="submitSach('add')">Lưu</button>
    `;

    showModal('Thêm sách mới', html, footer);
}

function showEditSachModal(maSach) {
    const s = window.SachDataMap[maSach];
    if (!s) return;

    const html = `
        <form id="frmSach">
            <div id="frmSachErr" class="alert error hidden"></div>
            <div class="form-group">
                <label>Mã sách</label>
                <input type="text" id="sMa" class="form-control" value="${escapeHtml(s.MaSach)}" disabled />
            </div>
            <div class="form-group">
                <label>Tên sách</label>
                <input type="text" id="sTen" class="form-control" value="${escapeHtml(s.TenSach)}" required />
            </div>
            <div class="form-group">
                <label>Thể loại</label>
                <input type="text" id="sLoai" class="form-control" value="${escapeHtml(s.TheLoai || '')}" />
            </div>
            <div class="form-group">
                <label>Tác giả</label>
                <input type="text" id="sTac" class="form-control" value="${escapeHtml(s.TacGia || '')}" />
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Tồn kho</label>
                    <input type="number" id="sTon" class="form-control" value="${s.SoLuongTon}" min="0" />
                </div>
                <div class="form-group">
                    <label>Đơn giá</label>
                    <input type="number" id="sGia" class="form-control" value="${s.DonGia}" min="0" />
                </div>
            </div>
        </form>
    `;

    const footer = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-warning" onclick="submitSach('edit', '${s.MaSach}')">Cập nhật</button>
    `;

    showModal('Chỉnh sửa sách', html, footer);
}

async function submitSach(mode, maSach = '') {
    const err = document.getElementById('frmSachErr');
    const data = {
        MaSach: document.getElementById('sMa')?.value || maSach,
        TenSach: document.getElementById('sTen')?.value || '',
        TheLoai: document.getElementById('sLoai')?.value || '',
        TacGia: document.getElementById('sTac')?.value || '',
        SoLuongTon: parseInt(document.getElementById('sTon')?.value || 0),
        DonGia: parseFloat(document.getElementById('sGia')?.value || 0)
    };

    if (!data.MaSach || !data.TenSach) {
        err.classList.remove('hidden');
        err.innerHTML = 'Vui lòng nhập mã sách và tên sách.';
        return;
    }

    const res = mode === 'add' ? await api.addSach(data) : await api.updateSach(maSach, data);

    if (res.success) {
        closeModal();
        loadSach();
    } else {
        err.classList.remove('hidden');
        err.innerHTML = res.message;
    }
}

async function deleteSach(maSach) {
    if (!confirm(`Bạn có chắc chắn xóa sách ${maSach}?`)) return;
    const res = await api.deleteSach(maSach);
    if (res.success) {
        loadSach();
    } else {
        alert(res.message);
    }
}
