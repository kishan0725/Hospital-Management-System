<?php
// hash_existing_passwords.php — one-off migration script.
//
// Scans admintb, doctb, and patreg for plaintext password values
// and replaces them with bcrypt hashes via password_hash().
// Idempotent: rows that are already hashed are skipped.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("This script may only be run from the command line.\n");
}

$apply = in_array('--apply', $argv, true);
echo $apply
    ? "Mode: APPLY (writes will be performed)\n"
    : "Mode: DRY RUN (no writes — use --apply to commit)\n";
echo str_repeat('-', 60) . "\n";


$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    fwrite(STDERR, "DB connection failed: " . mysqli_connect_error() . "\n");
    exit(1);
}

/**
 * Returns true if $value looks like a valid PHP password_hash() output.
 * password_get_info() returns algo = 0 / algoName = 'unknown' when the
 * string isn't a recognized hash (i.e. plaintext).
 */
function looks_hashed(?string $value): bool
{
    if ($value === null || $value === '') {
        return false;
    }
    $info = password_get_info($value);
    // algo is int|string|null depending on PHP version; 0, '0', or null = not a hash.
    return !empty($info['algo']);
}

/**
 * Migrate one table's password column.
 *
 * @param mysqli  $con       Connection.
 * @param string  $table     Table name (whitelisted below — never user input).
 * @param string  $pk        Primary-key column name.
 * @param string  $pkType    mysqli bind type char for the PK ('i' or 's').
 * @param string  $pwCol     Password column to hash.
 * @param bool    $apply     If false, only report.
 * @return array  [checked, updated, skipped]
 */
function migrate_table(
    mysqli $con,
    string $table,
    string $pk,
    string $pkType,
    string $pwCol,
    bool   $apply
): array {
    // Table / column names cannot be bound as parameters, so we hard-code
    // the allowed set in the caller and interpolate only those. Never
    // accept table/column names from user input here.
    $sql = "SELECT `$pk` AS pk, `$pwCol` AS pw FROM `$table`";
    $res = mysqli_query($con, $sql);
    if (!$res) {
        fwrite(STDERR, "  [$table.$pwCol] query failed: " . mysqli_error($con) . "\n");
        return [0, 0, 0];
    }

    $checked = 0;
    $updated = 0;
    $skipped = 0;

    // Prepare the update once; we'll rebind per-row.
    $upd = mysqli_prepare(
        $con,
        "UPDATE `$table` SET `$pwCol` = ? WHERE `$pk` = ?"
    );

    while ($row = mysqli_fetch_assoc($res)) {
        $checked++;
        $pkVal = $row['pk'];
        $pwVal = $row['pw'];

        if (looks_hashed($pwVal)) {
            $skipped++;
            continue;
        }

        if ($pwVal === null || $pwVal === '') {
            // Empty password — skip rather than hash an empty string.
            echo "  [$table.$pwCol] pk=$pkVal : empty, skipped\n";
            $skipped++;
            continue;
        }

        $hash = password_hash($pwVal, PASSWORD_DEFAULT);

        if ($apply) {
            mysqli_stmt_bind_param($upd, "s$pkType", $hash, $pkVal);
            $ok = mysqli_stmt_execute($upd);
            if ($ok) {
                $updated++;
                echo "  [$table.$pwCol] pk=$pkVal : hashed\n";
            } else {
                fwrite(STDERR, "  [$table.$pwCol] pk=$pkVal : UPDATE failed: "
                    . mysqli_stmt_error($upd) . "\n");
            }
        } else {
            $updated++;  // counted as "would update" in dry-run
            echo "  [$table.$pwCol] pk=$pkVal : would hash\n";
        }
    }

    if ($upd) mysqli_stmt_close($upd);
    return [$checked, $updated, $skipped];
}

// ---------------------------------------------------------------------
// Tables to migrate. Hard-coded whitelist — NEVER driven by user input.
//
//   - admintb : PK=username (string) , pw col=password
//   - doctb   : PK=username (string) , pw col=password
//   - patreg  : PK=pid      (int)    , pw col=password
//               plus cpassword (legacy confirm-password column)
// ---------------------------------------------------------------------
$targets = [
    ['admintb', 'username', 's', 'password'],
    ['doctb',   'username', 's', 'password'],
    ['patreg',  'pid',      'i', 'password'],
    ['patreg',  'pid',      'i', 'cpassword'],
];

$totalChecked = 0;
$totalUpdated = 0;
$totalSkipped = 0;

foreach ($targets as [$table, $pk, $pkType, $pwCol]) {
    echo "Scanning `$table`.`$pwCol`  (PK=$pk)\n";
    [$c, $u, $s] = migrate_table($con, $table, $pk, $pkType, $pwCol, $apply);
    echo "  -> checked: $c, " . ($apply ? "updated" : "would update") . ": $u, already hashed / empty: $s\n\n";
    $totalChecked += $c;
    $totalUpdated += $u;
    $totalSkipped += $s;
}

echo str_repeat('-', 60) . "\n";
echo "Totals: checked=$totalChecked, "
    . ($apply ? "updated" : "would update") . "=$totalUpdated, "
    . "already hashed / empty=$totalSkipped\n";

if (!$apply) {
    echo "\nNothing was written. Re-run with --apply to commit.\n";
}

mysqli_close($con);
