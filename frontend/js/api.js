// ===== api.js =====
// HTTP client wrapper – tự động gắn credentials, parse JSON, xử lý lỗi auth

const API_BASE = '/Quanlymuontrasach/api';

async function apiFetch(endpoint, options = {}) {
    const url = API_BASE + endpoint;
    const defaults = {
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', ...options.headers }
    };
    const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };

    try {
        const res = await fetch(url, config);
        const data = await res.json();

        if (res.status === 401) {
            // Session expired – redirect login
            window.location.href = '/Quanlymuontrasach/frontend/login.html';
            return null;
        }
        return data;
    } catch (err) {
        console.error('API Error:', err);
        return { success: false, message: 'Không thể kết nối đến máy chủ. Kiểm tra XAMPP đang chạy.' };
    }
}

const api = {
    // Auth
    login: (username, password) => apiFetch('/auth.php?action=login', { method: 'POST', body: JSON.stringify({ username, password }) }),
    logout: () => apiFetch('/auth.php?action=logout', { method: 'POST' }),
    me:     () => apiFetch('/auth.php?action=me'),

    // Sách
    getSach:    (search = '') => apiFetch(`/sach.php${search ? '?search=' + encodeURIComponent(search) : ''}`),
    addSach:    (data)        => apiFetch('/sach.php', { method: 'POST', body: JSON.stringify(data) }),
    updateSach: (id, data)    => apiFetch('/sach.php?id=' + encodeURIComponent(id), { method: 'PUT', body: JSON.stringify(data) }),
    deleteSach: (id)          => apiFetch('/sach.php?id=' + encodeURIComponent(id), { method: 'DELETE' }),

    // Khách hàng
    getKhachHang:    (search = '') => apiFetch(`/khachhang.php${search ? '?search=' + encodeURIComponent(search) : ''}`),
    addKhachHang:    (data)        => apiFetch('/khachhang.php', { method: 'POST', body: JSON.stringify(data) }),
    updateKhachHang: (id, data)    => apiFetch('/khachhang.php?id=' + encodeURIComponent(id), { method: 'PUT', body: JSON.stringify(data) }),
    deleteKhachHang: (id)          => apiFetch('/khachhang.php?id=' + encodeURIComponent(id), { method: 'DELETE' }),

    // Hóa đơn
    getHoaDon: () => apiFetch('/hoadon.php'),
    addHoaDon: (data) => apiFetch('/hoadon.php', { method: 'POST', body: JSON.stringify(data) }),

    // Nhập sách
    getNhapSach: () => apiFetch('/nhapsach.php'),
    addNhapSach: (data) => apiFetch('/nhapsach.php', { method: 'POST', body: JSON.stringify(data) }),

    // Phiếu thu
    getPhieuThu: () => apiFetch('/phieuthu.php'),
    addPhieuThu: (data) => apiFetch('/phieuthu.php', { method: 'POST', body: JSON.stringify(data) }),

    // Báo cáo
    getBaoCaoTon: (thang, nam) => apiFetch(`/baocao.php?type=tonkho&thang=${thang}&nam=${nam}`),
    getBaoCaoCongNo: (thang, nam) => apiFetch(`/baocao.php?type=congno&thang=${thang}&nam=${nam}`),

    // Nhân viên
    getNhanVien:    ()     => apiFetch('/nhanvien.php'),
    addNhanVien:    (data) => apiFetch('/nhanvien.php', { method: 'POST', body: JSON.stringify(data) }),
    updateNhanVien: (id, data) => apiFetch(`/nhanvien.php?id=${id}`, { method: 'PUT', body: JSON.stringify(data) }),
    deleteNhanVien: (id)   => apiFetch(`/nhanvien.php?id=${id}`, { method: 'DELETE' }),

    // Cấu hình
    getCauHinh:    () => apiFetch('/cauhinh.php'),
    updateCauHinh: (data) => apiFetch('/cauhinh.php', { method: 'PUT', body: JSON.stringify(data) }),

    // Thống kê
    getThongKe: () => apiFetch('/thongke.php'),
};
