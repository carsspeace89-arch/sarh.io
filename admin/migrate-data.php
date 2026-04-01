<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php'); exit;
}
if (!isset($_GET['run'])) {
    echo '<!DOCTYPE html><html dir="rtl"><body style="font-family:sans-serif;padding:40px;background:#f5f5f5">';
    echo '<h1>🔄 ترحيل البيانات من القاعدة القديمة</h1>';
    echo '<p>سيتم ترحيل: الفروع، الورديات، الموظفين، الحضور، الوثائق</p>';
    echo '<p style="color:red;font-weight:bold">⚠️ تنبيه: سيتم حذف البيانات الحالية واستبدالها ببيانات القاعدة القديمة</p>';
    echo '<a href="?run=1" style="display:inline-block;padding:15px 30px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-size:1.2rem;margin:10px 0">🚀 بدء الترحيل</a>';
    echo '</body></html>'; exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"></head>';
echo '<body style="font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;direction:ltr"><pre>';

$src = new mysqli('localhost', 'u307296675_whats', 'Goolbx512@@@', 'u307296675_whats');
if ($src->connect_error) die('SRC ERROR: ' . $src->connect_error);
$src->set_charset('utf8mb4');

$dst = new mysqli('localhost', 'u307296675_xml5', 'Goolbx512@@@@', 'u307296675_xml5');
if ($dst->connect_error) die('DST ERROR: ' . $dst->connect_error);
$dst->set_charset('utf8mb4');

echo "<span style='color:#4ec9b0'>✅ Connected to both databases</span>\n\n";

// ─── 1. Add profile_photo column ───
echo "<span style='color:#569cd6'>═══ Step 1: Add profile_photo column ═══</span>\n";
$dst->query("ALTER TABLE employees ADD COLUMN profile_photo VARCHAR(500) NULL AFTER phone");
echo "Done (or already exists)\n\n";

// ─── 2. Add branch 6 ───
echo "<span style='color:#569cd6'>═══ Step 2: Add branch 6 ═══</span>\n";
$r = $src->query("SELECT * FROM branches WHERE id = 6");
if ($row = $r->fetch_assoc()) {
    $stmt = $dst->prepare("INSERT IGNORE INTO branches (id, name, latitude, longitude, geofence_radius, allow_overtime, overtime_start_after, overtime_min_duration, is_active, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('isddiiiii',
        $row['id'], $row['name'], $row['latitude'], $row['longitude'],
        $row['geofence_radius'], $row['allow_overtime'], $row['overtime_start_after'],
        $row['overtime_min_duration'], $row['is_active']
    );
    // workaround: created_at via query
    $stmt->execute();
    echo "Branch 6: " . $row['name'] . " → " . ($stmt->affected_rows > 0 ? 'INSERTED' : 'ALREADY EXISTS') . "\n";
}
echo "\n";

// ─── 3. Update all branch coords ───
echo "<span style='color:#569cd6'>═══ Step 3: Update branch coordinates ═══</span>\n";
$r = $src->query("SELECT * FROM branches");
while ($row = $r->fetch_assoc()) {
    $dst->query("UPDATE branches SET 
        latitude='" . $dst->real_escape_string($row['latitude']) . "',
        longitude='" . $dst->real_escape_string($row['longitude']) . "',
        geofence_radius=" . (int)$row['geofence_radius'] . ",
        allow_overtime=" . (int)$row['allow_overtime'] . ",
        overtime_start_after=" . (int)$row['overtime_start_after'] . ",
        overtime_min_duration=" . (int)$row['overtime_min_duration'] . "
        WHERE id=" . (int)$row['id']);
    echo "Branch {$row['id']}: coords updated\n";
}
echo "\n";

// ─── 4. Create branch_shifts table ───
echo "<span style='color:#569cd6'>═══ Step 4: Create branch_shifts table ═══</span>\n";
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
if ($dst->error) echo "<span style='color:#f44747'>ERROR: " . $dst->error . "</span>\n";
else echo "Table created/exists\n";
echo "\n";

// ─── 5. Migrate shifts ───
echo "<span style='color:#569cd6'>═══ Step 5: Migrate shifts ═══</span>\n";
$dst->query("DELETE FROM branch_shifts");
$r = $src->query("SELECT * FROM branch_shifts");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $stmt = $dst->prepare("INSERT INTO branch_shifts (id, branch_id, shift_number, shift_start, shift_end, is_active, created_at) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('iiissss',
        $row['id'], $row['branch_id'], $row['shift_number'],
        $row['shift_start'], $row['shift_end'], $row['is_active'], $row['created_at']
    );
    if ($stmt->execute()) $cnt++;
    else echo "<span style='color:#f44747'>ERR shift {$row['id']}: {$stmt->error}</span>\n";
}
echo "Inserted $cnt shifts\n\n";

// ─── 6. Re-import all employees ───
echo "<span style='color:#569cd6'>═══ Step 6: Re-import employees ═══</span>\n";
$dst->query("SET FOREIGN_KEY_CHECKS = 0");
$dst->query("DELETE FROM attendances");
$dst->query("DELETE FROM employees");

$r = $src->query("SELECT id, name, job_title, pin, pin_changed_at, phone, profile_photo, unique_token, branch_id, device_fingerprint, device_registered_at, device_bind_mode, security_level, is_active, deleted_at, created_at FROM employees ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $stmt = $dst->prepare("INSERT INTO employees (id, name, job_title, pin, pin_changed_at, phone, profile_photo, unique_token, branch_id, device_fingerprint, device_registered_at, device_bind_mode, security_level, is_active, deleted_at, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('isssssssisssiiis',
        $row['id'], $row['name'], $row['job_title'], $row['pin'],
        $row['pin_changed_at'], $row['phone'], $row['profile_photo'],
        $row['unique_token'], $row['branch_id'], $row['device_fingerprint'],
        $row['device_registered_at'], $row['device_bind_mode'],
        $row['security_level'], $row['is_active'], $row['deleted_at'], $row['created_at']
    );
    if ($stmt->execute()) {
        $cnt++;
    } else {
        echo "<span style='color:#f44747'>ERR emp {$row['id']} ({$row['name']}): {$stmt->error}</span>\n";
    }
}
echo "<span style='color:#4ec9b0'>Imported $cnt employees</span>\n\n";

// ─── 7. Update PINs to last 4 digits of phone ───
echo "<span style='color:#569cd6'>═══ Step 7: Update PINs (last 4 digits of phone) ═══</span>\n";
$r = $dst->query("SELECT id, phone FROM employees WHERE phone IS NOT NULL AND phone != '' ORDER BY id");
$pinMap = []; // pin => [emp_ids]
$empPins = []; // id => desired pin

while ($row = $r->fetch_assoc()) {
    $clean = preg_replace('/[^0-9]/', '', $row['phone']);
    if (strlen($clean) < 4) continue;
    $pin4 = substr($clean, -4);
    $empPins[$row['id']] = $pin4;
    $pinMap[$pin4][] = $row['id'];
}

$updated = 0;
$conflicts = 0;
foreach ($pinMap as $pin => $ids) {
    if (count($ids) === 1) {
        $dst->query("UPDATE employees SET pin='" . $dst->real_escape_string($pin) . "' WHERE id=" . (int)$ids[0]);
        $updated++;
    } else {
        // Conflict: first keeps original, others get pin+suffix
        echo "<span style='color:#dcdcaa'>⚠️ PIN conflict '$pin' for employees: " . implode(', ', $ids) . "</span>\n";
        foreach ($ids as $i => $id) {
            if ($i === 0) {
                $finalPin = $pin;
            } else {
                // Try last 5, then last 6, etc.
                $clean = preg_replace('/[^0-9]/', '', '');
                $r2 = $dst->query("SELECT phone FROM employees WHERE id=$id");
                $emp = $r2->fetch_assoc();
                $cleanPhone = preg_replace('/[^0-9]/', '', $emp['phone']);
                $finalPin = substr($cleanPhone, -5); // Use 5 digits instead
                if (strlen($finalPin) < 5) $finalPin = $pin . $id; // Fallback
            }
            $dst->query("UPDATE employees SET pin='" . $dst->real_escape_string($finalPin) . "' WHERE id=" . (int)$id);
            echo "  emp $id → PIN: $finalPin\n";
            $updated++;
        }
        $conflicts++;
    }
}
echo "Updated $updated PINs" . ($conflicts ? " ($conflicts conflicts resolved)" : "") . "\n\n";

// ─── 8. Migrate attendances ───
echo "<span style='color:#569cd6'>═══ Step 8: Migrate attendances ═══</span>\n";
$r = $src->query("SELECT * FROM attendances ORDER BY id");
$cnt = 0;
$errs = 0;
while ($row = $r->fetch_assoc()) {
    $type = ($row['type'] === 'overtime') ? 'overtime-start' : $row['type'];
    $stmt = $dst->prepare("INSERT IGNORE INTO attendances (id, employee_id, type, timestamp, attendance_date, late_minutes, latitude, longitude, location_accuracy, ip_address, user_agent, notes, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('iisssiddsssss',
        $row['id'], $row['employee_id'], $type, $row['timestamp'],
        $row['attendance_date'], $row['late_minutes'], $row['latitude'],
        $row['longitude'], $row['location_accuracy'], $row['ip_address'],
        $row['user_agent'], $row['notes'], $row['created_at']
    );
    if ($stmt->execute()) $cnt++;
    else { echo "<span style='color:#f44747'>ERR att {$row['id']}: {$stmt->error}</span>\n"; $errs++; }
}
echo "<span style='color:#4ec9b0'>Imported $cnt attendances" . ($errs ? " ($errs errors)" : "") . "</span>\n\n";

// ─── 9. Migrate documents ───
echo "<span style='color:#569cd6'>═══ Step 9: Migrate documents ═══</span>\n";
$dst->query("DELETE FROM emp_document_files");
$dst->query("DELETE FROM emp_document_groups");

$r = $src->query("SELECT * FROM emp_document_groups ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $stmt = $dst->prepare("INSERT INTO emp_document_groups (id, employee_id, group_name, expiry_date, sort_order, created_at, updated_at) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('iississ',
        $row['id'], $row['employee_id'], $row['group_name'],
        $row['expiry_date'], $row['sort_order'], $row['created_at'], $row['updated_at']
    );
    if ($stmt->execute()) $cnt++;
    else echo "<span style='color:#f44747'>ERR doc group {$row['id']}: {$stmt->error}</span>\n";
}
echo "Imported $cnt document groups\n";

$r = $src->query("SELECT * FROM emp_document_files ORDER BY id");
$cnt = 0;
while ($row = $r->fetch_assoc()) {
    $stmt = $dst->prepare("INSERT INTO emp_document_files (id, group_id, file_path, file_type, original_name, file_size, sort_order, created_at) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('iisssiis',
        $row['id'], $row['group_id'], $row['file_path'],
        $row['file_type'], $row['original_name'], $row['file_size'],
        $row['sort_order'], $row['created_at']
    );
    if ($stmt->execute()) $cnt++;
    else echo "<span style='color:#f44747'>ERR doc file {$row['id']}: {$stmt->error}</span>\n";
}
echo "Imported $cnt document files\n";

$dst->query("SET FOREIGN_KEY_CHECKS = 1");

// ─── 10. Copy document files ───
echo "\n<span style='color:#569cd6'>═══ Step 10: Copy document files ═══</span>\n";
$oldBase = dirname(dirname(__FILE__), 2) . '/public_html/storage/uploads/';
$newBase = dirname(__DIR__) . '/storage/uploads/';

// Actually both sites share the same storage path on Hostinger
// Check if profiles folder exists in storage
$storageDir = dirname(__DIR__) . '/storage/uploads/profiles/';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0755, true);
    echo "Created storage/uploads/profiles/\n";
}

