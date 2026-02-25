# CHỨC NĂNG 4: DASHBOARD VỚI BIỂU ĐỒ THỐNG KÊ

## Mô tả tổng quan
Dashboard dùng kiến trúc **AJAX bất đồng bộ**: trang tải trước với các con số thống kê cơ bản, sau đó JavaScript gọi riêng từng API endpoint để load biểu đồ. Điều này giúp trang hiển thị nhanh ngay lập tức, không bị block bởi các query nặng. Admin thấy toàn bộ hệ thống, Doctor chỉ thấy dữ liệu cá nhân — sự khác biệt nằm ở điều kiện WHERE trong câu query.

---

## LUỒNG 4.1: ADMIN DASHBOARD

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session admin** | Hệ thống | `$_SESSION['user_type']` | Tiếp tục hoặc redirect | Admin middleware. Sau khi qua middleware, hệ thống biết chắc người dùng là admin và không cần lọc dữ liệu theo user_id — admin có quyền xem tất cả. |
| **Bước 2: Query thống kê tổng hợp dạng cards** | Hệ thống | Không có filter | 6 con số thống kê | Đây là phần load cùng lúc với trang, nên cần chạy nhanh. Hệ thống thực hiện 6 query COUNT và SUM đơn giản. Với doanh thu, cần thêm điều kiện `userStatus = 1 AND doctorStatus = 1` để chỉ tính những lịch hẹn cả hai bên đã xác nhận. Dùng `fetchColumn()` cho từng query vì chỉ cần 1 giá trị duy nhất. |
| **Bước 3: JavaScript kích hoạt load biểu đồ sau khi DOM ready** | JavaScript | DOMContentLoaded event | 4 AJAX request song song | Sau khi trang HTML đã hiển thị đầy đủ (`DOMContentLoaded`), JavaScript gọi hàm `loadChart()` cho từng biểu đồ. 4 lần gọi `fetch()` chạy **gần như song song** vì chúng đều là async, giúp giảm thời gian chờ tổng thể so với gọi tuần tự. |
| **Bước 4: API nhận action và định tuyến** | Hệ thống | GET: `?action=tên_action` | Gọi đúng hàm xử lý | File `charts_api.php` đọc `$_GET['action']`, dùng `switch` để gọi đúng hàm tương ứng. Mỗi action là một hàm riêng biệt để code dễ bảo trì. Tất cả response đều có `Content-Type: application/json` và cùng format `{"labels": [...], "data": [...]}` để JavaScript xử lý thống nhất. |
| **Bước 5: Query bệnh nhân mới theo tháng** | Hệ thống | Không | JSON 12 tháng gần nhất | Đếm số bệnh nhân phân biệt (`COUNT DISTINCT pid`) theo từng tháng trong 12 tháng gần nhất. Dùng `DATE_FORMAT(appdate, '%Y-%m')` để nhóm. `DATE_SUB(NOW(), INTERVAL 12 MONTH)` để lấy dữ liệu 12 tháng gần nhất. Kết quả dùng `array_column()` để tách riêng mảng labels (tháng) và mảng data (số lượng). |
| **Bước 6: Query doanh thu theo tháng** | Hệ thống | Không | JSON doanh thu 12 tháng | Tính `SUM(docFees)` theo tháng từ lịch hẹn đã hoàn thành (`userStatus = 1 AND doctorStatus = 1`). `CAST(docFees AS DECIMAL(10,2))` để đảm bảo tính toán số thập phân chính xác, phòng trường hợp cột này đang lưu kiểu VARCHAR. |
| **Bước 7: Query tỷ lệ lịch hẹn** | Hệ thống | Không | JSON `{success, cancelled}` | Dùng `SUM(CASE WHEN ... THEN 1 ELSE 0 END)` để đếm có điều kiện trong một lần query duy nhất — hiệu quả hơn chạy 2 query riêng. Lịch thành công: cả `userStatus` và `doctorStatus` đều bằng 1. Lịch hủy: một trong hai bằng 0. |
| **Bước 8: Query top 5 bác sĩ** | Hệ thống | Không | JSON 5 bác sĩ nhiều lịch nhất | JOIN `doctb` LEFT JOIN `appointmenttb`, GROUP BY `doctor_id`, HAVING `cnt > 0` để loại bác sĩ chưa có lịch nào, ORDER BY DESC, LIMIT 5. Kết quả là tên bác sĩ làm labels và số lượng lịch làm data. |
| **Bước 9: JavaScript render Chart.js** | JavaScript | JSON data | Biểu đồ hiển thị trên canvas | Sau khi `fetch()` trả về response, `await res.json()` parse về object JS. Tạo `new Chart(ctx, config)` với `data.labels` và `data.data`. Mỗi canvas element có một Chart instance riêng biệt. |

---

## LUỒNG 4.2: DOCTOR DASHBOARD

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session bác sĩ** | Hệ thống | `$_SESSION['doctor_id']` | Tiếp tục hoặc redirect | Doctor middleware. Lấy cả `$doctor_id` từ `$_SESSION['doctor_id']` và `$doctor_fullname` từ `$_SESSION['dname']` vì 2 bảng dùng 2 cách lưu khác nhau. |
| **Bước 2: Query 4 thống kê cá nhân** | Hệ thống | `$doctor_id`, `$doctor_fullname` | 4 con số cá nhân của bác sĩ | Sự khác biệt so với admin: mỗi query đều có `WHERE doctor = ?` hoặc `WHERE doctor_id = ?`. Lịch hẹn hôm nay: `WHERE doctor = ? AND appdate = CURDATE()`. Tổng bệnh nhân: `COUNT(DISTINCT pid)` với `WHERE doctor = ?`. Đơn thuốc: `WHERE doctor = $_SESSION['dname']` vì `prestb.doctor` lưu tên. Bệnh án: `WHERE doctor_id = $_SESSION['doctor_id']` vì `medical_records.doctor_id` lưu ID. |
| **Bước 3: AJAX biểu đồ cá nhân** | JavaScript | `?action=doctor_personal_stats&did={id}` | JSON lịch hẹn 12 tháng của bác sĩ này | Tương tự admin nhưng query thêm `WHERE doctor = ?` để chỉ lấy dữ liệu của bác sĩ đang đăng nhập. Frontend truyền `doctor_id` qua query string, server bind vào prepared statement. |

---

## Điểm khác biệt Admin vs Doctor

| Khía cạnh | Admin | Doctor |
|-----------|-------|--------|
| Điều kiện WHERE | Không có filter theo user | Bắt buộc `WHERE doctor = ?` hoặc `WHERE doctor_id = ?` |
| Phạm vi xem | Toàn bộ hệ thống | Chỉ dữ liệu cá nhân |
| Biểu đồ | patients_by_month, revenue_stats, appointment_ratios, top_doctors | doctor_personal_stats |
| Lưu ý đặc biệt | `SUM(docFees)` cần CAST về DECIMAL | `prestb.doctor` dùng tên, `medical_records.doctor_id` dùng ID |

## Quy tắc nghiệp vụ
- Mỗi biểu đồ là 1 AJAX call riêng → trang không bị block khi load
- Tất cả API response cùng format `{"labels": [...], "data": [...]}` để JavaScript xử lý thống nhất
- `array_column($rows, 'month')` và `array_column($rows, 'cnt')` để tách labels/data sau khi `fetchAll()`
