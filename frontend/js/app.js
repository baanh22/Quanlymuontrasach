const DOM = {
    content: document.getElementById('appContent'),
    loading: document.getElementById('loadingOverlay'),
    navItems: document.querySelectorAll('.nav-item')
};

function showLoading(show) {
    if (show) DOM.loading.classList.add('show');
    else DOM.loading.classList.remove('show');
}

function setContent(html) {
    DOM.content.innerHTML = html;
}

function setPageTitle(title) {
    document.title = title + ' | Quản lý sách';
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (m) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[m]));
}

function showModal(title, bodyHtml, footerHtml) {
    const existing = document.getElementById('appModal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'appModal';
    modal.className = 'modal-overlay show';
    modal.innerHTML = `
        <div class="modal">
            <div class="modal-header">
                <h3>${title}</h3>
                <button type="button" class="close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">${bodyHtml}</div>
            <div class="modal-footer">${footerHtml}</div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.getElementById('appModal');
    if (modal) modal.remove();
}

const routes = {
    '/sach': renderSach,
    '/nhapsach': renderNhapSach
};

function handleRoute() {
    let hash = window.location.hash.replace('#', '') || '/sach';
    if (!routes[hash]) hash = '/sach';

    DOM.navItems.forEach((n) => {
        n.classList.toggle('active', n.getAttribute('data-route') === hash);
    });

    routes[hash]();
}

DOM.navItems.forEach((item) => {
    item.addEventListener('click', () => {
        window.location.hash = item.getAttribute('data-route');
    });
});

window.addEventListener('hashchange', handleRoute);
handleRoute();
showLoading(false);
