const API_BASE = window.location.pathname.includes('/frontend/') ? '../api' : './api';

async function apiFetch(endpoint, options = {}) {
    const url = API_BASE + endpoint;
    const defaults = {
        headers: { 'Content-Type': 'application/json', ...options.headers }
    };
    const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };

    try {
        const res = await fetch(url, config);
        const data = await res.json();
        return data;
    } catch (err) {
        console.error('API Error:', err);
        return { success: false, message: 'Không thể kết nối đến máy chủ.' };
    }
}

const api = {
    getSach: (search = '') => apiFetch(`/sach.php${search ? '?search=' + encodeURIComponent(search) : ''}`),
    addSach: (data) => apiFetch('/sach.php', { method: 'POST', body: JSON.stringify(data) }),
    updateSach: (id, data) => apiFetch('/sach.php?id=' + encodeURIComponent(id), { method: 'PUT', body: JSON.stringify(data) }),
    deleteSach: (id) => apiFetch('/sach.php?id=' + encodeURIComponent(id), { method: 'DELETE' }),
    getNhapSach: () => apiFetch('/nhapsach.php'),
    addNhapSach: (data) => apiFetch('/nhapsach.php', { method: 'POST', body: JSON.stringify(data) }),
    getCauHinh: () => apiFetch('/cauhinh.php')
};
