# CHỨC NĂNG 6: DIỄN ĐÀN (FORUM)

## Mô tả tổng quan
Forum có 4 tính năng chính: đăng bài, bình luận phân cấp (nested comments), toggle like và admin quản lý. Điểm kỹ thuật đáng chú ý: nested comments dùng cột `parent_id` tự tham chiếu và được tổ chức bằng PHP loop sau khi fetch flat từ DB; toggle like dùng logic kiểm tra-rồi-insert-hoặc-delete; ORDER BY trong tìm kiếm phải whitelist để tránh SQL injection.

---

## LUỒNG 6.1: TẠO BÀI VIẾT

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra đăng nhập** | Hệ thống | `$_SESSION['user_type']` | Tiếp tục hoặc redirect | Khác với các chức năng khác chỉ cần 1 loại user, Forum cần kiểm tra 3 loại cùng lúc. Dùng `in_array($user_type, ['patient', 'doctor', 'admin'])` thay vì viết 3 điều kiện OR riêng biệt. Guest (không có session) sẽ không thỏa điều kiện này và bị redirect về login. |
| **Bước 2: Nhận và làm sạch data** | Hệ thống | POST: `title`, `content`, `tags`, `category`, `privacy` | Biến PHP sạch | `trim()` tất cả các trường text. Dùng `mb_strlen()` thay vì `strlen()` để đếm đúng ký tự nhiều byte như tiếng Việt — `strlen('à')` trả về 2 (bytes) nhưng `mb_strlen('à', 'UTF-8')` trả về 1 (ký tự). |
| **Bước 3: Validate độ dài tiêu đề và nội dung** | Hệ thống | `$title`, `$content` | Lỗi hoặc tiếp tục | Tiêu đề yêu cầu 10–255 ký tự, nội dung tối thiểu 20 ký tự. Các giới hạn này tránh bài viết quá ngắn không có giá trị. Mỗi vi phạm cho thông báo lỗi riêng biệt. |
| **Bước 4: Validate quyền tạo category** | Hệ thống | `$category`, `$_SESSION['user_type']` | Lỗi hoặc tiếp tục | Category `'announcement'` (thông báo chính thức) chỉ Admin được tạo. Hệ thống kiểm tra: nếu category là announcement mà user_type không phải admin → lỗi 403. Dù phía client đã ẩn option này khỏi dropdown, server vẫn phải kiểm tra vì người dùng có thể tự thêm option bằng developer tools. |
| **Bước 5: Whitelist privacy** | Hệ thống | `$privacy` từ POST | Giá trị an toàn | Chỉ chấp nhận `'public'` hoặc `'private'`. Dùng: `$privacy = in_array($privacy, ['public', 'private']) ? $privacy : 'public'` — nếu giá trị không hợp lệ thì mặc định về public thay vì báo lỗi. |
| **Bước 6: Xác định user_id từ session** | Hệ thống | `$_SESSION` | `$user_id`, `$user_type` | 3 loại user lưu ID ở 3 key session khác nhau: Patient dùng `$_SESSION['pid']`, Doctor dùng `$_SESSION['doctor_id']`, Admin không có ID riêng. Dùng switch/match để xác định `$user_id` đúng theo `$user_type`. |
| **Bước 7: INSERT bài viết** | Hệ thống | Tất cả data validated | `$post_id` | INSERT vào `forum_posts` với `status = 'open'` và `is_pinned = 0` mặc định. `lastInsertId()` để lấy ID bài vừa tạo, dùng cho redirect. |
| **Bước 8: Redirect về trang bài viết** | Hệ thống | `$post_id` | Trang chi tiết bài vừa tạo | `header("Location: post.php?id=$post_id")` — redirect về chính bài vừa tạo để người dùng xác nhận bài đã được đăng thành công. |

---

## LUỒNG 6.2: XEM DANH SÁCH BÀI VIẾT

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Xây dựng WHERE điều kiện động** | Hệ thống | GET: `search`, `category`, `status` | `$where[]` (mảng điều kiện), `$params[]` | Hệ thống không viết cứng một câu WHERE mà xây dựng động theo từng filter. Bắt đầu với `$where = ["p.privacy = 'public'"]` — chỉ hiển thị bài public cho tất cả mọi người. Với mỗi filter có giá trị thì `$where[] = "điều kiện mới"` và `$params[] = "giá trị tương ứng"`. Cuối cùng `implode(' AND ', $where)` để nối thành WHERE string hoàn chỉnh. |
| **Bước 2: Whitelist sort — chống SQL Injection qua ORDER BY** | Hệ thống | GET: `sort` | `$sort_field` an toàn | Không thể dùng Prepared Statement cho cột ORDER BY, nên phải whitelist thủ công. Khai báo mảng `$allowed_sorts` với các giá trị hợp lệ. Lấy `$sort_field = $allowed_sorts[$sort_key] ?? 'default'` — nếu key không tồn tại trong whitelist sẽ lấy giá trị mặc định. Cách này ngăn hacker truyền `ORDER BY (SELECT ...)` vào URL. |
| **Bước 3: Query bài viết với subquery đếm like và comment** | Hệ thống | `$where`, `$sort_field`, `$params` | Mảng `$posts` với like_count và comment_count | Dùng correlated subquery để đếm trong cùng 1 query thay vì query riêng: `(SELECT COUNT(*) FROM forum_likes WHERE target_id = p.id AND target_type = 'post') as like_count`. ORDER BY quan trọng: `p.is_pinned DESC` luôn đứng đầu để bài ghim hiển thị trên cùng, sau đó mới sort theo tiêu chí người dùng chọn. |

