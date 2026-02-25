# CHỨC NĂNG 1: ĐÁNH GIÁ & FEEDBACK BÁC SĨ

## Mô tả tổng quan
Bệnh nhân gửi đánh giá (1–5 sao + nhận xét) cho bác sĩ. Hệ thống lưu đánh giá, tính lại điểm trung bình và cập nhật ngay vào bảng bác sĩ để đảm bảo dữ liệu rating luôn chính xác theo thời gian thực.

---

## LUỒNG 1: BỆNH NHÂN GỬI ĐÁNH GIÁ

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra xác thực** | Hệ thống | `$_SESSION['pid']`, `$_SESSION['user_type']` | Cho phép tiếp tục hoặc redirect về login | Trước tiên hệ thống phải xác nhận người đang truy cập có đúng là bệnh nhân đã đăng nhập hay không. Nếu session không tồn tại hoặc user_type không phải `'patient'` thì dừng ngay, không cho làm gì thêm. Điều này ngăn khách vãng lai hoặc bác sĩ gửi đánh giá thay bệnh nhân. `if (!isset($_SESSION['pid']) || $_SESSION['user_type'] !== 'patient') { header("Location: login.php"); exit(); }` |
| **Bước 2: Load danh sách bác sĩ** | Hệ thống | Không có tham số đặc biệt | Mảng `$doctors` kèm tên chuyên khoa và rating | Hệ thống truy vấn tất cả bác sĩ đang hoạt động (`status = 1`), JOIN thêm bảng specializations để lấy tên chuyên khoa hiển thị. Chỉ lấy bác sĩ active vì bác sĩ bị khóa không nên nhận đánh giá mới. `SELECT d.*, s.name_vi as spec_name FROM doctb d LEFT JOIN specializations s ON d.spec_id = s.id WHERE d.status = 1 ORDER BY d.average_rating DESC` |
| **Bước 3: Nhận và ép kiểu dữ liệu POST** | Hệ thống | POST: `doctor_id`, `rating`, `review` | Biến PHP đã được ép kiểu an toàn | Khi nhận dữ liệu từ form, hệ thống phải ép kiểu ngay lập tức. `doctor_id` và `rating` bắt buộc là số nguyên (`intval`), `review` là chuỗi tùy chọn được làm sạch bằng `trim()` và `htmlspecialchars()` để chống XSS. Quan trọng: `patient_id` lấy từ `$_SESSION['pid']` chứ không phải từ form — vì nếu lấy từ form, người dùng có thể chỉnh sửa và gửi đánh giá dưới danh nghĩa người khác. |
| **Bước 4: Validate dữ liệu đầu vào** | Hệ thống | `$doctor_id`, `$rating` | Thông báo lỗi cụ thể hoặc tiếp tục | Hệ thống kiểm tra từng điều kiện theo thứ tự: `doctor_id` phải lớn hơn 0, `rating` phải nằm trong khoảng 1–5. Nếu vi phạm bất kỳ điều kiện nào, dừng lại và trả về thông báo lỗi tương ứng. Không để lỗi chung chung vì người dùng cần biết chính xác vấn đề là gì. |
| **Bước 5: Xác minh bác sĩ thực sự tồn tại** | Hệ thống | `$doctor_id` | Tiếp tục hoặc báo lỗi | Đây là bước bảo mật bổ sung — dù form có danh sách bác sĩ, người dùng vẫn có thể tự chỉnh `doctor_id` trong form trước khi submit. Vì vậy hệ thống phải truy vấn DB một lần nữa để chắc chắn `doctor_id` đó thực sự tồn tại và đang active. `SELECT id FROM doctb WHERE id = ? AND status = 1` — nếu không tìm thấy, từ chối xử lý. |
| **Bước 6: INSERT đánh giá vào DB** | Hệ thống | `doctor_id`, `patient_id`, `rating`, `review` | Một row mới trong bảng `doctor_ratings` | Sau khi tất cả validate qua, hệ thống thực hiện INSERT vào `doctor_ratings`. Dùng Prepared Statement với placeholder `?` để tránh SQL Injection — không bao giờ ghép chuỗi trực tiếp vào câu SQL. `INSERT INTO doctor_ratings (doctor_id, patient_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())` |
| **Bước 7: Tính lại điểm trung bình** | Hệ thống | `doctor_id` | Giá trị `avg_rating` và `total_ratings` mới | Sau khi lưu đánh giá xong, hệ thống phải tính lại điểm trung bình ngay lập tức. Lý do không lưu sẵn mà tính lại: để đảm bảo số liệu luôn chính xác 100%, không bị sai nếu có nhiều đánh giá được gửi đồng thời. Dùng hàm SQL `AVG(rating)` và `COUNT(*)` trên toàn bộ đánh giá của bác sĩ đó. `SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM doctor_ratings WHERE doctor_id = ?` |
| **Bước 8: Cập nhật lại bảng bác sĩ** | Hệ thống | `avg_rating`, `total_ratings`, `doctor_id` | Bảng `doctb` được cập nhật | Điểm trung bình vừa tính được ghi trực tiếp vào hai cột `average_rating` và `total_ratings` của bảng `doctb`. Lý do lưu vào `doctb` thay vì chỉ tính khi hiển thị: giúp truy vấn danh sách bác sĩ nhanh hơn vì không cần JOIN hoặc subquery mỗi lần load trang. `UPDATE doctb SET average_rating = ?, total_ratings = ? WHERE id = ?` |
| **Bước 9: Redirect với thông báo** | Hệ thống | Trạng thái thành công | Quay về trang reviews kèm flash message | Hệ thống lưu thông báo thành công vào `$_SESSION['flash']` rồi redirect. Lý do dùng session thay vì GET param: tránh thông báo hiện lại khi user F5 lại trang. `redirectWithMessage('reviews.php', 'success', 'Đánh giá đã được ghi nhận!')` |

