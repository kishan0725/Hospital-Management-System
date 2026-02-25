<?php
session_start();
require_once('../../config.php');
require_once('../../includes/messages.php');
require_once('../../includes/functions.php');

if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin = $_SESSION['username'];


$total_medicines = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity > 0 AND quantity <= 10")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity = 0")->fetchColumn();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medicine'])) {
    $name = trim($_POST['medicine_name']);
    $category = trim($_POST['category']);
    $specialty = trim($_POST['specialty'] ?? '');
    $dosage = trim($_POST['dosage']);
    $unit_price = floatval($_POST['unit_price']);
    $quantity = intval($_POST['quantity']);
    $expiry_date = $_POST['expiry_date'];
    $manufacturer = trim($_POST['manufacturer']);
    $description = trim($_POST['description']);

    if (empty($name) || empty($category) || $quantity < 0) {
        redirectWithMessage('medicine-inventory.php', 'error', 'Vui lòng điền đầy đủ thông tin!');
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO medicines (name, category, dosage_form, unit_price, quantity, expiry_date, manufacturer, description, specialty, created_by, created_at)
            VALUES (:name, :category, :dosage, :unit_price, :quantity, :expiry_date, :manufacturer, :description, :specialty, :created_by, NOW())
        ");
        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':dosage' => $dosage,
            ':unit_price' => $unit_price,
            ':quantity' => $quantity,
            ':expiry_date' => $expiry_date,
            ':manufacturer' => $manufacturer,
            ':description' => $description,
            ':specialty' => $specialty,
            ':created_by' => $admin
        ]);
        redirectWithMessage('medicine-inventory.php', 'success', 'Thêm thuốc thành công!');
    } catch (PDOException $e) {
        redirectWithMessage('medicine-inventory.php', 'error', 'Lỗi: ' . $e->getMessage());
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_medicine'])) {
    $id = intval($_POST['medicine_id']);
    $name = trim($_POST['medicine_name']);
    $category = trim($_POST['category']);
    $specialty = trim($_POST['specialty'] ?? '');
    $dosage = trim($_POST['dosage']);
    $unit_price = floatval($_POST['unit_price']);
    $quantity = intval($_POST['quantity']);
    $expiry_date = $_POST['expiry_date'];
    $manufacturer = trim($_POST['manufacturer']);
    $description = trim($_POST['description']);

    if (empty($name) || empty($category) || $quantity < 0 || !$id) {
        redirectWithMessage('medicine-inventory.php', 'error', 'Vui lòng điền đầy đủ thông tin!');
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET name = :name, category = :category, dosage_form = :dosage, 
                unit_price = :unit_price, quantity = :quantity, expiry_date = :expiry_date,
                manufacturer = :manufacturer, description = :description, specialty = :specialty, updated_at = NOW()
            WHERE id = :id
        ");
        $result = $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':dosage' => $dosage,
            ':unit_price' => $unit_price,
            ':quantity' => $quantity,
            ':expiry_date' => $expiry_date,
            ':manufacturer' => $manufacturer,
            ':description' => $description,
            ':specialty' => $specialty,
            ':id' => $id
        ]);
        if ($result && $stmt->rowCount() > 0) {
            redirectWithMessage('medicine-inventory.php', 'success', 'Cập nhật thành công!');
        } else {
            redirectWithMessage('medicine-inventory.php', 'error', 'Không tìm thấy thuốc để cập nhật!');
        }
    } catch (PDOException $e) {
        redirectWithMessage('medicine-inventory.php', 'error', 'Lỗi: ' . $e->getMessage());
    }
}


if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = :id");
        $stmt->execute([':id' => $id]);
        redirectWithMessage('medicine-inventory.php', 'success', 'Xóa thành công!');
    } catch (PDOException $e) {
        redirectWithMessage('medicine-inventory.php', 'error', 'Lỗi: ' . $e->getMessage());
    }
}


$edit_medicine = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = :id");
    $stmt->execute([':id' => $edit_id]);
    $edit_medicine = $stmt->fetch(PDO::FETCH_ASSOC);
}


$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$specialty_filter = $_GET['specialty'] ?? '';
$expiry_filter = $_GET['expiry'] ?? '';
$search_condition = '';
$params = [];

if ($search_query) {
    $search_condition .= " AND (name LIKE :search1 OR manufacturer LIKE :search2)";
    $params[':search1'] = "%$search_query%";
    $params[':search2'] = "%$search_query%";
}
if ($category_filter) {
    $search_condition .= " AND category = :category";
    $params[':category'] = $category_filter;
}
if ($specialty_filter) {
    $search_condition .= " AND specialty = :specialty";
    $params[':specialty'] = $specialty_filter;
}
if ($expiry_filter === 'expiring') {
    $search_condition .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
}


