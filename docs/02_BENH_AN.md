# CHỨC NĂNG 2: GHI CHÚ BỆNH ÁN (MEDICAL RECORDS)

## Mô tả tổng quan
Bác sĩ tạo và quản lý hồ sơ bệnh án điện tử. Hệ thống có 3 nguyên tắc cốt lõi: **(1)** bác sĩ chỉ thao tác với bệnh nhân mình đã từng khám, **(2)** bệnh nhân chỉ xem bệnh án của chính mình và `patient_id` luôn lấy từ session không từ form, **(3)** bệnh án không bao giờ bị xóa vĩnh viễn mà chỉ chuyển về trạng thái `archived`.

---

## LUỒNG 2.1: BÁC SĨ TẠO BỆNH ÁN MỚI

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session bác sĩ** | Hệ thống | `$_SESSION['doctor_id']`, `$_SESSION['user_type']` | Tiếp tục hoặc redirect | Đây là bước đầu tiên và quan trọng nhất — hệ thống xác nhận người dùng đang là bác sĩ đã đăng nhập. Nếu session không chứa `doctor_id` hoặc `user_type` không phải `'doctor'`, lập tức redirect về trang login và gọi `exit()` để dừng toàn bộ script. Không có `exit()` sau `header()` thì PHP vẫn tiếp tục chạy code bên dưới — đây là lỗi bảo mật phổ biến. |
| **Bước 2: Load danh sách bệnh nhân hợp lệ** | Hệ thống | `$doctor_id` từ session | Danh sách bệnh nhân đã khám với bác sĩ này | Hệ thống không load toàn bộ bệnh nhân trong hệ thống mà chỉ load những bệnh nhân **đã từng có lịch hẹn** với bác sĩ đang đăng nhập. Điều này thực hiện bằng cách JOIN `patreg` với `appointmenttb` có điều kiện `WHERE a.doctor = ?`. Dùng `DISTINCT` để tránh một bệnh nhân xuất hiện nhiều lần nếu đã khám nhiều lần. |
| **Bước 3: Nhận dữ liệu POST và ép kiểu** | Hệ thống | Toàn bộ `$_POST` | Biến PHP sạch và đúng kiểu | Với mỗi trường, hệ thống phải ép về đúng kiểu dữ liệu: `patient_id` dùng `intval()`, `height` và `weight` dùng `floatval()`, các trường text thì `trim()` để bỏ khoảng trắng thừa. Những trường không bắt buộc như `appointment_id` hay `follow_up_date` cần kiểm tra thêm — nếu rỗng thì gán `null` thay vì để chuỗi rỗng, vì DB sẽ phân biệt NULL với chuỗi rỗng. |
| **Bước 4: Validate các trường bắt buộc** | Hệ thống | `patient_id`, `symptoms`, `diagnosis`, `record_date` | Thông báo lỗi hoặc tiếp tục | Hệ thống kiểm tra tuần tự từng trường bắt buộc. Thiếu `patient_id` nghĩa là không biết tạo bệnh án cho ai. Thiếu `symptoms` hoặc `diagnosis` thì bệnh án không có giá trị y tế. Mỗi lỗi phát hiện sẽ redirect ngay kèm thông báo cụ thể để bác sĩ biết cần điền gì còn thiếu. |
| **Bước 5: Xác minh bệnh nhân thuộc bác sĩ** | Hệ thống | `patient_id`, `doctor_id` | Tiếp tục hoặc 403 Forbidden | Dù bước 2 đã lọc danh sách dropdown, người dùng vẫn có thể tự sửa `patient_id` trong form trước khi submit. Vì vậy hệ thống phải kiểm tra lại trong DB: bệnh nhân này có thực sự từng khám với bác sĩ này không? Truy vấn `SELECT COUNT(*) FROM appointmenttb WHERE pid = ? AND doctor = ?` — nếu kết quả là 0, tức là bác sĩ đang cố tạo bệnh án cho bệnh nhân lạ, trả về lỗi 403. |
| **Bước 6: Tính BMI phía server** | Hệ thống | `height` (cm), `weight` (kg) | Giá trị `$bmi` | Dù phía client (JavaScript) có thể hiển thị BMI real-time cho bác sĩ xem, hệ thống **không tin** giá trị BMI gửi lên từ form. Server tự tính lại theo công thức chuẩn: `BMI = weight / (height/100)²`. Nếu height hoặc weight bằng 0 thì gán `null` thay vì tính ra giá trị vô nghĩa. Đây là nguyên tắc: luôn tính các giá trị dẫn xuất phía server. |
| **Bước 7: INSERT bệnh án trong try/catch** | Hệ thống | Toàn bộ dữ liệu validated + `$bmi` | Row mới trong `medical_records` hoặc log lỗi | Hệ thống thực hiện INSERT trong khối `try/catch PDOException`. Nếu xảy ra lỗi DB (ví dụ vi phạm constraint, kết nối đứt...), lỗi được ghi vào error log bằng `error_log()` để admin kiểm tra sau — không được hiển thị cho người dùng vì có thể lộ cấu trúc DB. Người dùng chỉ nhận được thông báo lỗi thân thiện. |

---

