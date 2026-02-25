# 🏥 Global Hospital Management System

## ✨ Hệ thống quản lý bệnh viện hiện đại

### 🚀 Tính năng chính

-   ✅ **PDO Database** - Bảo mật với prepared statements
-   ✅ **3 Dashboard hiện đại** - Patient, Doctor, Admin
-   ✅ **Smart Login** - Tự động phát hiện vai trò
-   ✅ **Responsive Design** - Mobile-friendly
-   ✅ **Modern UI** - Gradient, animations, icons

### 📂 Cấu trúc dự án

```
Project-Booking-Medical-Appointment/
├── include/
│   ├── config.php         # PDO Database connection ⭐
│   ├── functions.php      # Core functions
│   └── pdo-helpers.php    # Helper functions
│
├── dashboard/             # Modern dashboards
│   ├── patient/
│   ├── doctor/
│   └── admin/
│
├── assets/css/custom/     # Modern CSS
├── docs/                  # Documentation
├── deprecated/            # Old files
│
├── index.php              # Đăng ký
├── index1.php             # Đăng nhập
├── login-handler.php      # Authentication
└── START-HERE.html        # Navigation
```

### ⚙️ Cài đặt

1. **Import database**

```bash
mysql -u root -p < myhmsdb.sql
```

2. **Cấu hình database** (`include/config.php`)

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'myhmsdb');
```

3. **Truy cập**

-   Navigation: `START-HERE.html`
-   Đăng ký: `index.php`
-   Đăng nhập: `index1.php`

### 🔧 PDO Usage

```php
// Get connection
$con = getDB();

// Prepared statement
$stmt = $con->prepare("SELECT * FROM patreg WHERE email = :email");
$stmt->execute([':email' => $email]);
$row = $stmt->fetch();
```

### 🎨 Design

-   **Primary**: `#4F46E5` (Indigo)
-   **Secondary**: `#10B981` (Green)
-   **Font**: Inter (Google Fonts)
-   **Style**: Modern gradient, rounded corners

### 🔐 Security

✅ PDO prepared statements  
✅ Password hashing (`password_hash`)  
✅ Input sanitization  
✅ Session authentication  
✅ Error logging

### 📚 Documentation

Xem folder `docs/` để biết thêm chi tiết:

-   `README-NEW.md` - Tài liệu đầy đủ
-   `QUICK-SUMMARY.md` - Tóm tắt nhanh
-   `INSTALLATION-GUIDE.md` - Hướng dẫn cài đặt

---

**Version**: 2.0.0 | **Updated**: January 2026 | **License**: MIT