---

## LUỒNG 6.3: XEM CHI TIẾT BÀI VIẾT

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Validate và tăng lượt xem** | Hệ thống | GET: `id` | views + 1 | Sau khi validate `post_id > 0`, tăng views bằng `UPDATE forum_posts SET views = views + 1 WHERE id = ?`. Dùng `views = views + 1` thay vì đọc giá trị, cộng thêm 1, rồi ghi lại — cách sau có thể bị race condition nếu nhiều người xem cùng lúc. |
| **Bước 2: Load bài viết kèm counts** | Hệ thống | `$post_id` | `$post` row | Query lấy bài viết với subquery đếm like và comment. Sau fetch nếu kết quả là `false` → redirect về index (bài không tồn tại hoặc đã bị xóa). |
| **Bước 3: Load tất cả comments dạng flat** | Hệ thống | `$post_id` | Mảng flat `$flat_comments` | Query lấy toàn bộ comment của bài với subquery đếm like của từng comment. ORDER BY phức tạp hơn: `ORDER BY COALESCE(c.parent_id, c.id) ASC, c.created_at ASC` — ý nghĩa: nhóm reply theo comment cha (nếu là comment cha thì dùng chính id của nó, nếu là reply thì dùng parent_id), trong cùng nhóm thì sort theo thời gian. |
| **Bước 4: Tổ chức nested comments bằng PHP** | Hệ thống | `$flat_comments` (mảng flat) | `$comments_tree` (mảng 2 cấp) | DB trả về dạng flat — mọi comment đều ngang hàng nhau. PHP loop qua mảng: nếu `parent_id === null` thì đây là comment gốc, thêm vào `$comments_tree[$c['id']]`. Nếu có `parent_id`, đây là reply, thêm vào `$comments_tree[$parent_id]['replies'][]`. Kết quả là mảng comment gốc, mỗi comment gốc có mảng `replies` chứa các reply của nó. |
| **Bước 5: Kiểm tra user đã like bài chưa** | Hệ thống | `$post_id`, `$user_id` | Boolean `$user_liked` | Cần biết trạng thái đã like chưa để hiển thị đúng nút (like/unlike). Chỉ chạy query này nếu user đã đăng nhập (có `$user_id > 0`). `SELECT COUNT(*) FROM forum_likes WHERE target_id = ? AND target_type = 'post' AND user_id = ?` — nếu > 0 là đã like. |

---

## LUỒNG 6.4: GỬI BÌNH LUẬN

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra đăng nhập** | Hệ thống | `$_SESSION['user_type']` | Tiếp tục hoặc redirect | Guest không được comment. Chỉ cần kiểm tra `isset($_SESSION['user_type'])` vì tất cả 3 loại user đều có thể comment. |
| **Bước 2: Xử lý parent_id (phân biệt comment gốc và reply)** | Hệ thống | POST: `content`, `post_id`, `parent_id` | `$parent_id` = null hoặc int | Nếu `$_POST['parent_id']` không được set hoặc rỗng → comment gốc, `$parent_id = null`. Nếu có giá trị → reply, `$parent_id = intval($_POST['parent_id'])`. Sự phân biệt này quyết định cách comment được đặt trong cây phân cấp. |
| **Bước 3: Kiểm tra bài còn mở** | Hệ thống | `$post_id` | Tiếp tục hoặc dừng | Bài có `status = 'closed'` không nhận comment mới. Hệ thống query bài viết, kiểm tra `status !== 'closed'`. Đây là business rule quan trọng — admin có thể đóng bài để ngừng thảo luận. |
| **Bước 4: Validate parent_id** | Hệ thống | `$parent_id`, `$post_id` | `$parent_id` hợp lệ hoặc reset về null | Nếu là reply, kiểm tra comment cha có thực sự thuộc bài này không: `SELECT id FROM forum_comments WHERE id = ? AND post_id = ?`. Nếu không tìm thấy, reset `$parent_id = null` thay vì báo lỗi — tức là coi như comment gốc. Điều này phòng người dùng giả mạo `parent_id` của bài khác. |
| **Bước 5: INSERT comment** | Hệ thống | All validated data | Comment ID mới | `INSERT INTO forum_comments (post_id, user_id, user_type, content, parent_id, created_at) VALUES (...)` — `parent_id` là NULL hoặc integer tùy loại comment. |

---

