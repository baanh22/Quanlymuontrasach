async function renderSach() {
    setPageTitle('Danh mục sách');
    setContent(`
        <header class="page-heading">
            <div><p class="eyebrow">Kho sách</p><h1>Danh mục sách</h1></div>
            <button class="btn btn-primary" id="btnAddBook">+ Thêm sách</button>
        </header>
        <div class="toolbar">
            <input class="search-field" id="inpSearchSach" type="search" placeholder="Tìm mã, tên sách, tác giả hoặc thể loại" />
            <button class="btn btn-primary" id="btnSearchSach">Tìm kiếm</button>
            <button class="btn btn-quiet" id="btnClearSearch">Xóa lọc</button>
        </div>
        <div id="bookAlert" class="alert error" hidden></div>
        <section class="panel"><div class="table-wrap"><table>
            <thead><tr><th>Mã sách</th><th>Tên sách</th><th>Thể loại</th><th>Tác giả</th><th>Tồn kho</th><th>Đơn giá</th><th>Thao tác</th></tr></thead>
            <tbody id="tblSachBody"><tr><td colspan="7" class="empty-state">Đang tải dữ liệu...</td></tr></tbody>
        </table></div></section>
    `);

    document.getElementById('btnAddBook').addEventListener('click', () => showBookModal());
    document.getElementById('btnSearchSach').addEventListener('click', loadSach);
    document.getElementById('btnClearSearch').addEventListener('click', () => {
        document.getElementById('inpSearchSach').value = '';
        loadSach();
    });
    document.getElementById('inpSearchSach').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') loadSach();
    });
    document.getElementById('tblSachBody').addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const book = window.bookInventory.find((item) => item.MaSach === button.dataset.id);
        if (button.dataset.action === 'edit' && book) showBookModal(book);
        if (button.dataset.action === 'delete') deleteSach(button.dataset.id);
    });
    await loadSach();
}

async function loadSach() {
    const body = document.getElementById('tblSachBody');
    const alert = document.getElementById('bookAlert');
    if (!body) return;
    alert.hidden = true;
    body.innerHTML = '<tr><td colspan="7" class="empty-state">Đang tải dữ liệu...</td></tr>';

    const search = document.getElementById('inpSearchSach')?.value || '';
    const response = await api.getSach(search);
    if (!response?.success) {
        body.innerHTML = '';
        alert.hidden = false;
        alert.textContent = response?.message || 'Không tải được danh sách sách.';
        return;
    }
    window.bookInventory = response.data;
    if (!response.data.length) {
        body.innerHTML = '<tr><td colspan="7" class="empty-state">Không tìm thấy sách.</td></tr>';
        return;
    }
    body.innerHTML = response.data.map((book) => `
        <tr>
            <td><span class="pill">${escapeHtml(book.MaSach)}</span></td>
            <td class="primary-cell">${escapeHtml(book.TenSach)}</td>
            <td>${escapeHtml(book.TheLoai || '—')}</td>
            <td>${escapeHtml(book.TacGia || '—')}</td>
            <td><span class="pill ${Number(book.SoLuongTon) === 0 ? 'low' : 'clear'}">${Number(book.SoLuongTon)} cuốn</span></td>
            <td>${money(book.DonGia)}</td>
            <td><div class="actions">
                <button class="btn btn-quiet btn-small" data-action="edit" data-id="${escapeHtml(book.MaSach)}">Sửa</button>
                <button class="btn btn-danger btn-small" data-action="delete" data-id="${escapeHtml(book.MaSach)}">Xóa</button>
            </div></td>
        </tr>`).join('');
}

function showBookModal(book = {}) {
    const editing = Boolean(book.MaSach);
    const body = `
        <form id="frmSach">
            <div id="frmSachError" class="alert error" hidden></div>
            <div class="form-group"><label for="sachMa">Mã sách</label><input id="sachMa" required maxlength="50" ${editing ? 'disabled' : ''} value="${escapeHtml(book.MaSach || '')}" placeholder="VD: S004" /></div>
            <div class="form-group"><label for="sachTen">Tên sách</label><input id="sachTen" required maxlength="255" value="${escapeHtml(book.TenSach || '')}" /></div>
            <div class="form-row">
                <div class="form-group"><label for="sachTheLoai">Thể loại</label><input id="sachTheLoai" maxlength="150" value="${escapeHtml(book.TheLoai || '')}" /></div>
                <div class="form-group"><label for="sachTacGia">Tác giả</label><input id="sachTacGia" maxlength="255" value="${escapeHtml(book.TacGia || '')}" /></div>
            </div>
            ${editing ? `<div class="form-group"><label for="sachTon">Tồn kho</label><input id="sachTon" disabled value="${Number(book.SoLuongTon)} cuốn (thay đổi qua chức năng nhập kho)" /></div>` : `<div class="form-group"><label for="sachTon">Tồn kho ban đầu</label><input id="sachTon" type="number" min="0" step="1" value="0" required /></div>`}
            <div class="form-group"><label for="sachGia">Đơn giá</label><input id="sachGia" type="number" min="0" step="0.01" value="${escapeHtml(book.DonGia ?? 0)}" required /></div>
        </form>`;
    showModal(
        editing ? 'Cập nhật thông tin sách' : 'Thêm sách mới',
        body,
        '<button class="btn btn-quiet" data-close-modal>Hủy</button><button class="btn btn-primary" id="btnSaveBook">Lưu sách</button>'
    );
    document.getElementById('btnSaveBook').addEventListener('click', () => {
        document.getElementById('frmSach').requestSubmit();
    });
    document.getElementById('frmSach').addEventListener('submit', (event) => {
        event.preventDefault();
        submitSach(editing ? 'edit' : 'add', book.MaSach || '');
    });
}

async function submitSach(mode, id) {
    const error = document.getElementById('frmSachError');
    const data = {
        MaSach: document.getElementById('sachMa').value.trim() || id,
        TenSach: document.getElementById('sachTen').value.trim(),
        TheLoai: document.getElementById('sachTheLoai').value.trim(),
        TacGia: document.getElementById('sachTacGia').value.trim(),
        DonGia: Number(document.getElementById('sachGia').value)
    };
    if (mode === 'add') data.SoLuongTon = Number(document.getElementById('sachTon').value);
    const response = mode === 'add' ? await api.addSach(data) : await api.updateSach(id, data);
    if (!response.success) {
        error.hidden = false;
        error.textContent = response.message || 'Không thể lưu sách.';
        return;
    }
    closeModal();
    await loadSach();
}

async function deleteSach(id) {
    if (!confirm(`Xóa sách ${id}? Sách đã phát sinh nhập kho hoặc hóa đơn sẽ không thể xóa.`)) return;
    const response = await api.deleteSach(id);
    if (!response.success) {
        const alert = document.getElementById('bookAlert');
        alert.hidden = false;
        alert.textContent = response.message || 'Không thể xóa sách.';
        return;
    }
    await loadSach();
}
