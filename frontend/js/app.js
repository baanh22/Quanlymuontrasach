// ===== app.js =====
// Core SPA Router và Utility Functions

const DOM = {
    content: document.getElementById('appContent'),
    loading: document.getElementById('loadingOverlay'),
    navItems: document.querySelectorAll('.nav-item'),
    btnLogout: document.getElementById('btnLogout'),
    userName: document.getElementById('userName'),
    userRole: document.getElementById('userRole'),
    userAvatar: document.getElementById('userAvatar'),
    sidebar: document.getElementById('sidebar'),
    btnToggleMenu: document.getElementById('btnToggleMenu')
};

// --- Utilities ---
function showLoading(show) { show ? DOM.loading.classList.add('show') : DOM.loading.classList.remove('show'); }
function setContent(html) { DOM.content.innerHTML = html; }
function setPageTitle(title) { document.title = title + ' | Quản Lý Nhà Sách'; }
function escapeHtml(str) { return String(str).replace(/[&<>"']/g, m => ({'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#039;'}[m])); }

// Fake jQuery html to DOM element
function htmlToElement(html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstChild;
}

// Hàm xuất Table ra Excel (CSV format)
function exportTableToExcel(tableId, filename = '') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (const row of rows) {
        let rowData = [];
        const cols = row.querySelectorAll('td, th');
        for (const col of cols) {
            // Loại bỏ khoảng trắng thừa và dấu phẩy, \n
            let text = col.innerText.trim();
            // Wrap text in quotes if it contains commas
            if (text.includes(',')) text = '"' + text + '"';
            rowData.push(text);
        }
        csv.push(rowData.join(','));
    }

    const csvFile = new Blob(["\\uFEFF" + csv.join('\\n')], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = filename ? filename + '.csv' : 'export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Global modal builder
function showModal(title, bodyHtml, footerHtml) {
    const existing = document.getElementById('appModal');
    if (existing) existing.remove();
    
    const m = document.createElement('div');
    m.className = 'modal-overlay';
    m.id = 'appModal';
    m.innerHTML = `
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">${title}</div>
                <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">${bodyHtml}</div>
            <div class="modal-footer">${footerHtml}</div>
        </div>
    `;
    document.body.appendChild(m);
    setTimeout(() => m.classList.add('show'), 10);
}
function closeModal() {
    const m = document.getElementById('appModal');
    if (!m) return;
    m.classList.remove('show');
    setTimeout(() => m.remove(), 250);
}

// --- Routing ---
const routes = {
    '/dashboard': renderDashboard,
    '/sach':      renderSach,
    '/khachhang': renderKhachHang,
    '/hoadon':    renderHoaDon,
    '/nhapsach':  renderNhapSach,
    '/phieuthu':  renderPhieuThu,
    '/baocaoton': renderBaoCaoTon,
    '/baocaocongno': renderBaoCaoCongNo,
    '/nhanvien':  renderNhanVien,
    '/cauhinh':   renderCauHinh
};

function handleRoute() {
    let hash = window.location.hash.replace('#', '') || '/dashboard';
    if (!routes[hash]) hash = '/dashboard';
    
    DOM.navItems.forEach(n => {
        n.classList.remove('active');
        if (n.getAttribute('data-route') === hash) n.classList.add('active');
    });

    // Gọi render function của trang
    routes[hash]();
    if(window.innerWidth <= 768) DOM.sidebar.classList.remove('open');
}

// --- Init & Auth ---
async function initApp() {
    showLoading(true);
    const userRes = await api.me();
    if (!userRes || !userRes.success) return; // API module sẽ tự redirect sang login

    const u = userRes.data;
    DOM.userName.textContent = u.fullName;
    DOM.userName.dataset.username = u.username;
    DOM.userRole.textContent = u.role;
    DOM.userAvatar.textContent = u.fullName.charAt(0).toUpperCase();

    // Responsive toggle
    DOM.btnToggleMenu.addEventListener('click', () => DOM.sidebar.classList.toggle('open'));
    if(window.innerWidth <= 768) DOM.btnToggleMenu.style.display = 'block';

    window.addEventListener('hashchange', handleRoute);
    handleRoute();
    
    showLoading(false);
}

DOM.btnLogout.addEventListener('click', async () => {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        await api.logout();
        window.location.href = 'login.html';
    }
});

// Chạy luôn
initApp();
