# CHỨC NĂNG 3: ĐƠN THUỐC CHI TIẾT (PRESCRIPTION MANAGEMENT)

## Mô tả tổng quan
Kê đơn là thao tác phức tạp nhất vì liên quan đến **2 bảng**: `prestb` (thông tin đơn tổng) và `prescription_medications` (mỗi loại thuốc 1 row). Hệ thống dùng **Transaction** để đảm bảo nguyên tắc "tất cả hoặc không có gì" — nếu thêm thuốc thất bại thì đơn thuốc chính cũng bị hủy, không để lại dữ liệu không hoàn chỉnh.

---

## LUỒNG 3.1: BÁC SĨ KÊ ĐƠN THUỐC

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session bác sĩ** | Hệ thống | `$_SESSION['doctor_id']`, `$_SESSION['dname']`, `user_type` | Tiếp tục hoặc redirect | Bác sĩ phải đăng nhập mới được kê đơn. Hệ thống kiểm tra session và lấy cả `$_SESSION['dname']` (tên đầy đủ) vì bảng `prestb` lưu tên bác sĩ dạng chuỗi, không phải ID. Đây là điểm đặc biệt cần chú ý: cột `doctor` trong `prestb` là `VARCHAR`, không phải `INT FK`. |
| **Bước 2: Auto-fill thông tin từ trang lịch hẹn** | Hệ thống | GET: `pid`, `fname`, `lname`, `appdate`, `apptime` | Biến PHP được gán sẵn | Khi bác sĩ click "Kê đơn" từ trang lịch hẹn, hệ thống nhận thông tin bệnh nhân qua URL query string. Mục đích là tiết kiệm thao tác: bác sĩ không phải chọn lại bệnh nhân. Tuy nhiên đây chỉ là pre-fill gợi ý, bác sĩ vẫn có thể thay đổi trước khi submit. Dùng `htmlspecialchars()` khi lấy từ GET để tránh XSS nếu giá trị được echo ra form. |
| **Bước 3: Nhận POST và validate cơ bản** | Hệ thống | POST: `pid`, `disease`, `allergy`, `treatment_duration`, `general_notes` | Dữ liệu sạch | `pid` là bắt buộc — không có bệnh nhân thì không thể kê đơn. `disease` (chẩn đoán) cũng bắt buộc — đây là thông tin cốt lõi của đơn thuốc. Các trường còn lại là tùy chọn nên dùng `?? ''` để tránh lỗi undefined index. |
| **Bước 4: Nhận và kiểm tra mảng thuốc** | Hệ thống | POST: `medications[]` — mảng các thuốc | Mảng `$medications` hợp lệ | Đây là bước quan trọng: hệ thống nhận một **mảng** gồm nhiều thuốc, mỗi thuốc có `name`, `dosage`, `frequency`, `duration`. Trước tiên kiểm tra mảng không được rỗng — đơn thuốc phải có ít nhất 1 thuốc. Sau đó loop qua từng thuốc để kiểm tra các trường bắt buộc. Nếu thiếu bất kỳ thuốc nào không hợp lệ, dừng ngay trước khi vào transaction. |
| **Bước 5: Khởi động Transaction** | Hệ thống | — | Transaction active | Gọi `$pdo->beginTransaction()` để bắt đầu. Từ thời điểm này, mọi thay đổi trong DB chỉ là tạm thời — chưa được lưu thật. Chỉ khi gọi `commit()` thì mới lưu vĩnh viễn. Nếu có lỗi bất kỳ ở bước nào sau đây, gọi `rollBack()` để hoàn tác toàn bộ. |
| **Bước 6: INSERT đơn thuốc chính vào `prestb`** | Hệ thống | Thông tin đơn thuốc tổng | Row mới trong `prestb` + lấy `$presc_id` | INSERT thông tin đơn thuốc chính vào bảng `prestb`. Ngay sau execute, gọi `$pdo->lastInsertId()` để lấy ID của row vừa tạo — đây sẽ là `prescription_id` cho các thuốc ở bước sau. Điểm quan trọng: `lastInsertId()` phải được gọi **ngay lập tức** sau execute, trước bất kỳ query nào khác, vì nó chỉ trả về ID của thao tác INSERT gần nhất. |
| **Bước 7: INSERT từng thuốc vào `prescription_medications`** | Hệ thống | `$presc_id`, mảng `$medications` | N rows trong bảng `prescription_medications` | Chuẩn bị Prepared Statement **một lần duy nhất** bên ngoài vòng lặp, rồi loop qua từng thuốc và chỉ thay đổi tham số `execute()`. Cách này hiệu quả hơn nhiều so với `prepare()` trong vòng lặp vì DB chỉ phân tích cú pháp SQL một lần. Nếu bất kỳ `execute()` nào throw exception, sẽ nhảy ngay vào `catch` để rollback. |
| **Bước 8: Commit — xác nhận lưu vĩnh viễn** | Hệ thống | — | Tất cả INSERT được lưu thật | Nếu toàn bộ bước 6 và 7 thành công mà không có exception, gọi `$pdo->commit()`. Lúc này tất cả thay đổi mới thực sự được ghi vào DB. Sau đó redirect sang trang danh sách đơn thuốc. |
| **Bước 9: Rollback khi có lỗi** | Hệ thống | Exception | Hủy toàn bộ — DB không đổi | Nếu có bất kỳ lỗi nào trong transaction (lỗi DB, exception...), `catch` block chạy `$pdo->rollBack()`. Điều này đảm bảo: không có đơn thuốc nào được lưu mà không có thuốc kèm theo, và không có thuốc nào được lưu mà không thuộc đơn nào. DB trở về trạng thái trước khi transaction bắt đầu. |

