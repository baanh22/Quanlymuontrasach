const API_BASE = '../api';

async function apiFetch(endpoint, options = {}) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            ...options,
            headers: { 'Content-Type': 'application/json', ...options.headers }
        });
        const result = await response.json();
        if (!response.ok && result.success) return { success: false, message: 'Yêu cầu không thành công.' };
        return result;
    } catch (error) {
        console.error('API error:', error);
        return { success: false, message: 'Không thể kết nối API. Hãy kiểm tra PHP và MySQL đang chạy.' };
    }
}

const api = {
    getThongKe: () => apiFetch('/thongke.php'),
    getBaoCaoTon: (month, year) => apiFetch(`/baocao.php?type=tonkho&thang=${month}&nam=${year}`),
    getBaoCaoCongNo: (month, year) => apiFetch(`/baocao.php?type=congno&thang=${month}&nam=${year}`),
    getCauHinh: () => apiFetch('/cauhinh.php'),
    updateCauHinh: (data) => apiFetch('/cauhinh.php', { method: 'PUT', body: JSON.stringify(data) })
};
