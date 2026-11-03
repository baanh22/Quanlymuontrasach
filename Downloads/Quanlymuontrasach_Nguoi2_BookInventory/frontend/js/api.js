const API_BASE = '../api';

async function apiFetch(endpoint, options = {}) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            ...options,
            headers: { 'Content-Type': 'application/json', ...options.headers }
        });
        const data = await response.json();
        if (!response.ok && data.success !== false) {
            return { success: false, message: 'Yêu cầu không thành công.' };
        }
        return data;
    } catch (error) {
        console.error('API error:', error);
        return { success: false, message: 'Không thể kết nối API. Hãy kiểm tra PHP và MySQL đang chạy.' };
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
