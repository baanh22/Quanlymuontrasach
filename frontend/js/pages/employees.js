async function renderEmployees() {
    if (currentUser.role !== 'Admin') {
        window.location.hash = '/account';
        return;
    }
    setPageTitle('Quản lý nhân viên');
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Quản trị hệ thống</p><h1>Nhân viên</h1></div><button class="btn btn-primary" onclick="showAddEmployee()">+ Tạo tài khoản</button></header>
        <p class="heading-note">Chỉ quản trị viên được tạo tài khoản và thay đổi quyền truy cập.</p>
        <section class="panel employee-panel"><div class="table-wrap"><table>
            <thead><tr><th>Tên đăng nhập</th><th>Họ và tên</th><th>Vai trò</th><th>Thao tác</th></tr></thead>
            <tbody id="employeeRows"><tr><td colspan="4" class="empty-state">Đang tải danh sách...</td></tr></tbody>
        </table></div></section>
    `);
    await loadEmployees();
}

async function loadEmployees() {
    const body = document.getElementById('employeeRows');
    if (!body) return;
    const result = await api.getEmployees();
    if (!result?.success) {
        body.innerHTML = `<tr><td colspan="4" class="empty-state">${escapeHtml(result?.message || 'Không tải được danh sách.')}</td></tr>`;
        return;
    }
    body.innerHTML = result.data.map((employee) => `
        <tr>
            <td><span class="username-cell">${escapeHtml(employee.Username)}</span></td>
            <td>${escapeHtml(employee.FullName)}</td>
            <td><span class="role-badge ${employee.Role === 'Admin' ? 'admin' : ''}">${employee.Role === 'Admin' ? 'Quản trị viên' : 'Nhân viên'}</span></td>
            <td><div class="actions">
                <button class="btn btn-quiet btn-small" onclick="showEditEmployee('${escapeHtml(employee.Username)}')">Sửa</button>
                <button class="btn btn-danger btn-small" onclick="deleteEmployee('${escapeHtml(employee.Username)}')" ${employee.Username === currentUser.username ? 'disabled title="Không thể xóa tài khoản đang dùng"' : ''}>Xóa</button>
            </div></td>
        </tr>`).join('') || '<tr><td colspan="4" class="empty-state">Chưa có nhân viên.</td></tr>';
}

function employeeForm(employee = {}, editing = false) {
    return `
        <form id="employeeForm" onsubmit="event.preventDefault();saveEmployee('${editing ? 'edit' : 'add'}','${escapeHtml(employee.Username || '')}')">
            <div id="employeeError" class="alert error" hidden></div>
            <div class="form-group"><label for="employeeUsername">Tên đăng nhập</label><input id="employeeUsername" required ${editing ? 'disabled' : ''} value="${escapeHtml(employee.Username || '')}" placeholder="3-40 ký tự, không dấu" /></div>
            <div class="form-group"><label for="employeeName">Họ và tên</label><input id="employeeName" required value="${escapeHtml(employee.FullName || '')}" /></div>
            <div class="form-group"><label for="employeePassword">${editing ? 'Mật khẩu mới (để trống nếu giữ nguyên)' : 'Mật khẩu khởi tạo'}</label><input id="employeePassword" type="password" ${editing ? '' : 'required'} minlength="8" autocomplete="new-password" /></div>
            <div class="form-group"><label for="employeeRole">Quyền</label><select id="employeeRole"><option value="User" ${employee.Role !== 'Admin' ? 'selected' : ''}>Nhân viên</option><option value="Admin" ${employee.Role === 'Admin' ? 'selected' : ''}>Quản trị viên</option></select></div>
        </form>`;
}

function showAddEmployee() {
    showModal('Tạo tài khoản nhân viên', employeeForm(), '<button class="btn btn-quiet" onclick="closeModal()">Hủy</button><button class="btn btn-primary" onclick="document.getElementById(\'employeeForm\').requestSubmit()">Tạo tài khoản</button>');
}

async function showEditEmployee(username) {
    const result = await api.getEmployees();
    const employee = result?.data?.find((item) => item.Username === username);
    if (!employee) return;
    showModal('Cập nhật nhân viên', employeeForm(employee, true), '<button class="btn btn-quiet" onclick="closeModal()">Hủy</button><button class="btn btn-primary" onclick="document.getElementById(\'employeeForm\').requestSubmit()">Lưu thay đổi</button>');
}

async function saveEmployee(mode, username) {
    const error = document.getElementById('employeeError');
    const data = {
        Username: document.getElementById('employeeUsername').value.trim(),
        FullName: document.getElementById('employeeName').value.trim(),
        Password: document.getElementById('employeePassword').value,
        Role: document.getElementById('employeeRole').value
    };
    const result = mode === 'add'
        ? await api.addEmployee(data)
        : await api.updateEmployee(username, data);
    if (!result?.success) {
        error.hidden = false;
        error.textContent = result?.message || 'Không thể lưu tài khoản.';
        return;
    }
    closeModal();
    await loadEmployees();
}

async function deleteEmployee(username) {
    if (!confirm(`Xóa tài khoản ${username}?`)) return;
    const result = await api.deleteEmployee(username);
    if (!result?.success) {
        alert(result?.message || 'Không thể xóa tài khoản.');
        return;
    }
    await loadEmployees();
}
