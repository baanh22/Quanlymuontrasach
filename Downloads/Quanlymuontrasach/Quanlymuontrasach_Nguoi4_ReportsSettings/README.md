# Project nguoi thu 4: Bao cao, thong ke va cau hinh

## Chuc nang
- Dashboard: tong sach, dau sach, khach hang, cong no, hoa don, phieu nhap, sach sap het va top sach ban chay
- Bao cao ton kho theo thang/nam
- Bao cao cong no theo thang/nam
- Xuat CSV tu bang bao cao
- Xem va cap nhat cac quy dinh nhap/bán sach

## Chay project doc lap
1. Import `database/schema.sql` vao MySQL.
2. Sua thong tin ket noi trong `api/config/database.php` neu can.
3. Dat folder vao `htdocs` (neu dung XAMPP).
4. Mo `http://localhost/Quanlymuontrasach_Nguoi4_ReportsSettings/`.

Project dung database mau `QuanLyNhaSach_Nguoi4` de khong sua database cac module khac. Khi ghep nhom, doi `$db` sang database chung va chi dung schema tong hop cua nhom; khong import schema rieng vao database da co.

## API
- `GET api/thongke.php`
- `GET api/baocao.php?type=tonkho&thang=10&nam=2026`
- `GET api/baocao.php?type=congno&thang=10&nam=2026`
- `GET/PUT api/cauhinh.php`

Project mau chua noi voi module dang nhap. Can tich hop middleware phan quyen Admin truoc khi cho phep cap nhat cau hinh tren moi truong that.
