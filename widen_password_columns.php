<?php
// widen_password_columns.php — one-off schema migration.
//
// Widens every password-bearing column to VARCHAR(255) so it can hold
// bcrypt hashes (60 chars) and leaves headroom for future algorithms

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("This script may only be run from the command line.\n");
}

$apply = in_array('--apply', $argv, true);
echo $apply
    ? "Mode: APPLY (ALTER TABLE statements will be executed)\n"
    : "Mode: DRY RUN (no schema changes — use --apply to commit)\n";
echo str_repeat('-', 60) . "\n";

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    fwrite(STDERR, "DB connection failed: " . mysqli_connect_error() . "\n");
    exit(1);
}

// Hard-coded whitelist of (table, column) pairs to widen.
// Table/column names are NEVER read from user input.
$targets = [
    ['admintb', 'password'],
    ['doctb',   'password'],
    ['patreg',  'password'],
    ['patreg',  'cpassword'],
];

foreach ($targets as [$table, $col]) {
    // Inspect the current column definition so we can show before/after.
    $probe = mysqli_query(
        $con,
        "SELECT COLUMN_TYPE, IS_NULLABLE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = '" . mysqli_real_escape_string($con, $table) . "'
           AND COLUMN_NAME  = '" . mysqli_real_escape_string($con, $col)   . "'"
    );
    $info = $probe ? mysqli_fetch_assoc($probe) : null;

    if (!$info) {
        echo "  [$table.$col] column not found — skipped\n";
        continue;
    }

    $currentType = $info['COLUMN_TYPE'];
    $nullable    = strtoupper($info['IS_NULLABLE']) === 'YES' ? 'NULL' : 'NOT NULL';

    echo "  [$table.$col] current type: $currentType  ($nullable)\n";

    if (strcasecmp($currentType, 'varchar(255)') === 0) {
        echo "                  already VARCHAR(255) — skipped\n";
        continue;
    }

    $sql = "ALTER TABLE `$table` MODIFY `$col` VARCHAR(255) $nullable";

    if ($apply) {
        if (mysqli_query($con, $sql)) {
            echo "                  -> widened to VARCHAR(255)\n";
        } else {
            fwrite(STDERR, "                  ALTER failed: "
                . mysqli_error($con) . "\n");
        }
    } else {
        echo "                  would run: $sql\n";
    }
}

echo str_repeat('-', 60) . "\n";
if (!$apply) {
    echo "Nothing was changed. Re-run with --apply to commit.\n";
} else {
    echo "Done. You can now run: hash_existing_passwords.php --apply\n";
}

mysqli_close($con);