---

## LUỒNG 2: XEM DANH SÁCH ĐÁNH GIÁ (KHÔNG CẦN ĐĂNG NHẬP)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Load danh sách bác sĩ** | Hệ thống | Không | Danh sách bác sĩ kèm điểm đánh giá | Đây là dữ liệu công khai nên không cần kiểm tra session. Hệ thống JOIN bảng `doctb` với `specializations` để lấy tên chuyên khoa, đồng thời lấy luôn `average_rating` và `total_ratings` đã được lưu sẵn ở bảng `doctb` — giúp tránh phải tính lại mỗi lần load trang. |
| **Bước 2: Nhận doctor_id để xem chi tiết** | Hệ thống | GET: `id` | `$doctor_id` được validate | Khi người dùng click vào một bác sĩ, `doctor_id` được truyền qua GET. Hệ thống ép kiểu bằng `intval()` và kiểm tra > 0 để tránh query với ID rác. |
| **Bước 3: Load danh sách đánh giá của bác sĩ** | Hệ thống | `$doctor_id` | Danh sách reviews kèm tên bệnh nhân | Truy vấn bảng `doctor_ratings` JOIN với `patreg` để lấy tên bệnh nhân. Sắp xếp theo `created_at DESC` để hiển thị mới nhất lên trên. `SELECT r.rating, r.review, r.created_at, p.fname, p.lname FROM doctor_ratings r INNER JOIN patreg p ON r.patient_id = p.pid WHERE r.doctor_id = ? ORDER BY r.created_at DESC` |

---

## CẤU TRÚC BẢNG

| Bảng | Cột | Kiểu | Mô tả |
|------|-----|------|--------|
| `doctor_ratings` | `id` | INT PK | Mã đánh giá |
| | `doctor_id` | INT FK → doctb.id | Bác sĩ được đánh giá |
| | `patient_id` | INT FK → patreg.pid | Bệnh nhân đánh giá |
| | `rating` | TINYINT | Điểm 1–5 |
| | `review` | TEXT nullable | Nội dung nhận xét |
| | `created_at` | DATETIME | Thời gian gửi |
| `doctb` | `average_rating` | DECIMAL(3,2) | Điểm TB — cập nhật sau mỗi đánh giá |
| | `total_ratings` | INT | Tổng số lượt đánh giá |

## Quy tắc nghiệp vụ
- `rating` (1–5) là bắt buộc; `review` là tùy chọn
- `patient_id` **luôn** lấy từ `$_SESSION['pid']` — không lấy từ form
- Sau mỗi INSERT **phải** chạy lại `AVG()` và `UPDATE doctb` ngay — không được bỏ qua
- Bước xác minh `doctor_id` trong DB là cần thiết dù form đã có dropdown
