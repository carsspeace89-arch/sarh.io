<?php
// CLI-only migration script - No session required
if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

echo "=== Database Migration ===\n\n";

$src = new mysqli('localhost', 'u307296675_whats', 'Goolbx512@@@', 'u307296675_whats');
if ($src->connect_error) { echo "SRC ERROR: " . $src->connect_error . "\n"; exit(1); }
$src->set_charset('utf8mb4');
echo "SRC connected\n";

$dst = new mysqli('localhost', 'u307296675_xml5', 'Goolbx512@@@@', 'u307296675_xml5');
if ($dst->connect_error) { echo "DST ERROR: " . $dst->connect_error . "\n"; exit(1); }
$dst->set_charset('utf8mb4');
echo "DST connected\n\n";

// Suppress exceptions for duplicate errors
mysqli_report(MYSQLI_REPORT_ERROR);

// 1. Add profile_photo column
echo "--- Step 1: Add profile_photo column ---\n";
try {
    $dst->query("ALTER TABLE employees ADD COLUMN profile_photo VARCHAR(500) NULL AFTER phone");
} catch (Exception $e) { /* already exists */ }
echo "Done\n\n";

// 2. Add branch 6
echo "--- Step 2: Add branch 6 ---\n";
$r = $src->query("SELECT * FROM branches WHERE id = 6");
if ($row = $r->fetch_assoc()) {
    $name = $dst->real_escape_string($row['name']);
    $dst->query("INSERT IGNORE INTO branches (id, name, latitude, longitude, geofence_radius, allow_overtime, overtime_start_after, overtime_min_duration, is_active) VALUES ({$row['id']}, '$name', {$row['latitude']}, {$row['longitude']}, {$row['geofence_radius']}, {$row['allow_overtime']}, {$row['overtime_start_after']}, {$row['overtime_min_duration']}, {$row['is_active']})");
    echo "Branch 6: " . ($dst->affected_rows > 0 ? 'INSERTED' : 'EXISTS') . "\n";
}
echo "\n";

// 3. Update branch coords
echo "--- Step 3: Update branch coords ---\n";
$r = $src->query("SELECT * FROM branches");
while ($row = $r->fetch_assoc()) {
    $dst->query("UPDATE branches SET latitude={$row['latitude']}, longitude={$row['longitude']}, geofence_radius={$row['geofence_radius']}, allow_overtime={$row['allow_overtime']}, overtime_start_after={$row['overtime_start_after']}, overtime_min_duration={$row['overtime_min_duration']} WHERE id={$row['id']}");
    echo "Branch {$row['id']} updated\n";
}
echo "\n";

// 4. Create branch_shifts table
echo "--- Step 4: Create branch_shifts ---\n";
try {
    $dst->query("CREATE TABLE IF NOT EXISTS branch_shifts (
        id int(11) NOT NULL AUTO_INCREMENT,
        branch_id int(11) NOT NULL,
        shift_number tinyint(4) NOT NULL DEFAULT 1,
        shift_start time NOT NULL,
        shift_end time NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        created_at timestamp NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id),
        KEY idx_branch (branch_id),
        CONSTRAINT branch_shifts_fk FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "OK\n\n";
} catch (Exception $e) {
    echo "Table exists or: " . $e->getMessage() . "\n\n";
}

// 5. Migrate shifts
echo "--- Step 5: Migrate shifts ---\n";
$dst->query("DELETE FROM branch_shifts");
$r = $src->query("SELECT * FROM branch_shifts");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $s = $dst->real_escape_string($row['shift_start']);
    $e = $dst->real_escape_string($row['shift_end']);
    $dst->query("INSERT INTO branch_shifts (id, branch_id, shift_number, shift_start, shift_end, is_active) VALUES ({$row['id']}, {$row['branch_id']}, {$row['shift_number']}, '$s', '$e', {$row['is_active']})");
    if ($dst->affected_rows > 0) $cnt++;
    elseif ($dst->error) echo "ERR: {$dst->error}\n";
}
echo "Inserted $cnt shifts\n\n";