---

## LUỒNG 3.2: XEM DANH SÁCH ĐƠN THUỐC (SEARCH + PAGINATION)

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Xây dựng điều kiện tìm kiếm động** | Hệ thống | GET: `search` | `$search_condition` (chuỗi SQL) và `$params` (mảng tham số) | Hệ thống xây dựng điều kiện WHERE động. Ban đầu `$params` chỉ có `[$doctor_fullname]` — điều kiện cơ bản chỉ lấy đơn của bác sĩ mình. Nếu có `search` trong GET, hệ thống thêm điều kiện `AND (fname LIKE ? OR lname LIKE ? OR disease LIKE ?)` vào chuỗi SQL và thêm 3 giá trị tương ứng vào `$params`. Kỹ thuật này cho phép tái dùng cùng `$params` cho cả query đếm và query lấy dữ liệu — đảm bảo nhất quán. |
| **Bước 2: Đếm tổng bản ghi** | Hệ thống | `$params` (cùng điều kiện với query data) | `$total_records` | Trước khi lấy dữ liệu trang, hệ thống phải biết tổng số bản ghi để tính số trang. Query `SELECT COUNT(*)` với **cùng điều kiện WHERE và cùng params** với query data. Dùng `fetchColumn()` để lấy trực tiếp giá trị số đếm mà không cần `fetch()` rồi truy cập vào mảng. |
| **Bước 3: Tính toán các giá trị phân trang** | Hệ thống | GET: `page_num`, `$total_records` | `$page_num`, `$offset`, `$total_pages` | `page_num` nhận từ GET, dùng `max(1, intval())` để vừa ép kiểu integer vừa đảm bảo không âm. `offset = (page_num - 1) * per_page` là công thức tính vị trí bắt đầu: trang 1 lấy từ row 0, trang 2 từ row 10, trang 3 từ row 20... `total_pages = ceil(total / per_page)` — dùng `ceil` để làm tròn lên, đảm bảo phần dư không bị mất. |
| **Bước 4: Query dữ liệu trang hiện tại** | Hệ thống | `$params`, `$offset` | `$prescriptions` array | Thêm `LIMIT $per_page OFFSET $offset` vào cuối câu SQL. `LIMIT` và `OFFSET` là số nguyên thuần túy nên có thể nhúng thẳng vào chuỗi SQL mà không lo injection. Kết quả là đúng 10 bản ghi tương ứng với trang hiện tại. |
| **Bước 5: Build link pagination giữ nguyên search** | Hệ thống | `$search_query`, `page_num` | Chuỗi URL cho mỗi trang | Khi chuyển trang, tham số `search` phải được giữ nguyên trong URL, nếu không kết quả tìm kiếm sẽ biến mất. Dùng `urlencode($search_query)` để đảm bảo ký tự đặc biệt (khoảng trắng, dấu tiếng Việt...) được encode đúng trong URL. |

