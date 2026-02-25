# CHỨC NĂNG 5: PHÂN QUYỀN NGƯỜI DÙNG (AUTHENTICATION & AUTHORIZATION)

## Mô tả tổng quan
Hệ thống xác thực 3 role từ 3 bảng DB riêng biệt. Thứ tự kiểm tra khi đăng nhập: **Patient trước, Doctor sau, Admin cuối**. Dùng PHP Session để duy trì trạng thái. Password mã hóa bằng bcrypt. Mỗi nhóm trang được bảo vệ bằng đoạn code middleware đặt ở đầu file.

---

## LUỒNG 5.1: ĐĂNG NHẬP (LOGIN)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Chỉ xử lý khi request là POST** | Hệ thống | `$_SERVER['REQUEST_METHOD']` | Tiếp tục hoặc chỉ hiển thị form | Hệ thống kiểm tra method ngay đầu file. Nếu là GET (user truy cập trang login lần đầu), chỉ render form HTML. Nếu là POST (user vừa submit form), mới bắt đầu xử lý logic. Điều này tránh chạy code xác thực khi không cần thiết. |
| **Bước 2: Làm sạch dữ liệu đầu vào** | Hệ thống | POST: `username`, `password` | Biến `$username`, `$password` | `$username = trim($_POST['username'])` — xóa khoảng trắng đầu/cuối. Riêng `$password` **không** trim vì người dùng có thể cố ý đặt mật khẩu có khoảng trắng. Nếu trim password, user sẽ không đăng nhập được mặc dù nhập đúng. |
| **Bước 3: Validate không được rỗng** | Hệ thống | `$username`, `$password` | Lỗi hoặc tiếp tục | Nếu một trong hai rỗng, lập tức dừng và báo lỗi. Không để đến bước query DB vì query với chuỗi rỗng có thể trả về kết quả không mong muốn. |
| **Bước 4: Kiểm tra Patient (ưu tiên 1)** | Hệ thống | `$username` so với cột `email` trong `patreg` | Row bệnh nhân hoặc không tìm thấy | Hệ thống tìm kiếm trong bảng `patreg` với điều kiện `email = ?`. Lý do dùng `email`: bệnh nhân đăng ký bằng email, không có `username`. Nếu tìm thấy row và `password_verify($password, $patient['password'])` trả về true → xác thực thành công, set session Patient (bước 7a). Nếu thất bại → tiếp tục xuống Doctor. |
| **Bước 5: Kiểm tra Doctor (ưu tiên 2)** | Hệ thống | `$username` so với cột `username` trong `doctb` | Row bác sĩ hoặc không tìm thấy | Chỉ chạy nếu Patient check thất bại. Tìm trong `doctb` với `username = ? AND status = 1` — thêm `status = 1` để bác sĩ đã bị vô hiệu hóa không đăng nhập được dù biết mật khẩu đúng. `password_verify()` tự xử lý salt trong bcrypt hash, không cần tách thủ công. |
| **Bước 6: Kiểm tra Admin (ưu tiên 3)** | Hệ thống | `$username` so với cột `username` trong `admintb` | Row admin hoặc không tìm thấy | Chỉ chạy nếu cả Patient và Doctor đều thất bại. Tìm trong `admintb` với `username = ?`. Admin ít người nên không cần thêm điều kiện status. |
| **Bước 7: Xử lý khi tất cả check đều thất bại** | Hệ thống | Không match bất kỳ bảng nào | Thông báo lỗi chung | Nếu cả 3 lần kiểm tra đều thất bại, redirect về login với thông báo lỗi chung chung ("Sai thông tin đăng nhập") mà **không nói rõ** tài khoản không tồn tại hay mật khẩu sai. Nếu thông báo cụ thể, attacker biết được username có tồn tại hay không. |
| **Bước 8a: Tạo Session Patient** | Hệ thống | `$patient` row từ DB | Session variables được set | Gọi `session_regenerate_id(true)` trước khi ghi session — tạo Session ID mới và xóa file session cũ. Đây là biện pháp chống **Session Fixation Attack**: kẻ tấn công không thể dùng lại session ID cũ đã biết. Set: `pid`, `username`, `email`, `user_type = 'patient'`, `patientSession = true`. |
| **Bước 8b: Tạo Session Doctor** | Hệ thống | `$doctor` row từ DB | Session variables được set | Tương tự bước 8a nhưng set: `dname` (tên bác sĩ — dùng cho query `prestb`), `doctor_id` (ID số — dùng cho query `medical_records`), `user_type = 'doctor'`, `doctorSession = true`. Có 2 giá trị khác nhau vì 2 bảng dùng 2 cách lưu khác nhau. |
| **Bước 8c: Tạo Session Admin** | Hệ thống | `$admin` row từ DB | Session variables được set | Set: `username`, `user_type = 'admin'`, `adminSession = true`. Admin không cần `pid` hay `doctor_id`. |

---