## LUỒNG 6.5: TOGGLE LIKE / UNLIKE

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra đăng nhập** | Hệ thống | `$_SESSION['user_type']` | Tiếp tục hoặc dừng | Guest không được like. |
| **Bước 2: Whitelist target_type** | Hệ thống | POST: `target_id`, `target_type` | Giá trị an toàn | `target_type` từ POST được dùng trực tiếp trong WHERE clause, nên phải whitelist: `in_array($target_type, ['post', 'comment'])`. Nếu không whitelist, hacker có thể truyền giá trị bất kỳ vào câu SQL. `target_id` phải `intval()` và > 0. |
| **Bước 3: Kiểm tra đã like chưa** | Hệ thống | `$user_id`, `$target_id`, `$target_type` | Boolean `$already_liked` | Query `SELECT COUNT(*) FROM forum_likes WHERE user_id = ? AND target_id = ? AND target_type = ?`. Phải kiểm tra đủ cả 3 điều kiện để phân biệt: cùng user này like post A khác với like comment B. |
| **Bước 4: INSERT hoặc DELETE tùy trạng thái** | Hệ thống | `$already_liked` | Row được thêm hoặc xóa | Đây là **toggle pattern**: nếu chưa like → INSERT row mới vào `forum_likes`; nếu đã like → DELETE row đó. Không cần cờ riêng, chỉ cần kiểm tra xem row có tồn tại không. Sau khi INSERT hoặc DELETE, redirect về trang trước để refresh và cập nhật số like. |

---

## LUỒNG 6.6: ADMIN QUẢN LÝ BÀI VIẾT

| Tiến trình | Tác nhân | Input | Output | Xử lý logic (Process) |
|---|---|---|---|---|
| **Bước 1: Kiểm tra quyền admin** | Hệ thống | `$_SESSION['user_type']` | Cho phép hoặc 403 | Tất cả hành động admin đều phải kiểm tra `user_type === 'admin'` trước. Trả về HTTP 403 (Forbidden) thay vì redirect để phân biệt "không có quyền" với "chưa đăng nhập". |
| **Bước 2: Toggle pin bài** | Admin | POST: `post_id`, `action='toggle_pin'` | `is_pinned` đảo 0↔1 | Dùng `IF()` trong SQL để toggle trong 1 query: `UPDATE forum_posts SET is_pinned = IF(is_pinned = 1, 0, 1) WHERE id = ?`. Cách này tránh race condition so với đọc giá trị cũ rồi đảo. |
| **Bước 3: Toggle khóa/mở bài** | Admin | POST: `action='toggle_status'` | `status` đảo open↔closed | Tương tự: `SET status = IF(status = 'open', 'closed', 'open')`. Bài closed không nhận comment mới (đã kiểm tra ở luồng comment). |
| **Bước 4: Xóa bài và cascade** | Admin | POST: `action='delete_post'` | Bài + comments + likes bị xóa | `DELETE FROM forum_posts WHERE id = ?` — DB tự cascade xóa comments và likes nhờ ràng buộc `ON DELETE CASCADE` đã khai báo trong DDL. Không cần DELETE thủ công từng bảng. |

---

## Phân quyền Forum

| Hành động | Guest | Patient | Doctor | Admin |
|-----------|-------|---------|--------|-------|
| Xem bài public | ✅ | ✅ | ✅ | ✅ |
| Xem bài private | ❌ | Chỉ của mình | Chỉ của mình | ✅ |
| Tạo bài (general/question/discussion) | ❌ | ✅ | ✅ | ✅ |
| Tạo announcement | ❌ | ❌ | ❌ | ✅ |
| Comment (bài còn open) | ❌ | ✅ | ✅ | ✅ |
| Like/Unlike | ❌ | ✅ | ✅ | ✅ |
| Xóa bài/comment của mình | ❌ | ✅ | ✅ | ✅ |
| Pin, Khóa, Xóa mọi bài | ❌ | ❌ | ❌ | ✅ |

## Quy tắc nghiệp vụ

| Vấn đề | Giải pháp | Lý do |
|--------|----------|-------|
| SQL Injection qua ORDER BY | Whitelist `$allowed_sorts` | Không thể dùng Prepared Statement cho ORDER BY |
| `target_type` tùy ý trong like | `in_array()` whitelist | Giá trị được dùng trong WHERE clause |
| Nested comments | `parent_id` nullable, tổ chức bằng PHP loop | DB trả về flat, PHP tổ chức thành cây |
| Race condition khi tăng views | `views = views + 1` atomic | Tránh đọc-cộng-ghi tuần tự |
| Toggle pin/status | `IF(field=val1, val2, val1)` trong 1 query | Tránh race condition khi toggle |
| Xóa bài kéo theo comments, likes | `ON DELETE CASCADE` trong DDL | Tự động, không cần DELETE thủ công |
| Bài đóng không nhận comment | Kiểm tra `status !== 'closed'` trước INSERT | Tránh dữ liệu vi phạm business rule |
