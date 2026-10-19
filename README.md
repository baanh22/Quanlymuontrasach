# 📚 Quản Lý Nhà Sách

Hệ thống quản lý bán sách, kho sách và công nợ khách hàng theo mô hình SPA (Single Page Application) kết hợp RESTful API PHP.

---

## 🚀 Công Nghệ Sử Dụng
- **Frontend:** HTML5, CSS3 (Glassmorphism Dark Theme, Responsive), Vanilla JavaScript (ES6+ SPA Router).
- **Backend:** PHP Native (RESTful API, Session, PDO Prepared Statements, ACID Transactions).
- **Database:** MySQL / MariaDB (XAMPP).

---

## ✨ Chức Năng Chính
1. **Dashboard:** Thống kê tổng quan kho, công nợ, đơn hàng, cảnh báo sách sắp hết và top sách bán chạy.
2. **Quản lý sách:** Thêm, sửa, xóa, tìm kiếm sách theo tên/tác giả/thể loại.
3. **Quản lý khách hàng:** Theo dõi thông tin và số tiền nợ của từng khách hàng.
4. **Nhập sách:** Lập phiếu nhập kho theo quy định, tự động tăng tồn kho và cập nhật đơn giá.
5. **Hóa đơn bán hàng:** Lập hóa đơn bán sách, tự động tính giá bán, trừ kho và cộng nợ khách hàng.
6. **Phiếu thu tiền:** Thu nợ từ khách hàng, kiểm soát không thu vượt nợ, giảm nợ tức thì.
7. **Báo cáo thống kê:** Báo cáo tồn kho và báo cáo công nợ theo tháng/năm, hỗ trợ xuất file Excel (CSV).
8. **Nhân viên & Cấu hình:** Quản lý tài khoản (Admin/User) và tùy chỉnh các quy định nghiệp vụ.

---

## ⚖️ 4 Quy Định Nghiệp Vụ Cốt Lõi
- **QĐ1 (Nhập sách):** Nhập ít nhất 150 cuốn; chỉ nhập sách có lượng tồn trước khi nhập < 300 cuốn.
- **QĐ2 (Bán sách):** Chỉ bán cho khách nợ $\le$ 20.000đ; tồn kho sau bán $\ge$ 20 cuốn; Giá bán = Giá nhập $\times$ 105%.
- **QĐ3 (Thu tiền):** Tiền thu không được vượt quá số tiền khách đang nợ.
- **QĐ4 (Thay đổi quy định):** Quản trị viên có thể tùy chỉnh lại các tham số trên trong phần Cài đặt.

---

## 🛠️ Hướng Dẫn Cài Đặt & Chạy Nhanh

1. **Khởi động XAMPP:** Bật 2 dịch vụ **Apache** và **MySQL**.
2. **Đặt source code:** Đặt thư mục `Quanlymuontrasach` vào `xampp/htdocs/`.
3. **Import CSDL:**
   - Vào [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Tạo database: `QuanLyNhaSach`.
   - Import file `database.sql` có sẵn trong thư mục gốc của dự án.
4. **Truy cập ứng dụng:**
   - Mở trình duyệt vào: **[http://localhost/Quanlymuontrasach/](http://localhost/Quanlymuontrasach/)**

---

## 🔑 Tài Khoản Đăng Nhập Mặc Định

| Tên Đăng Nhập | Mật Khẩu | Quyền Hạn |
| :--- | :--- | :--- |
| **`admin`** | **`admin`** | Quản trị viên (Toàn quyền) |
| **`nhanvien`** | **`123456`** | Nhân viên bán hàng |