// Check old site path
$oldSite = '/home/u307296675/domains/sarh.io/public_html/storage/uploads/profiles/';
$newSite = dirname(__DIR__) . '/storage/uploads/profiles/';
if (is_dir($oldSite) && $oldSite !== $newSite) {
    // Copy files recursively
    $cmd = "cp -rn " . escapeshellarg($oldSite) . "* " . escapeshellarg($newSite) . " 2>&1";
    $output = shell_exec($cmd);
    echo "Copy from old site: " . ($output ?: 'done') . "\n";
} else {
    echo "Old storage: " . ($oldSite) . (is_dir($oldSite) ? ' EXISTS' : ' NOT FOUND') . "\n";
    echo "New storage: " . ($newSite) . (is_dir($newSite) ? ' EXISTS' : ' NOT FOUND') . "\n";
    // Try the whats app path
    $whatsPath = '/home/u307296675/domains/sarh.io/public_html/whats/storage/uploads/profiles/';
    if (is_dir($whatsPath)) {
        $cmd = "cp -rn " . escapeshellarg(rtrim($whatsPath, '/')) . "/ " . escapeshellarg(rtrim($newSite, '/')) . "/ 2>&1";
        $output = shell_exec($cmd);
        echo "Copy from whats: " . ($output ?: 'done') . "\n";
    }
}

