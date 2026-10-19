async function renderNhapSach() {
    setPageTitle('Nhập sách');

    const [rulesRes, sachRes, pnRes] = await Promise.all([
        api.getCauHinh(),
        api.getSach(),
        api.getNhapSach()
    ]);

    const rules = rulesRes?.data || {};
    const sachs = sachRes?.data || [];
    const pns = pnRes?.data || [];
    const fmt = (n) => new Intl.NumberFormat('vi-VN').format(n);

    const sachOptions = sachs.map((s) => `
        <option value="${escapeHtml(s.MaSach)}">${escapeHtml(s.TenSach)} (Tồn: ${s.SoLuongTon})</option>
    `).join('');

    const rows = pns.length
        ? pns.map((p) => `
            <tr>
                <td><span class="badge">PN${p.MaPhieuNhap}</span></td>
                <td>${p.NgayNhap}</td>
                <td>${escapeHtml(p.DanhSachSach || '--')}</td>
                <td><span class="badge info">${p.TongSoLuong || 0}</span></td>
                <td>${fmt(p.TongTien || 0)}đ</td>
            </tr>
          `).join('')
        : '<tr><td colspan="5" style="text-align:center">Chưa có phiếu nhập nào.</td></tr>';

    setContent(`
        <div class="page-header">
            <h1 class="page-title">Nhập sách</h1>
        </div>

        <div class="form-grid">
            <div class="glass-panel">
                <h3>Tạo phiếu nhập</h3>
                <div id="msgNhapSach" class="alert hidden"></div>
                <form id="frmNhapSach" onsubmit="event.preventDefault(); submitNhapSach();">
                    <div class="form-group">
                        <label>Chọn sách</label>
                        <select id="pnSach" class="form-control" required>
                            <option value="">-- Chọn sách --</option>
                            ${sachOptions}
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Số lượng</label>
                            <input type="number" id="pnSoLuong" class="form-control" min="1" value="${rules.SoLuongNhapItNhat || 1}" required />
                        </div>
                        <div class="form-group">
                            <label>Đơn giá nhập</label>
                            <input type="number" id="pnDonGia" class="form-control" min="1000" value="0" required />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Lưu phiếu nhập</button>
                </form>
            </div>

            <div class="glass-panel">
                <h3>Quy định nhập sách</h3>
                <div class="form-group">
                    <strong>Số lượng nhập tối thiểu:</strong> ${rules.SoLuongNhapItNhat || 0} cuốn
                </div>
                <div class="form-group">
                    <strong>Tồn kho tối đa trước khi nhập:</strong> ${rules.SoLuongTonToiDaTruocNhap || 0} cuốn
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <h3>Lịch sử nhập kho</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã PN</th>
                            <th>Ngày nhập</th>
                            <th>Tên sách</th>
                            <th>Số lượng</th>
                            <th>Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
    `);
}

async function submitNhapSach() {
    const data = {
        MaSach: document.getElementById('pnSach').value,
        SoLuong: parseInt(document.getElementById('pnSoLuong').value || 0),
        DonGia: parseFloat(document.getElementById('pnDonGia').value || 0)
    };

    const msg = document.getElementById('msgNhapSach');
    if (!data.MaSach || data.SoLuong <= 0 || data.DonGia <= 0) {
        msg.classList.remove('hidden');
        msg.classList.add('error');
        msg.innerHTML = 'Vui lòng chọn sách và nhập đầy đủ thông tin.';
        return;
    }

    showLoading(true);
    const res = await api.addNhapSach(data);
    showLoading(false);

    msg.classList.remove('hidden');
    if (res.success) {
        msg.classList.add('success');
        msg.innerHTML = res.message;
        setTimeout(() => renderNhapSach(), 1200);
    } else {
        msg.classList.add('error');
        msg.innerHTML = res.message;
    }
}
