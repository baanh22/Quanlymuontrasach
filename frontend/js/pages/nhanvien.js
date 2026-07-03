// ===== pages/nhanvien.js =====

async function renderNhanVien() {
    setPageTitle('Quản lý Nhân viên');
    
    // Yêu cầu quyền admin mới được xài, nhưng front-end check lỏng lẻo 
    // vì backend cũng chặn dựa theo table, nhưng giả định backend TAIKHOAN ko phân quyền sâu.
    // Thực tế ứng dụng nhỏ nên cho hiển thị hết.
    
    setContent(`
        <div class="page-header">
            <h1 class="page-title">Quản Lý Nhân Viên (Tài Khoản Hệ Thống)</h1>
            <button class="btn btn-primary" onclick="showAddNhanVienModal()"><i class="fa-solid fa-user-plus"></i> Thêm Nhân Viên Mới</button>
        </div>
        
        <div class="glass-panel">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username (Tên Đăng Nhập)</th>
                            <th>Họ và Tên Nhân Viên</th>
                            <th>Phân Quyền (Role)</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="tblNVBody">
                        <tr><td colspan="4" style="text-align:center"><div class="spinner" style="margin:20px auto"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    
    window.NVDataMap = {};
    loadNhanVien();
}

async function loadNhanVien() {
    showLoading(true);
    const res = await api.getNhanVien();
    showLoading(false);
    
    const tbody = document.getElementById('tblNVBody');
    if (!res || !res.success) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--danger)">Lỗi lấy dữ liệu tài khoản</td></tr>`;
        return;
    }
    
    if (res.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center">Chưa có dữ liệu</td></tr>`;
        return;
    }
    
    let html = '';
    window.NVDataMap = {};
    
    res.data.forEach(nv => {
        window.NVDataMap[nv.Username] = nv;
        const isAdmin = nv.Role.toLowerCase() === 'admin';
        
        html += `
            <tr>
                <td style="font-weight:600; color:var(--primary-light)">${nv.Username}</td>
                <td>${escapeHtml(nv.FullName)}</td>
                <td>
                    <span class="badge ${isAdmin ? 'danger' : 'success'}"><i class="fa-solid ${isAdmin ? 'fa-crown' : 'fa-user-tag'}"></i> ${nv.Role}</span>
                </td>
                <td class="td-actions">
                    <button class="btn btn-sm btn-warning" onclick="showEditNhanVienModal('${nv.Username}')" title="Sửa thông tin hoặc Đổi mật khẩu"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteNhanVien('${nv.Username}')" title="Xóa tài khoản"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function showAddNhanVienModal() {
    const html = `
        <form id="frmNV">
            <div id="frmNVErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Tên Đăng Nhập (Username)</label>
                <input type="text" id="nvU" class="form-control" required placeholder="Viết liền không dấu...">
            </div>
            <div class="form-group">
                <label>Họ và Tên Nhân Viên</label>
                <input type="text" id="nvH" class="form-control" required placeholder="Trần Văn A">
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Mật khẩu khởi tạo</label>
                    <input type="password" id="nvP" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Quyền hạn</label>
                    <select id="nvR" class="form-control" required>
                        <option value="User">Nhân viên bình thường</option>
                        <option value="Admin">Quản trị viên (Admin)</option>
                    </select>
                </div>
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-primary" onclick="submitNhanVien('add')"><i class="fa-solid fa-check"></i> Tạo Mới</button>
    `;
    showModal('Thêm Tài Khoản Nhân Viên', html, ft);
}

function showEditNhanVienModal(id) {
    const nv = window.NVDataMap[id];
    if(!nv) return;
    
    const html = `
        <form id="frmNV">
            <div id="frmNVErr" class="alert error alert-hidden"></div>
            <div class="form-group">
                <label>Tên Đăng Nhập</label>
                <input type="text" class="form-control" value="${nv.Username}" disabled style="opacity:0.6">
            </div>
            <div class="form-group">
                <label>Họ và Tên Nhân Viên</label>
                <input type="text" id="nvH" class="form-control" required value="${escapeHtml(nv.FullName)}">
            </div>
            <div class="form-grid" style="margin-bottom:0">
                <div class="form-group">
                    <label>Mật khẩu (Bỏ trống nếu không đổi)</label>
                    <input type="password" id="nvP" class="form-control" placeholder="Nhập để đổi mật khẩu mới...">
                </div>
                <div class="form-group">
                    <label>Quyền hạn</label>
                    <select id="nvR" class="form-control" required>
                        <option value="User" ${nv.Role.toLowerCase()==='user' ? 'selected' : ''}>Nhân viên bình thường</option>
                        <option value="Admin" ${nv.Role.toLowerCase()==='admin' ? 'selected' : ''}>Quản trị viên (Admin)</option>
                    </select>
                </div>
            </div>
        </form>
    `;
    const ft = `
        <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button class="btn btn-warning" onclick="submitNhanVien('edit', '${nv.Username}')"><i class="fa-solid fa-save"></i> Cập Nhật</button>
    `;
    showModal('Sửa Thông Tin & Đổi Mật Khẩu', html, ft);
}

async function submitNhanVien(mode, id = '') {
    const err = document.getElementById('frmNVErr');
    
    const data = {
        FullName: document.getElementById('nvH').value,
        Password: document.getElementById('nvP').value,
        Role: document.getElementById('nvR').value
    };
    
    if(mode === 'add') {
        data.Username = document.getElementById('nvU').value;
        if(!data.Username || !data.Password) {
            err.classList.remove('alert-hidden');
            err.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Vui lòng nhập đầy đủ thông tin bắt buộc.';
            return;
        }
    }
    
    if(!data.FullName) {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Họ tên không được trống.';
        return;
    }
    
    showLoading(true);
    const req = mode === 'add' ? api.addNhanVien(data) : api.updateNhanVien(id, data);
    const res = await req;
    showLoading(false);
    
    if (res.success) {
        closeModal();
        loadNhanVien();
        
        // Nếu user tự đổi tên của chính mình thì cập nhật lại chữ hiển thị trên góc
        if (mode === 'edit' && id === document.getElementById('user-name').dataset.username) {
            document.getElementById('user-name').textContent = data.FullName;
        }
    } else {
        err.classList.remove('alert-hidden');
        err.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + res.message;
    }
}

async function deleteNhanVien(id) {
    if(!confirm(`Xác nhận xóa tài khoản truy cập của: ${id}? \nLưu ý: Bạn không thể tự xóa chính mình.`)) return;
    
    showLoading(true);
    const res = await api.deleteNhanVien(id);
    showLoading(false);
    
    if(res.success) {
        loadNhanVien();
    } else {
        alert(res.message);
    }
}
