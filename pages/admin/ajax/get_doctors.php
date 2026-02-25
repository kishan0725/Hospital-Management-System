<?php




require_once dirname(__DIR__, 3) . '/config.php';

$search = trim($_POST['search'] ?? '');
$spec   = trim($_POST['spec']   ?? '');

$where  = [];
$params = [];


$joinType = 'LEFT JOIN';
if ($spec !== '') {
    $joinType = 'INNER JOIN';
}

if ($search !== '') {
    $where[]           = "(d.fullname LIKE :search OR d.email LIKE :search2)";
    $params[':search']  = "%$search%";
    $params[':search2'] = "%$search%";
}

if ($spec !== '') {
    
    $where[]          = "TRIM(s.name_vi) = :spec";
    $params[':spec']  = $spec;
}

$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT d.*, COALESCE(s.name_vi, d.spec, 'Chưa có') AS spec_display,
        s.name as spec_name_en
        FROM doctb d
        $joinType specializations s ON d.spec_id = s.id
        $whereSQL
        ORDER BY d.fullname ASC";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($doctors)) {
    echo '<tr><td colspan="6" class="text-center text-muted py-3">Không tìm thấy bác sĩ nào.</td></tr>';
} else {
    $serial = 1;
    foreach ($doctors as $row) {
        $fullname     = htmlspecialchars($row['fullname'] ?? $row['username']);
        $spec_display = htmlspecialchars($row['spec_display'] ?? '---');
        $email        = htmlspecialchars($row['email']);
        $fees         = htmlspecialchars($row['docFees']);
        echo '<tr>
            <td>' . $serial++ . '</td>
            <td><strong>BS. ' . $fullname . '</strong></td>
            <td><span class="badge" style="background: linear-gradient(135deg, #d2302c, #8b0000); color: #ffffff; padding: 0.5rem 0.8rem; font-weight: 700;">' . $spec_display . '</span></td>
            <td>' . $email . '</td>
            <td><strong style="color: #d2302c;">₹' . $fees . '</strong></td>
            <td>
                <form method="post" action="?page=doctors" style="display:inline" onsubmit="return confirm(\'Bạn có chắc muốn xóa?\');">
                    <input type="hidden" name="demail" value="' . $email . '">
                    <button type="submit" name="docsub1" class="btn btn-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; border: none; border-radius: 6px; padding: 8px 14px; font-weight: 600; box-shadow: 0 2px 8px rgba(239,68,68,0.3);">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </button>
                </form>
            </td>
        </tr>';
    }
}
