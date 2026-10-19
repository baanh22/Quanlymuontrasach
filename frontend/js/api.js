const API_BASE = '../api';

async function apiFetch(endpoint, options = {}) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            credentials: 'same-origin',
            ...options,
            headers: { 'Content-Type': 'application/json', ...options.headers }
        });
        const data = await response.json();
        if (response.status === 401 && !endpoint.startsWith('/auth.php?action=login')) {
            window.location.href = 'login.html';
            return null;
        }
        return data;
    } catch (error) {
        console.error('API error:', error);
        return { success: false, message: 'Không thể kết nối máy chủ. Hãy kiểm tra PHP và MySQL.' };
    }
}

const api = {
    login: (username, password) => apiFetch('/auth.php?action=login', { method: 'POST', body: JSON.stringify({ username, password }) }),
    logout: () => apiFetch('/auth.php?action=logout', { method: 'POST', body: '{}' }),
    me: () => apiFetch('/auth.php?action=me'),
    getEmployees: () => apiFetch('/nhanvien.php'),
    addEmployee: (data) => apiFetch('/nhanvien.php', { method: 'POST', body: JSON.stringify(data) }),
    updateEmployee: (id, data) => apiFetch('/nhanvien.php?id=' + encodeURIComponent(id), { method: 'PUT', body: JSON.stringify(data) }),
    deleteEmployee: (id) => apiFetch('/nhanvien.php?id=' + encodeURIComponent(id), { method: 'DELETE' }),
    updateProfile: (data) => apiFetch('/profile.php', { method: 'PUT', body: JSON.stringify(data) })
};
