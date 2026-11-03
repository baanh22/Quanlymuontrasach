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

function showModal(title, body, footer) {
    closeModal();
    const overlay = document.createElement('div');
    overlay.id = 'appModal';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <header class="modal-header"><h2 id="modalTitle">${escapeHtml(title)}</h2><button class="icon-button" aria-label="Đóng" data-close-modal>×</button></header>
            <div class="modal-body">${body}</div>
            <footer class="modal-footer">${footer}</footer>
        </section>`;
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay || event.target.closest('[data-close-modal]')) closeModal();
    });
    document.body.appendChild(overlay);
}

function closeModal() {
    document.getElementById('appModal')?.remove();
}

const routes = {
    '/sach': renderSach,
    '/nhapsach': renderNhapSach
};

function handleRoute() {
    let route = window.location.hash.slice(1) || '/sach';
    if (!routes[route]) route = '/sach';
    DOM.navItems.forEach((item) => item.classList.toggle('active', item.dataset.route === route));
    routes[route]();
}

DOM.navItems.forEach((item) => {
    item.addEventListener('click', () => { window.location.hash = item.dataset.route; });
});
window.addEventListener('hashchange', handleRoute);
handleRoute();