$page_num = max(1, intval($_GET['page_num'] ?? 1));
$records_per_page = 20;
$offset = ($page_num - 1) * $records_per_page;


$count_sql = "SELECT COUNT(*) FROM medicines WHERE 1=1 $search_condition";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);


$sql = "SELECT * FROM medicines WHERE 1=1 $search_condition ORDER BY specialty, category, name LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);


$medicines_by_specialty = [];
foreach ($medicines as $med) {
    $spec = $med['specialty'] ?: 'Chung';
    if (!isset($medicines_by_specialty[$spec])) {
        $medicines_by_specialty[$spec] = [];
    }
    $medicines_by_specialty[$spec][] = $med;
}

$categories = ['Thuốc giảm đau', 'Kháng sinh', 'Vitamin', 'Thuốc tim mạch', 'Thuốc tiêu hóa', 'Thuốc da liễu', 'Thuốc thần kinh', 'Thuốc hô hấp', 'Khác'];

$specialties = [
    'Pediatrics' => 'Nhi khoa',
    'Cardiology' => 'Tim mạch',
    'Dermatology' => 'Da liễu',
    'Gastroenterology' => 'Tiêu hóa',
    'Neurology' => 'Thần kinh',
    'Orthopedics' => 'Chấn thương chỉnh hình',
    'ENT' => 'Tai Mũi Họng',
    'Ophthalmology' => 'Mắt',
    'Obstetrics_Gynecology' => 'Sơ sinh - Phụ khoa',
    'Oncology' => 'Ung thư',
    'Pulmonology' => 'Phổi',
    'Endocrinology' => 'Nội tiết',
    'Nephrology' => 'Thận',
    'Psychiatry' => 'Tâm thần',
    'Rheumatology' => 'Thấp khớp',
    'Infectious_Diseases' => 'Bệnh truyền nhiễm',
    'Immunology' => 'Miễn dịch',
    'Hematology' => 'Huyết học',
    'Neurosurgery' => 'Phẫu thuật thần kinh',
    'Urology' => 'Tiết niệu',
    'Colorectal_Surgery' => 'Phẫu thuật đại trực tràng'
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Kho thuốc - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, rgba(254, 243, 199, 0.85), rgba(249, 115, 22, 0.85)), url('../../images/ngua.png');
            background-size: cover;
            background-attachment: fixed;
        }

        .medicine-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title-section {
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title-section h1 {
            color: #065f46;
            font-weight: 700;
            font-size: 28px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stats-pills {
            display: flex;
            gap: 15px;
        }

        .stat-pill {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-left: 4px solid #10b981;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-pill-value {
            font-size: 20px;
            font-weight: 700;
            color: #065f46;
        }

        .stat-pill-label {
            font-size: 11px;
            color: #059669;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 5px;
        }

        .card-medicine {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-medicine .card-header {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            border: none;
            padding: 18px 24px;
        }

        .card-medicine .card-header h5 {
            color: white;
            margin: 0;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-medicine .card-body {
            padding: 24px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .form-col {
            flex: 1;
            min-width: 140px;
        }

        .form-col label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-col input,
        .form-col select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 7px;
            font-size: 13px;
            transition: all 0.3s;
            width: 100%;
        }

        .form-col input:focus,
        .form-col select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            outline: none;
        }

        .btn-submit {
            background: linear-gradient(135deg, #047857, #10b981);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #065f46, #059669);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 95, 70, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-reset {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-reset:hover {
            background: #4b5563;
            transform: translateY(-2px);
            color: white;
        }

        .filter-group {
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group .form-col {
            min-width: 140px;
        }

        .table-medicines {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .table-medicines table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .table-medicines thead {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        }

        .table-medicines th {
            color: #065f46;
            font-weight: 700;
            border: none;
            padding: 14px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .table-medicines td {
            padding: 14px;
            border-color: #e5e7eb;
            vertical-align: middle;
        }

        .table-medicines tbody tr {
            transition: all 0.3s;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-medicines tbody tr:hover {
            background: #f0fdf4;
        }

        .table-medicines tbody tr:last-child {
            border-bottom: none;
        }

        .category-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            text-align: center;
            display: inline-block;
        }

        .status-in-stock {
            background: #d1fae5;
            color: #065f46;
        }

        .status-low-stock {
            background: #fef3c7;
            color: #92400e;
        }

        .status-out-of-stock {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            background: #fef08a;
            color: #854d0e;
        }

        .btn-edit:hover {
            background: #fde047;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-delete:hover {
            background: #fecaca;
            transform: translateY(-2px);
        }

        .pagination {
            margin-top: 20px;
            justify-content: center;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 4px;
            border: 1px solid #e5e7eb;
            color: #065f46;
            padding: 8px 12px;
        }

        .pagination .page-link:hover {
            background: #f0fdf4;
            border-color: #10b981;
            color: #065f46;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #065f46, #059669);
            border-color: #10b981;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 16px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            background: rgba(6, 95, 70, 0.8);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: #ffffff;
            background: rgba(5, 150, 105, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(6, 95, 70, 0.4);
        }
    </style>
</head>

<body>
    <div class="medicine-container">
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại bảng điều khiển</a>

        
        <div class="page-title-section">
            <h1><i class="fas fa-pills"></i>Quản lý Kho thuốc</h1>
            <div class="stats-pills">
                <div class="stat-pill">
                    <div class="stat-pill-value"><?php echo $total_medicines; ?></div>
                    <div class="stat-pill-label">Tổng thuốc</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-value"><?php echo $low_stock; ?></div>
                    <div class="stat-pill-label">Sắp hết</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-value"><?php echo $out_of_stock; ?></div>
                    <div class="stat-pill-label">Hết hàng</div>
                </div>
            </div>
        </div>

        <?php displayMessage(); ?>

        
        <div class="card-medicine">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle"></i>Thêm Thuốc Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-col" style="flex: 2;">
                            <label><i class="fas fa-capsules"></i>Tên thuốc *</label>
                            <input type="text" name="medicine_name" class="form-control" placeholder="Nhập tên thuốc" required>
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-tags"></i>Danh mục *</label>
                            <select name="category" class="form-control" required>
                                <option value="">-- Chọn --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-hospital"></i>Chuyên khoa</label>
                            <select name="specialty" class="form-control">
                                <option value="">-- Chung --</option>
                                <?php foreach ($specialties as $key => $name): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-boxes"></i>Số lượng *</label>
                            <input type="number" name="quantity" class="form-control" min="0" placeholder="0" required>
                        </div>
                        <div class="form-col" style="min-width: auto;">
                            <button type="submit" name="add_medicine" value="1" class="btn-submit">
                                <i class="fas fa-plus"></i>Thêm
                            </button>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 12px;">
                        <div class="form-col">
                            <label><i class="fas fa-prescription-bottle"></i>Dạng bào chế</label>
                            <input type="text" name="dosage" class="form-control" placeholder="Viên, nước...">
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-industry"></i>Hãng sản xuất</label>
                            <input type="text" name="manufacturer" class="form-control" placeholder="Tên hãng">
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-money-bill-wave"></i>Giá (VNĐ)</label>
                            <input type="number" name="unit_price" class="form-control" min="0" step="1000" placeholder="0">
                        </div>
                        <div class="form-col">
                            <label><i class="fas fa-calendar-times"></i>Hạn sử dụng</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                        <div class="form-col" style="flex: 2;">
                            <label><i class="fas fa-align-left"></i>Mô tả</label>
                            <input type="text" name="description" class="form-control" placeholder="Mô tả thêm">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <?php if ($edit_medicine): ?>
            <div class="card-medicine" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <h5><i class="fas fa-edit"></i>Chỉnh sửa Thuốc</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="medicine_id" value="<?php echo $edit_medicine['id']; ?>">

                        <div class="form-row">
                            <div class="form-col" style="flex: 2;">
                                <label><i class="fas fa-capsules"></i>Tên thuốc *</label>
                                <input type="text" name="medicine_name" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_medicine['name']); ?>" required>
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-tags"></i>Danh mục *</label>
                                <select name="category" class="form-control" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"
                                            <?php echo ($edit_medicine['category'] == $cat) ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-hospital"></i>Chuyên khoa</label>
                                <select name="specialty" class="form-control">
                                    <option value="">-- Chung --</option>
                                    <?php foreach ($specialties as $key => $name): ?>
                                        <option value="<?php echo $key; ?>"
                                            <?php echo ($edit_medicine['specialty'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-boxes"></i>Số lượng *</label>
                                <input type="number" name="quantity" class="form-control" min="0" required
                                    value="<?php echo $edit_medicine['quantity']; ?>">
                            </div>
                            <div class="form-col" style="min-width: auto;">
                                <button type="submit" name="update_medicine" value="1" class="btn-submit" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fas fa-save"></i>Cập nhật
                                </button>
                            </div>
                            <div class="form-col" style="min-width: auto;">
                                <a href="medicine-inventory.php" class="btn-reset"><i class="fas fa-times"></i>Hủy</a>
                            </div>
                        </div>

                        <div class="form-row" style="margin-top: 12px;">
                            <div class="form-col">
                                <label><i class="fas fa-prescription-bottle"></i>Dạng bào chế</label>
                                <input type="text" name="dosage" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_medicine['dosage_form']); ?>">
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-industry"></i>Hãng sản xuất</label>
                                <input type="text" name="manufacturer" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_medicine['manufacturer']); ?>">
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-money-bill-wave"></i>Giá (VNĐ)</label>
                                <input type="number" name="unit_price" class="form-control" min="0" step="1000"
                                    value="<?php echo $edit_medicine['unit_price']; ?>">
                            </div>
                            <div class="form-col">
                                <label><i class="fas fa-calendar-times"></i>Hạn sử dụng</label>
                                <input type="date" name="expiry_date" class="form-control"
                                    value="<?php echo $edit_medicine['expiry_date']; ?>">
                            </div>
                            <div class="form-col" style="flex: 2;">
                                <label><i class="fas fa-align-left"></i>Mô tả</label>
                                <input type="text" name="description" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_medicine['description']); ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="filter-group">
            <form method="GET" style="width: 100%; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-col" style="flex: 2;">
                    <label><i class="fas fa-search"></i>Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Tên thuốc, hãng sản xuất..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="form-col">
                    <label><i class="fas fa-filter"></i>Danh mục</label>
                    <select name="category" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($category_filter == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label><i class="fas fa-hospital"></i>Chuyên khoa</label>
                    <select name="specialty" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($specialties as $key => $name): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($specialty_filter == $key) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label><i class="fas fa-exclamation-triangle"></i>Hạn SD</label>
                    <select name="expiry" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <option value="expiring" <?php echo ($expiry_filter == 'expiring') ? 'selected' : ''; ?>>Sắp hết hạn</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-search"></i>Tìm</button>
                <a href="medicine-inventory.php" class="btn-reset"><i class="fas fa-redo"></i>Reset</a>
            </form>
        </div>

        
        <?php if (empty($medicines)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Không có thuốc nào phù hợp với tiêu chí tìm kiếm</p>
            </div>
        <?php else: ?>
            <?php foreach ($medicines_by_specialty as $specialty => $meds): ?>
                <div class="card-medicine">
                    <div class="card-header">
                        <h5><i class="fas fa-hospital-alt"></i>
                            <?php echo isset($specialties[$specialty]) ? $specialties[$specialty] : $specialty; ?>
                            <span class="badge badge-success ml-2"><?php echo count($meds); ?> thuốc</span>
                        </h5>
                    </div>
                    <div class="table-medicines">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 28%;">Tên thuốc</th>
                                        <th style="width: 14%;">Danh mục</th>
                                        <th style="width: 10%;">Dạng BC</th>
                                        <th style="width: 12%;">Giá</th>
                                        <th style="width: 8%;">SL</th>
                                        <th style="width: 12%;">Hạn SD</th>
                                        <th style="width: 12%;">Trạng thái</th>
                                        <th style="width: 10%; text-align: center;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($meds as $med): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($med['name']); ?></strong></td>
                                            <td><span class="category-badge"><?php echo $med['category']; ?></span></td>
                                            <td><?php echo htmlspecialchars($med['dosage_form'] ?: 'N/A'); ?></td>
                                            <td><?php echo number_format($med['unit_price'], 0, ',', '.'); ?> đ</td>
                                            <td><strong><?php echo $med['quantity']; ?></strong></td>
                                            <td><?php echo $med['expiry_date'] ? date('d/m/Y', strtotime($med['expiry_date'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($med['quantity'] == 0): ?>
                                                    <span class="status-badge status-out-of-stock">Hết hàng</span>
                                                <?php elseif ($med['quantity'] <= 10): ?>
                                                    <span class="status-badge status-low-stock">Sắp hết</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-in-stock">Còn hàng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <div class="action-btns">
                                                    <a href="?edit_id=<?php echo $med['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                                    <a href="?delete_id=<?php echo $med['id']; ?>" class="btn-delete"
                                                        onclick="return confirm('Xóa thuốc này?')"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination">
                        <?php if ($page_num > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_num=1<?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $specialty_filter ? '&specialty=' . urlencode($specialty_filter) : ''; ?><?php echo $expiry_filter ? '&expiry=' . $expiry_filter : ''; ?>">
                                    « Đầu
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_num=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $specialty_filter ? '&specialty=' . urlencode($specialty_filter) : ''; ?><?php echo $expiry_filter ? '&expiry=' . $expiry_filter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page_num < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_num=<?php echo $total_pages; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $specialty_filter ? '&specialty=' . urlencode($specialty_filter) : ''; ?><?php echo $expiry_filter ? '&expiry=' . $expiry_filter : ''; ?>">
                                    Cuối »
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>