---

## LUỒNG 3.3: XEM CHI TIẾT ĐƠN THUỐC

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Validate ID từ URL** | Hệ thống | GET: `id` | `$presc_id` hợp lệ | `intval($_GET['id'] ?? 0)` — nếu không có hoặc không phải số, kết quả là 0. Kiểm tra `> 0` rồi redirect nếu không hợp lệ. Đây là bước cơ bản nhất trước khi query DB. |
| **Bước 2: Lấy thông tin đơn thuốc chính** | Hệ thống | `$presc_id` | `$prescription` row | Query JOIN `prestb` với `patreg` để lấy thông tin bệnh nhân kèm theo. Nếu không tìm thấy (kết quả `fetch()` là `false`), redirect về danh sách — không hiển thị trang trắng hay lỗi DB. |
| **Bước 3: Kiểm tra quyền xem theo role** | Hệ thống | `$prescription`, session | Tiếp tục hoặc lỗi 403 | Bác sĩ: kiểm tra `$prescription['doctor'] === $_SESSION['dname']` — chỉ bác sĩ đã kê mới được xem. Bệnh nhân: kiểm tra `$prescription['pid'] == $_SESSION['pid']` — chỉ xem đơn của mình. Admin: không cần kiểm tra thêm. Nếu vi phạm: `http_response_code(403); exit()`. |
| **Bước 4: Lấy danh sách thuốc** | Hệ thống | `$presc_id` | `$medications` array | `SELECT * FROM prescription_medications WHERE prescription_id = ? ORDER BY id ASC` — sắp xếp theo thứ tự bác sĩ nhập để hiển thị nhất quán. |

## LUỒNG 3.4: BỆNH NHÂN XEM ĐƠN THUỐC

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra session** | Hệ thống | `$_SESSION['pid']` | Tiếp tục hoặc redirect | Patient middleware. |
| **Bước 2: Query đơn thuốc của mình** | Hệ thống | `$_SESSION['pid']` | Danh sách đơn thuốc | `WHERE pr.pid = $_SESSION['pid']` — `pid` cứng từ session. Đặc biệt: JOIN với `doctb` theo `pr.doctor = d.fullname` (không phải `d.id`) vì cột `prestb.doctor` lưu tên chuỗi. Đây là điểm khác biệt quan trọng so với hầu hết các bảng khác dùng FK là ID. |

---

## Quy tắc nghiệp vụ

| Nguyên tắc | Lý do |
|-----------|-------|
| Transaction bắt buộc | Ngăn đơn thuốc không có thuốc (dữ liệu không nhất quán) |
| `lastInsertId()` ngay sau INSERT | Giá trị này chỉ có sau query gần nhất |
| Prepare ngoài vòng lặp | Hiệu năng tốt hơn — DB phân tích SQL chỉ 1 lần |
| `prestb.doctor` = tên chuỗi không phải ID | Thiết kế cũ — cần JOIN theo `d.fullname` không phải `d.id` |
| `AND doctor = ?` khi xóa | Ngăn xóa đơn của bác sĩ khác |
