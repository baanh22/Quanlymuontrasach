const DOM = {
    content: document.getElementById('appContent'),
    loading: document.getElementById('loadingOverlay'),
    navItems: document.querySelectorAll('.nav-item')
};

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

function money(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value) || 0) + ' đ';
}

function numberFormat(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value) || 0);
}

function exportTableToCsv(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const lines = [...table.rows].map((row) => [...row.cells].map((cell) => {
        const value = cell.innerText.trim().replaceAll('"', '""');
        return `"${value}"`;
    }).join(','));
    const blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${filename}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

const routes = {
    '/dashboard': renderDashboard,
    '/baocaoton': renderBaoCaoTon,
    '/baocaocongno': renderBaoCaoCongNo,
    '/cauhinh': renderCauHinh
};

function handleRoute() {
    let route = window.location.hash.slice(1) || '/dashboard';
    if (!routes[route]) route = '/dashboard';
    DOM.navItems.forEach((item) => item.classList.toggle('active', item.dataset.route === route));
    routes[route]();
}

DOM.navItems.forEach((item) => {
    item.addEventListener('click', () => { window.location.hash = item.dataset.route; });
});
window.addEventListener('hashchange', handleRoute);
handleRoute();