// ─── Summary ───
echo "\n<span style='color:#569cd6'>═══════════════════════════════</span>\n";
echo "<span style='color:#569cd6'>         SUMMARY</span>\n";
echo "<span style='color:#569cd6'>═══════════════════════════════</span>\n";
$tables = ['branches', 'employees', 'attendances', 'branch_shifts', 'emp_document_groups', 'emp_document_files'];
foreach ($tables as $t) {
    $c = $dst->query("SELECT COUNT(*) c FROM `$t`")->fetch_assoc()['c'];
    echo str_pad($t, 25) . ": <span style='color:#4ec9b0'>$c</span> rows\n";
}

// Show PIN list
echo "\n<span style='color:#569cd6'>═══ Employee PINs ═══</span>\n";
$r = $dst->query("SELECT id, name, pin, phone FROM employees WHERE is_active = 1 AND deleted_at IS NULL ORDER BY id");
while ($row = $r->fetch_assoc()) {
    echo str_pad($row['id'], 4) . str_pad($row['name'], 30) . " PIN: " . str_pad($row['pin'], 8) . " Phone: " . $row['phone'] . "\n";
}

echo "\n<span style='color:#4ec9b0'>✅ Migration complete!</span>\n";
echo "<span style='color:#f44747'>⚠️ DELETE THIS FILE NOW FOR SECURITY!</span>\n";

$src->close();
$dst->close();
echo '</pre></body></html>';
