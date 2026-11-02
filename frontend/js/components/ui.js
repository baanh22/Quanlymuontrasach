// ===== components/ui.js =====
// Atomic Design System & 3-State API Handling (Loading, Error Fallback UI, Success/Empty State)

const UIComponents = {
    // --- ATOMS ---
    Badge: (text, variant = '') => {
        const cls = variant ? `badge ${variant}` : 'badge';
        return `<span class="${cls}">${escapeHtml(text)}</span>`;
    },

    Button: ({ label, icon = '', variant = 'primary', size = '', onClick = '', type = 'button', style = '' }) => {
        const sizeCls = size ? `btn-${size}` : '';
        const iconHtml = icon ? `<i class="fa-solid ${icon}"></i> ` : '';
        const clickAttr = onClick ? `onclick="${onClick}"` : '';
        const styleAttr = style ? `style="${style}"` : '';
        return `<button type="${type}" class="btn btn-${variant} ${sizeCls}" ${clickAttr} ${styleAttr}>${iconHtml}${label}</button>`;
    },

    // --- MOLECULES ---
    StatCard: ({ title, value, icon, borderColor, iconBg, iconColor }) => `
        <div class="stat-card glass-panel" style="border-left:4px solid ${borderColor}">
            <div class="stat-icon" style="background:${iconBg};color:${iconColor}">
                <i class="fa-solid ${icon}"></i>
            </div>
            <div class="stat-info">
                <h3>${escapeHtml(value)}</h3>
                <p>${escapeHtml(title)}</p>
            </div>
        </div>
    `,

    RuleItem: ({ icon, title, description }) => `
        <div class="rule-item">
            <i class="fa-solid ${icon}"></i>
            <div>
                <b>${title}</b>
                ${description ? `<br><span style="color:var(--text-muted)">${description}</span>` : ''}
            </div>
        </div>
    `,

    // --- ORGANISMS (3-State Network Handling: Loading, Error Fallback, Empty State) ---
    LoadingRow: (colSpan = 5, message = 'Đang tải dữ liệu từ máy chủ...') => `
        <tr>
            <td colspan="${colSpan}" style="text-align:center; padding: 32px;">
                <div class="spinner" style="margin: 0 auto 10px auto;"></div>
                <div style="color:var(--text-muted); font-size:13px;">${escapeHtml(message)}</div>
            </td>
        </tr>
    `,

    EmptyStateRow: (colSpan = 5, message = 'Chưa có dữ liệu nào trong danh mục này.', icon = 'fa-inbox') => `
        <tr>
            <td colspan="${colSpan}" style="text-align:center; padding: 36px; color:var(--text-muted);">
                <i class="fa-solid ${icon}" style="font-size: 28px; margin-bottom: 10px; opacity: 0.6; display:block;"></i>
                <div>${escapeHtml(message)}</div>
            </td>
        </tr>
    `,

    ErrorStateRow: (colSpan = 5, message = 'Không thể tải dữ liệu từ máy chủ.', retryFn = '') => `
        <tr>
            <td colspan="${colSpan}" style="text-align:center; padding: 32px;">
                <div style="color:var(--danger); margin-bottom: 10px; font-weight: 500;">
                    <i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(message)}
                </div>
                ${retryFn ? `<button class="btn btn-sm btn-secondary" onclick="${retryFn}"><i class="fa-solid fa-rotate-right"></i> Thử lại</button>` : ''}
            </td>
        </tr>
    `,

    ErrorPanel: (message = 'Lỗi kết nối máy chủ', retryFn = '') => `
        <div class="glass-panel" style="padding: 32px; text-align: center; border: 1px solid rgba(239,68,68,0.3);">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 36px; color: var(--danger); margin-bottom: 12px;"></i>
            <h3 style="margin-bottom: 8px; font-size: 16px;">Đã xảy ra lỗi tải dữ liệu</h3>
            <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 14px;">${escapeHtml(message)}</p>
            ${retryFn ? `<button class="btn btn-primary" onclick="${retryFn}"><i class="fa-solid fa-rotate-right"></i> Thử lại ngay</button>` : ''}
        </div>
    `
};

window.UIComponents = UIComponents;