// 6. Re-import employees
echo "--- Step 6: Re-import employees ---\n";
$dst->query("SET FOREIGN_KEY_CHECKS = 0");
$dst->query("DELETE FROM attendances");
$dst->query("DELETE FROM emp_document_files");
$dst->query("DELETE FROM emp_document_groups");
$dst->query("DELETE FROM employees");

$r = $src->query("SELECT * FROM employees ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $vals = [];
    $cols = ['id','name','job_title','pin','pin_changed_at','phone','profile_photo','unique_token','branch_id','device_fingerprint','device_registered_at','device_bind_mode','security_level','is_active','deleted_at','created_at'];
    foreach ($cols as $c) {
        if (!isset($row[$c]) || $row[$c] === null) {
            $vals[] = 'NULL';
        } else {
            $vals[] = "'" . $dst->real_escape_string($row[$c]) . "'";
        }
    }
    $colsStr = implode(',', array_map(fn($c) => "`$c`", $cols));
    $valsStr = implode(',', $vals);
    $dst->query("INSERT INTO employees ($colsStr) VALUES ($valsStr)");
    if ($dst->error) echo "ERR emp {$row['id']}: {$dst->error}\n";
    else $cnt++;
}
echo "Imported $cnt employees\n\n";

// 7. Update PINs
echo "--- Step 7: Update PINs ---\n";
$r = $dst->query("SELECT id, phone FROM employees WHERE phone IS NOT NULL AND phone != '' ORDER BY id");
$pinMap = [];
while ($row = $r->fetch_assoc()) {
    $clean = preg_replace('/[^0-9]/', '', $row['phone']);
    if (strlen($clean) < 4) continue;
    $pin4 = substr($clean, -4);
    $pinMap[$pin4][] = $row['id'];
}

$updated = 0;
foreach ($pinMap as $pin => $ids) {
    if (count($ids) === 1) {
        $dst->query("UPDATE employees SET pin='$pin' WHERE id={$ids[0]}");
        $updated++;
    } else {
        echo "CONFLICT pin '$pin' for IDs: " . implode(', ', $ids) . "\n";
        foreach ($ids as $i => $id) {
            if ($i === 0) {
                $dst->query("UPDATE employees SET pin='$pin' WHERE id=$id");
            } else {
                $r2 = $dst->query("SELECT phone FROM employees WHERE id=$id");
                $emp = $r2->fetch_assoc();
                $clean = preg_replace('/[^0-9]/', '', $emp['phone']);
                $pin5 = substr($clean, -5);
                if ($pin5 === $pin) $pin5 = $pin . $id;
                $dst->query("UPDATE employees SET pin='" . $dst->real_escape_string($pin5) . "' WHERE id=$id");
                echo "  emp $id -> $pin5\n";
            }
            $updated++;
        }
    }
}
echo "Updated $updated PINs\n\n";

// 8. Migrate attendances
echo "--- Step 8: Migrate attendances ---\n";
$r = $src->query("SELECT * FROM attendances ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $type = ($row['type'] === 'overtime') ? 'overtime-start' : $row['type'];
    $cols = ['id','employee_id','type','timestamp','attendance_date','late_minutes','latitude','longitude','location_accuracy','ip_address','user_agent','notes','created_at'];
    $vals = [];
    foreach ($cols as $c) {
        if ($c === 'type') {
            $vals[] = "'" . $dst->real_escape_string($type) . "'";
        } elseif (!isset($row[$c]) || $row[$c] === null) {
            $vals[] = 'NULL';
        } else {
            $vals[] = "'" . $dst->real_escape_string($row[$c]) . "'";
        }
    }
    $colsStr = implode(',', array_map(fn($c) => "`$c`", $cols));
    $valsStr = implode(',', $vals);
    $dst->query("INSERT IGNORE INTO attendances ($colsStr) VALUES ($valsStr)");
    if ($dst->error) echo "ERR att {$row['id']}: {$dst->error}\n";
    else $cnt++;
}
echo "Imported $cnt attendances\n\n";

