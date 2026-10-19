const DOM = {
    content: document.getElementById('appContent'),
    loading: document.getElementById('loadingOverlay'),
    headerName: document.getElementById('headerName'),
    headerRole: document.getElementById('headerRole'),
    employeesNav: document.getElementById('employeesNav'),
    navLinks: document.querySelectorAll('.nav-link'),
    logoutButton: document.getElementById('logoutButton')
};

let currentUser = null;

function showLoading(show) {
    DOM.loading.classList.toggle('show', show);
}

function setContent(html) {
    DOM.content.innerHTML = html;
}

function setPageTitle(title) {
    document.title = `${title} | Quản lý nhà sách`;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));
}

function showModal(title, body, footer) {
    closeModal();
    const overlay = document.createElement('div');
    overlay.id = 'appModal';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `<section class="modal" role="dialog" aria-modal="true"><header class="modal-header"><h2>${escapeHtml(title)}</h2><button class="icon-button" aria-label="Đóng" onclick="closeModal()">×</button></header><div class="modal-body">${body}</div><footer class="modal-footer">${footer}</footer></section>`;
    overlay.addEventListener('click', (event) => { if (event.target === overlay) closeModal(); });
    document.body.appendChild(overlay);
}

function closeModal() {
    document.getElementById('appModal')?.remove();
}

const routes = {
    '/account': renderAccount,
    '/employees': renderEmployees
};

function handleRoute() {
    let route = window.location.hash.slice(1) || '/account';
    if (!routes[route] || (route === '/employees' && currentUser?.role !== 'Admin')) route = '/account';
    window.location.hash = route;
    DOM.navLinks.forEach((link) => link.classList.toggle('active', link.dataset.route === route));
    routes[route]();
}

async function initApp() {
    showLoading(true);
    const response = await api.me();
    if (!response?.success) return;
    currentUser = response.data;
    DOM.headerName.textContent = currentUser.fullName;
    DOM.headerRole.textContent = currentUser.role === 'Admin' ? 'Quản trị viên' : 'Nhân viên';
    DOM.employeesNav.hidden = currentUser.role !== 'Admin';
    DOM.navLinks.forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        window.location.hash = link.dataset.route;
    }));
    DOM.logoutButton.addEventListener('click', async () => {
        if (!confirm('Bạn muốn đăng xuất khỏi hệ thống?')) return;
        await api.logout();
        window.location.href = 'login.html';
    });
    window.addEventListener('hashchange', handleRoute);
    handleRoute();
    showLoading(false);
}

initApp();
