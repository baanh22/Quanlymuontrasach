# Project nguoi thu 1: Dang nhap va nhan vien

Project rieng phu trach xac thuc tai khoan, phan quyen va quan ly nhan vien.

## Chuc nang
- Dang nhap/dang xuat bang PHP session
- Kiem tra tai khoan hien tai (`me`)
- Phan quyen User/Admin o backend
- Admin them, sua, doi mat khau, phan quyen va xoa nhan vien
- Nhan vien cap nhat ho ten va mat khau cua chinh minh
- Tu dong doi hash MD5 cu sang `password_hash` sau lan dang nhap thanh cong

## Chay demo
1. Import `database/schema.sql` vao MySQL.
2. Neu can, sua host/user/password trong `api/config/database.php`.
3. Dat project trong XAMPP `htdocs`.
4. Mo `http://localhost/Quanlymuontrasach_Nguoi1_AuthEmployees/`.
5. Tai khoan demo: `admin` / `admin123`. Doi mat khau demo sau lan dang nhap dau tien.

## Khi ghep voi project chung
Module dung bang `TAIKHOAN` voi cac cot `Username`, `Password`, `FullName`, `Role`, tuong thich voi API goc. Database demo rieng la `QuanLyNhaSach_Nguoi1`; khi ghép, tro cac project vao cung database va dung mot schema tong hop. Hash MD5 cu duoc nang cap tu dong sau login; tao tai khoan moi se luon dung hash hien dai.

## API
- `POST api/auth.php?action=login`
- `POST api/auth.php?action=logout`
- `GET api/auth.php?action=me`
- `PUT api/profile.php` cap nhat ho ten/mat khau cua minh
- `GET/POST/PUT/DELETE api/nhanvien.php` (Admin)

Project demo khong nen dua len internet truoc khi cau hinh HTTPS, thong tin database va chinh sach bao mat phu hop.