// 9. Migrate documents
echo "--- Step 9: Migrate documents ---\n";
$r = $src->query("SELECT * FROM emp_document_groups ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $gn = $dst->real_escape_string($row['group_name']);
    $ed = $dst->real_escape_string($row['expiry_date']);
    $ca = $dst->real_escape_string($row['created_at']);
    $ua = $dst->real_escape_string($row['updated_at']);
    $dst->query("INSERT INTO emp_document_groups (id, employee_id, group_name, expiry_date, sort_order, created_at, updated_at) VALUES ({$row['id']}, {$row['employee_id']}, '$gn', '$ed', {$row['sort_order']}, '$ca', '$ua')");
    if ($dst->error) echo "ERR grp {$row['id']}: {$dst->error}\n";
    else $cnt++;
}
echo "Imported $cnt document groups\n";

$r = $src->query("SELECT * FROM emp_document_files ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $fp = $dst->real_escape_string($row['file_path']);
    $ft = $dst->real_escape_string($row['file_type']);
    $on = $dst->real_escape_string($row['original_name']);
    $ca = $dst->real_escape_string($row['created_at']);
    $dst->query("INSERT INTO emp_document_files (id, group_id, file_path, file_type, original_name, file_size, sort_order, created_at) VALUES ({$row['id']}, {$row['group_id']}, '$fp', '$ft', '$on', {$row['file_size']}, {$row['sort_order']}, '$ca')");
    if ($dst->error) echo "ERR file {$row['id']}: {$dst->error}\n";
    else $cnt++;
}
echo "Imported $cnt document files\n\n";

$dst->query("SET FOREIGN_KEY_CHECKS = 1");

// 10. Copy document files from old site
echo "--- Step 10: Copy document files ---\n";
$newStorage = dirname(__DIR__) . '/storage/uploads/profiles/';
if (!is_dir($newStorage)) {
    @mkdir($newStorage, 0755, true);
    echo "Created $newStorage\n";
}

// Try common old paths
$oldPaths = [
    '/home/u307296675/domains/sarh.io/public_html/storage/uploads/profiles/',
    '/home/u307296675/domains/sarh.io/public_html/whats/storage/uploads/profiles/',
    '/home/u307296675/public_html/storage/uploads/profiles/',
];
foreach ($oldPaths as $old) {
    if (is_dir($old)) {
        echo "Found old storage: $old\n";
        $output = shell_exec("cp -rn " . escapeshellarg(rtrim($old, '/') . '/') . ". " . escapeshellarg($newStorage) . " 2>&1");
        echo "Copy result: " . ($output ?: "OK") . "\n";
        break;
    } else {
        echo "Not found: $old\n";
    }
}

// Summary
echo "\n=== SUMMARY ===\n";
$tables = ['branches','employees','attendances','branch_shifts','emp_document_groups','emp_document_files'];
foreach ($tables as $t) {
    $res = $dst->query("SELECT COUNT(*) c FROM `$t`");
    $c = $res ? $res->fetch_assoc()['c'] : '?';
    echo str_pad($t, 25) . ": $c rows\n";
}

echo "\n=== EMPLOYEE PINS ===\n";
$r = $dst->query("SELECT id, name, pin, phone FROM employees WHERE is_active = 1 AND deleted_at IS NULL ORDER BY id");
while ($row = $r->fetch_assoc()) {
    echo "ID:" . str_pad($row['id'], 4) . " PIN:" . str_pad($row['pin'], 8) . " " . $row['name'] . "\n";
}

echo "\nMigration complete!\n";
$src->close();
$dst->close();
