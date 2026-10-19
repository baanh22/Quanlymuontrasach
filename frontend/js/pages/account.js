async function renderAccount() {
    setPageTitle('Tài khoản của tôi');
    const isAdmin = currentUser.role === 'Admin';
    setContent(`
        <header class="page-heading"><div><p class="eyebrow">Hồ sơ cá nhân</p><h1>Tài khoản của tôi</h1></div><span class="status-chip">${isAdmin ? 'ADMIN' : 'NHÂN VIÊN'}</span></header>
        <div class="account-grid">
            <section class="panel account-summary">
                <div class="avatar">${escapeHtml(currentUser.fullName.charAt(0).toUpperCase())}</div>
                <p class="eyebrow">Đang đăng nhập</p>
                <h2>${escapeHtml(currentUser.fullName)}</h2>
                <p class="muted">@${escapeHtml(currentUser.username)}</p>
                <div class="account-facts"><span>Vai trò</span><strong>${isAdmin ? 'Quản trị viên' : 'Nhân viên'}</strong></div>
                <div class="account-facts"><span>Trạng thái</span><strong class="online-dot">Phiên đang hoạt động</strong></div>
            </section>
            <section class="panel account-form-panel">
                <p class="eyebrow">Bảo mật và thông tin</p><h2>Cập nhật hồ sơ</h2>
                <div id="profileMessage" class="alert" hidden></div>
                <form id="profileForm" onsubmit="event.preventDefault();saveProfile()">
                    <div class="form-group"><label for="profileName">Họ và tên</label><input id="profileName" required value="${escapeHtml(currentUser.fullName)}" /></div>
                    <div class="form-group"><label for="currentPassword">Mật khẩu hiện tại</label><input id="currentPassword" type="password" autocomplete="current-password" placeholder="Chỉ cần nhập khi đổi mật khẩu" /></div>
                    <div class="form-group"><label for="newPassword">Mật khẩu mới</label><input id="newPassword" type="password" autocomplete="new-password" minlength="8" placeholder="Tối thiểu 8 ký tự" /></div>
                    <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
                </form>
            </section>
        </div>
    `);
}

async function saveProfile() {
    const message = document.getElementById('profileMessage');
    const result = await api.updateProfile({
        FullName: document.getElementById('profileName').value.trim(),
        CurrentPassword: document.getElementById('currentPassword').value,
        NewPassword: document.getElementById('newPassword').value
    });
    message.hidden = false;
    message.className = `alert ${result?.success ? 'success' : 'error'}`;
    message.textContent = result?.message || 'Không thể cập nhật hồ sơ.';
    if (result?.success) {
        currentUser.fullName = result.data.fullName;
        DOM.headerName.textContent = currentUser.fullName;
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
    }
}