## LUỒNG 5.2: ĐĂNG KÝ (REGISTER — CHỈ CHO BỆNH NHÂN)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Làm sạch dữ liệu** | Hệ thống | POST: tất cả fields | Biến PHP đã trim() | `trim()` tất cả trường trừ password. Email thêm `strtolower()` để chuẩn hóa — "User@Email.COM" và "user@email.com" phải được xem là cùng một email. |
| **Bước 2: Validate định dạng email** | Hệ thống | `$email` | Hợp lệ hoặc lỗi | Dùng `filter_var($email, FILTER_VALIDATE_EMAIL)` — hàm built-in của PHP kiểm tra định dạng email theo chuẩn RFC. Đây là cách kiểm tra chính xác hơn so với tự viết regex. |
| **Bước 3: Validate mật khẩu khớp nhau** | Hệ thống | `$password`, `$cpassword` | Khớp hoặc lỗi | So sánh bằng `===` (strict comparison) không phải `==`. Nếu không khớp, dừng ngay — không để đến bước hash mật khẩu rồi mới phát hiện. |
| **Bước 4: Kiểm tra email và SĐT unique** | Hệ thống | `$email`, `$contact` | Unique hoặc lỗi | Hệ thống chạy 2 query `SELECT COUNT(*) FROM patreg WHERE email = ?` và `WHERE contact = ?`. Dùng `fetchColumn()` để lấy số đếm trực tiếp. Nếu > 0 nghĩa là đã có người dùng thông tin đó — báo lỗi cụ thể để người dùng biết field nào bị trùng. |
| **Bước 5: Hash mật khẩu** | Hệ thống | `$password` plain text | `$hashed` bcrypt string | `password_hash($password, PASSWORD_DEFAULT)` — `PASSWORD_DEFAULT` hiện tại là bcrypt. Hàm tự tạo salt ngẫu nhiên và nhúng vào trong chuỗi hash, nên không cần lưu salt riêng. Chuỗi kết quả trông như `$2y$10$...` (khoảng 60 ký tự). Không bao giờ lưu plain text vào DB. |
| **Bước 6: INSERT bệnh nhân mới** | Hệ thống | Tất cả dữ liệu + `$hashed` | `$pid` mới từ `lastInsertId()` | INSERT vào `patreg`. Lưu `$hashed` vào cả cột `password` lẫn `cpassword` (cột `cpassword` là thiết kế cũ, vẫn phải điền để tránh DB error). Sau INSERT, gọi ngay `lastInsertId()` để lấy `pid` của bệnh nhân vừa tạo. |
| **Bước 7: Tự động đăng nhập sau khi đăng ký** | Hệ thống | `$pid` | Session patient được tạo | Thay vì bắt user quay lại đăng nhập, hệ thống tự tạo session ngay sau khi đăng ký thành công. Gọi `session_regenerate_id(true)` trước, rồi set `$_SESSION['pid'] = $pid` và các biến session tương tự bước 8a của login. |

---

## LUỒNG 5.3: MIDDLEWARE BẢO VỆ TRANG

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Admin Middleware** | Hệ thống | `$_SESSION` | Cho phép hoặc redirect về login | Đây là đoạn code đặt ở **dòng đầu tiên** của mỗi file trong `pages/admin/*`. Hệ thống kiểm tra: `username` có trong session không?, `user_type` có đúng là `'admin'` không? Thiếu bất kỳ điều kiện nào → redirect về login + `exit()`. Không có `exit()` thì PHP tiếp tục chạy code trang admin — đây là lỗ hổng nghiêm trọng. |
| **Doctor Middleware** | Hệ thống | `$_SESSION` | Cho phép hoặc redirect về login | Tương tự nhưng kiểm tra `dname` (không phải `username`) và `user_type = 'doctor'`. Đặt ở đầu mỗi file trong `pages/doctor/*`. |
| **Patient Middleware** | Hệ thống | `$_SESSION` | Cho phép hoặc redirect về trang chủ | Kiểm tra `pid` và `user_type = 'patient'`. Bệnh nhân không được redirect về login mà về `index.php` (trang chủ public). Đặt ở đầu mỗi file trong `pages/patient/*`. |

---

## LUỒNG 5.4: ĐĂNG XUẤT (LOGOUT)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Xóa tất cả biến session** | Hệ thống | Session hiện tại | `$_SESSION` rỗng | `session_unset()` — xóa tất cả variables trong `$_SESSION` array nhưng giữ session file trên server và cookie trên browser. |
| **Bước 2: Hủy session trên server** | Hệ thống | — | Session file bị xóa khỏi server | `session_destroy()` — xóa file session khỏi server. Sau bước này, session ID cũ không còn hợp lệ ở phía server nữa. |
| **Bước 3: Xóa cookie session trên browser** | Hệ thống | Cookie `PHPSESSID` | Cookie bị hết hạn | `setcookie(session_name(), '', time() - 3600, '/')` — gửi lệnh cho browser đặt thời gian hết hạn của cookie về quá khứ, buộc browser xóa cookie. Nếu bỏ bước này, browser vẫn giữ cookie với session ID cũ, tuy server đã hủy session nhưng vẫn còn "dấu vết" ở client. |

---

## So sánh 3 hàm trong Logout

| Hàm | Hành động | Nếu thiếu |
|-----|-----------|-----------|
| `session_unset()` | Xóa `$_SESSION` variables | Session file vẫn còn biến |
| `session_destroy()` | Xóa session file trên server | Session ID cũ vẫn hợp lệ ở server |
| `setcookie(..., time()-3600)` | Xóa cookie trên browser | Browser vẫn giữ session ID cũ |

## Quy tắc nghiệp vụ

| Vấn đề | Giải pháp | Lý do |
|--------|----------|-------|
| SQL Injection | PDO Prepared Statement với `?` | Tách code khỏi data |
| Lộ thông tin khi login sai | Thông báo lỗi chung chung | Attacker không biết username có tồn tại |
| Session Fixation | `session_regenerate_id(true)` sau login | Tạo ID mới, vô hiệu hóa ID cũ |
| Lưu mật khẩu | `password_hash(PASSWORD_DEFAULT)` | Bcrypt + salt tự động |
| So sánh mật khẩu | `password_verify()` | Tự xử lý salt, không thể đảo ngược |
| Bác sĩ bị khóa vẫn đăng nhập | `AND status = 1` trong query | Từ chối từ DB level |
| Script tiếp tục chạy sau redirect | `exit()` sau `header()` | PHP không tự dừng khi redirect |
