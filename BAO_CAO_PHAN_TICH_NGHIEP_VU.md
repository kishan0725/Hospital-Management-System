# BÁO CÁO PHÂN TÍCH NGHIỆP VỤ – HỆ THỐNG ĐẶT LỊCH KHÁM BỆNH

**Dự án:** Global Hospitals – Medical Appointment Booking System  
**Ngày:** 23/02/2026

---

## MỤC LỤC CÁC CHỨC NĂNG

| # | Chức năng | File chi tiết |
|---|-----------|--------------|
| 1 | Đánh giá & Feedback bác sĩ | [01_DANH_GIA_BAC_SI.md](./docs/01_DANH_GIA_BAC_SI.md) |
| 2 | Ghi chú bệnh án | [02_BENH_AN.md](./docs/02_BENH_AN.md) |
| 3 | Đơn thuốc chi tiết | [03_DON_THUOC.md](./docs/03_DON_THUOC.md) |
| 4 | Dashboard & Biểu đồ thống kê | [04_DASHBOARD.md](./docs/04_DASHBOARD.md) |
| 5 | Phân quyền người dùng | [05_PHAN_QUYEN.md](./docs/05_PHAN_QUYEN.md) |
| 6 | Diễn đàn (Forum) | [06_FORUM.md](./docs/06_FORUM.md) |

---

## TỔNG HỢP KỸ THUẬT CHÍNH

| Kỹ thuật | Mục đích | Áp dụng tại |
|----------|----------|-------------|
| `PDO::prepare() + execute()` | Ngăn SQL Injection | Toàn bộ truy vấn |
| `password_hash(PASSWORD_DEFAULT)` | Mã hóa bcrypt khi lưu mật khẩu | Register |
| `password_verify($plain, $hash)` | So khớp mật khẩu khi đăng nhập | Login |
| `session_regenerate_id(true)` | Chống Session Fixation sau login | Login thành công |
| `PDO Transaction` | Toàn vẹn dữ liệu 2+ bảng | Kê đơn thuốc |
| `lastInsertId()` | Lấy ID vừa INSERT làm FK | Kê đơn, Forum post |
| `fetchColumn()` | Lấy 1 giá trị (COUNT, AVG) | Pagination, Rating |
| `LIMIT n OFFSET m` | Phân trang server-side | Đơn thuốc, Lịch hẹn |
| Soft Delete | Không xóa cứng bệnh án | Medical Records |
| Whitelist validation | Chống injection qua ORDER BY | Forum sort |
| AJAX + `json_encode()` | Load dữ liệu bất đồng bộ | Dashboard Charts |
| `ON DELETE CASCADE` | Xóa cascade khi xóa bài Forum | Forum |
