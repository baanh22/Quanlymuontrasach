# Project nguoi thu 2: Quan ly sach va nhap kho

Module phu trach danh muc sach, ton kho va lap phieu nhap sach.

## Chuc nang
- Tim kiem sach theo ten, tac gia, the loai va ma sach.
- Them, cap nhat thong tin va xoa sach chua co giao dich lien quan.
- Lap phieu nhap theo quy dinh so luong toi thieu va muc ton kho truoc khi nhap.
- Cap nhat ton kho va don gia sach trong cung mot giao dich co khoa dong sach.
- Xem lich su phieu nhap va thong tin tung phieu.

## Chay demo
1. Import `database/schema.sql` vao MySQL.
2. Neu can, sua thong tin ket noi trong `api/config/database.php`.
3. Dat thu muc project trong XAMPP `htdocs`.
4. Mo `http://localhost/Quanlymuontrasach_Nguoi2_BookInventory/`.

Module dung `require_auth()` giong project nguoi thu 3 de co the ghep voi ung dung chung. Khi ghep voi project nguoi thu 1, thay middleware demo bang middleware dang nhap chung va tro cac module vao cung database.

## API
- `GET/POST/PUT/DELETE api/sach.php` quan ly sach; tim kiem qua tham so `search`, cap nhat/xoa qua `id`.
- `GET/POST api/nhapsach.php` xem lich su va lap phieu nhap sach.
- `GET api/cauhinh.php` doc cac quy dinh nhap sach.

Tat ca API nhan va tra JSON. Payload lap phieu nhap: `{"MaSach":"S001","SoLuong":10,"DonGia":50000}`.