## LUỒNG 2.2: BÁC SĨ XEM VÀ THAO TÁC BỆNH ÁN

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session** | Hệ thống | `$_SESSION['doctor_id']` | Tiếp tục hoặc redirect | Doctor middleware — kiểm tra session hợp lệ và đúng role. |
| **Bước 2: Query bệnh án của bác sĩ** | Hệ thống | `$doctor_id` | Mảng records flat từ DB | Hệ thống chỉ lấy bệnh án có `doctor_id = ?` khớp với session — không bao giờ để bác sĩ xem bệnh án của đồng nghiệp. Thêm điều kiện `status != 'archived'` để loại bỏ những bệnh án đã xóa mềm. JOIN với `patreg` để lấy thông tin bệnh nhân hiển thị kèm. |
| **Bước 3: Nhóm bệnh án theo bệnh nhân bằng PHP** | Hệ thống | Mảng flat `$records` trả về từ DB | Mảng 2 chiều `$grouped` theo `patient_id` | DB trả về dữ liệu dạng flat (mỗi row là 1 bệnh án). Để hiển thị theo nhóm bệnh nhân, hệ thống dùng PHP loop: với mỗi row, lấy `patient_id` làm key của mảng ngoài, đẩy bệnh án vào mảng con. Cách này hiệu quả hơn chạy nhiều query riêng lẻ cho từng bệnh nhân (tránh N+1 query problem). |
| **Bước 4: Lọc theo một bệnh nhân cụ thể** | Hệ thống | `$_GET['patient_id']` | Chỉ hiện bệnh án của BN đó | Nếu bác sĩ click vào một bệnh nhân để xem chi tiết, `patient_id` được truyền qua GET. Hệ thống thêm `AND mr.patient_id = ?` vào WHERE của query bước 2. Giá trị này phải `intval()` trước khi bind để tránh injection. |
| **Bước 5: Phân quyền sửa/xóa từng record** | Hệ thống | `$record['created_by']`, `$doctor_id` | Boolean `$can_modify` | Trong một bệnh án, bác sĩ này có thể là bác sĩ khám nhưng bệnh án lại do đồng nghiệp tạo. Hệ thống so sánh `created_by` với session `doctor_id` — chỉ chủ bản ghi mới được sửa hoặc xóa mềm. Nếu không phải chủ, ẩn nút edit/delete đi. |
| **Bước 6: Xóa mềm (soft delete)** | Hệ thống | POST: `record_id`, `action='archive'` | `status` chuyển sang `'archived'` | Bệnh án y tế mang tính pháp lý và không thể bị xóa vĩnh viễn. Thay vì `DELETE`, hệ thống chạy `UPDATE medical_records SET status = 'archived' WHERE id = ? AND doctor_id = ?`. Điều kiện `AND doctor_id = ?` là bảo vệ kép — ngay cả khi ai đó biết `record_id` của bệnh án khác, họ vẫn không archive được vì `doctor_id` không khớp. |

---

## LUỒNG 2.3: BỆNH NHÂN XEM BỆNH ÁN CỦA MÌNH

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session bệnh nhân** | Hệ thống | `$_SESSION['pid']`, `$_SESSION['user_type']` | Tiếp tục hoặc redirect | Patient middleware kiểm tra `user_type === 'patient'`. Sau đó gán `$patient_id = intval($_SESSION['pid'])` — đây là giá trị duy nhất được tin dùng để query. |
| **Bước 2: Query bệnh án của chính mình** | Hệ thống | `$patient_id` từ session | Danh sách bệnh án của BN | Điều kiện WHERE luôn là `patient_id = $_SESSION['pid']`. Hệ thống JOIN thêm `doctb` và `specializations` để hiển thị tên bác sĩ và chuyên khoa. Bệnh nhân không thể xem bệnh án của người khác vì `patient_id` cứng từ session. |
| **Bước 3: Kiểm tra sở hữu khi xem chi tiết** | Hệ thống | `$_GET['record_id']` | Chi tiết hoặc lỗi 403 | Dù bắt đầu từ danh sách của mình, bệnh nhân vẫn có thể thử đổi `record_id` trong URL để xem bệnh án người khác. Sau khi fetch record theo `id`, hệ thống kiểm tra thêm: `if ($record['patient_id'] != $_SESSION['pid']) { http_response_code(403); exit(); }` — đây là kiểm tra sở hữu bắt buộc sau khi fetch. |

---

## LUỒNG 2.4: ADMIN XEM LỊCH SỬ BỆNH ÁN (AJAX)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra quyền admin** | Hệ thống | `$_SESSION['user_type']` | Tiếp tục hoặc 403 | Admin middleware. Admin có quyền xem toàn bộ bệnh án không giới hạn theo `doctor_id` hay `patient_id`. |
| **Bước 2: Phân loại AJAX request** | Hệ thống | POST: `action`, `patient_id` | Rẽ nhánh xử lý | Hệ thống nhận AJAX POST từ JS, đọc trường `action` để biết cần làm gì. Nếu `action = 'view_patient_history'` thì đọc `patient_id`, ép kiểu `intval()`. |
| **Bước 3: Query và render HTML động** | Hệ thống | `$pid` | JSON `{success, html}` | Hệ thống query toàn bộ bệnh án của bệnh nhân đó, JOIN doctor và specialization. Sau đó dùng `ob_start()` để bắt đầu output buffer, render HTML table, rồi `ob_get_clean()` để lấy HTML thành string. Cuối cùng đóng gói vào JSON: `echo json_encode(['success' => true, 'html' => $html])`. JavaScript nhận về, lấy `data.html` và nhúng vào modal. |

---

## Quy tắc nghiệp vụ

| Nguyên tắc | Lý do |
|-----------|-------|
| Không xóa cứng bệnh án | Tính pháp lý — bệnh án là tài liệu y tế |
| `patient_id` từ session, không từ GET/POST | Ngăn IDOR (Insecure Direct Object Reference) |
| BMI tính server-side | Không tin dữ liệu tính toán từ client |
| `AND doctor_id = ?` trong mọi UPDATE/DELETE của bác sĩ | Tránh bác sĩ thao tác trên bệnh án của đồng nghiệp |
| Kiểm tra sở hữu sau fetch | Dù đã lọc trong danh sách, vẫn phải kiểm tra lại khi xem chi tiết |
