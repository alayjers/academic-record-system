<?php
require_once 'includes/header.php';
require_once 'config/config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$message_type = '';

$years_stmt = $pdo->query("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
$available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

$selected_sy = isset($_GET['sy']) ? $_GET['sy'] : ($available_years[0] ?? '2025-2026');

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type'])) {
    
    if ($_POST['action_type'] == 'add_student') {
        $lrn = preg_replace('/\D/', '', $_POST['lrn'] ?? '');
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $gender = $_POST['gender'];
        $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
        $section_id = intval($_POST['section_id']);

        if (strlen($lrn) !== 12) {
            $message = "Enrollment rejected: Learner Reference Number (LRN) must be exactly 12 digits.";
            $message_type = "error";
        } else {
            $check = $pdo->prepare("SELECT id FROM students WHERE lrn = ?");
            $check->execute([$lrn]);
            
            if ($check->fetch()) {
                $message = "A student with this LRN already exists.";
                $message_type = "error";
            } else {
                try {
                    $sect_query = $pdo->prepare("SELECT grade_level, name FROM sections WHERE id = ? LIMIT 1");
                    $sect_query->execute([$section_id]);
                    $section_data = $sect_query->fetch(PDO::FETCH_ASSOC);

                    if (!$section_data) {
                        throw new Exception("Target classroom section does not exist.");
                    }

                    $grade_level = intval($section_data['grade_level']);
                    $section_name = $section_data['name'];

                    $pdo->beginTransaction();

                    $year_prefix = date('Y') . '1'; 
                    $seq_stmt = $pdo->prepare("
                        SELECT school_id_number 
                        FROM students 
                        WHERE school_id_number LIKE ? 
                        ORDER BY school_id_number DESC, id DESC 
                        LIMIT 1
                    ");
                    $seq_stmt->execute([$year_prefix . '-%']);
                    $last_id_entry = $seq_stmt->fetchColumn();

                    if ($last_id_entry) {
                        $parts = explode('-', $last_id_entry);
                        $next_numeric_sequence = intval($parts[1]) + 1;
                    } else {
                        $next_numeric_sequence = 1;
                    }
                    
                    $school_id_number = $year_prefix . '-' . str_pad($next_numeric_sequence, 5, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("INSERT INTO students (school_id_number, first_name, last_name, lrn, gender, birth_date, grade_level, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$school_id_number, $first_name, $last_name, $lrn, $gender, $birth_date, $grade_level, $section_name]);
                    $new_student_id = $pdo->lastInsertId();

                    $enroll_stmt = $pdo->prepare("INSERT INTO enrollments (student_id, section_id) VALUES (?, ?)");
                    $enroll_stmt->execute([$new_student_id, $section_id]);

                    $pdo->commit();
                    $message = "Student " . htmlspecialchars($first_name . ' ' . $last_name) . " enrolled successfully with ID: " . $school_id_number;
                    $message_type = "success";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = "Database fault: Unable to complete enrollment workflow.";
                    $message_type = "error";
                }
            }
        }
    }
    
    else if ($_POST['action_type'] == 'promote_students') {
        $target_section_id = intval($_POST['target_section_id']);
        $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];

        if (empty($student_ids)) {
            $message = "No student profiles selected for batch advancement.";
            $message_type = "error";
        } else {
            try {
                $target_sect_query = $pdo->prepare("SELECT grade_level, name, school_year FROM sections WHERE id = ? LIMIT 1");
                $target_sect_query->execute([$target_section_id]);
                $target_section_data = $target_sect_query->fetch(PDO::FETCH_ASSOC);

                if (!$target_section_data) {
                    throw new Exception("Target classroom infrastructure tracking node does not exist.");
                }

                $target_grade = intval($target_section_data['grade_level']);
                $target_section_name = $target_section_data['name'];
                $target_sy = $target_section_data['school_year'];

                $pdo->beginTransaction();

                foreach ($student_ids as $sid) {
                    $student_id = intval($sid);

                    $find_current_stmt = $pdo->prepare("
                        SELECT e.id, e.section_id 
                        FROM enrollments e
                        JOIN sections sec ON e.section_id = sec.id
                        WHERE e.student_id = ? AND sec.school_year = ?
                        LIMIT 1
                    ");
                    $find_current_stmt->execute([$student_id, $target_sy]);
                    $existing_term_enrollment = $find_current_stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing_term_enrollment) {
                        $update_enroll_stmt = $pdo->prepare("UPDATE enrollments SET section_id = ? WHERE id = ?");
                        $update_enroll_stmt->execute([$target_section_id, $existing_term_enrollment['id']]);
                    } else {
                        $find_any_stmt = $pdo->prepare("
                            SELECT e.id FROM enrollments e
                            JOIN sections sec ON e.section_id = sec.id
                            WHERE e.student_id = ? AND sec.school_year = ?
                            LIMIT 1
                        ");
                        $find_any_stmt->execute([$student_id, $selected_sy]);
                        $current_context_enrollment = $find_any_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($current_context_enrollment && $selected_sy === $target_sy) {
                            $update_enroll_stmt = $pdo->prepare("UPDATE enrollments SET section_id = ? WHERE id = ?");
                            $update_enroll_stmt->execute([$target_section_id, $current_context_enrollment['id']]);
                        } else {
                            $insert_promo_stmt = $pdo->prepare("INSERT INTO enrollments (student_id, section_id) VALUES (?, ?)");
                            $insert_promo_stmt->execute([$student_id, $target_section_id]);
                        }
                    }

                    $update_student_profile = $pdo->prepare("UPDATE students SET grade_level = ?, section = ? WHERE id = ?");
                    $update_student_profile->execute([$target_grade, $target_section_name, $student_id]);
                }

                $pdo->commit();
                
                header("Location: students.php?sy=" . urlencode($target_sy));
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Promotion sequence aborted due to a systemic database fault.";
                $message_type = "error";
            }
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: students.php?sy=" . urlencode($selected_sy));
    exit();
}

$sections_stmt = $pdo->prepare("SELECT id, name, grade_level FROM sections WHERE school_year = ? ORDER BY grade_level ASC, name ASC");
$sections_stmt->execute([$selected_sy]);
$active_sections = $sections_stmt->fetchAll();

$all_sections_stmt = $pdo->query("SELECT id, name, grade_level, school_year FROM sections ORDER BY school_year DESC, grade_level ASC, name ASC");
$all_global_sections = $all_sections_stmt->fetchAll();

$count_stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.id) 
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN sections sec ON e.section_id = sec.id
    WHERE sec.school_year = ?
");
$count_stmt->execute([$selected_sy]);
$total_students = intval($count_stmt->fetchColumn());
$total_pages = ceil($total_students / $limit);

$students_stmt = $pdo->prepare("
    SELECT s.id, s.school_id_number, s.lrn, s.first_name, s.last_name, s.gender, s.birth_date, sec.name as section_name, sec.grade_level 
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN sections sec ON e.section_id = sec.id
    WHERE sec.school_year = ?
    ORDER BY s.school_id_number ASC
    LIMIT ? OFFSET ?
");
$students_stmt->bindValue(1, $selected_sy, PDO::PARAM_STR);
$students_stmt->bindValue(2, $limit, PDO::PARAM_INT);
$students_stmt->bindValue(3, $offset, PDO::PARAM_INT);
$students_stmt->execute();
$enrolled_students = $students_stmt->fetchAll();
?>

<style>
    .header-action-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    .filter-bar { display: flex; align-items: center; gap: 10px; background: var(--mode-btn-bg, rgba(0,0,0,0.04)); padding: 10px 15px; border-radius: 8px; }
    .filter-bar select { padding: 8px; border-radius: 6px; border: 1px solid var(--border-card); background: #fff; color: #111; font-weight: 600; }
    [data-theme="dark"] .filter-bar select, body.dark-mode .filter-bar select { background: #2a2a2a; border-color: #3a3a3a; color: #fff; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { background-color: #ffffff; color: #111111; margin: 5% auto; padding: 24px; border: 1px solid #e0e0e0; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: relative; }
    .close-btn { position: absolute; right: 20px; top: 16px; font-size: 28px; font-weight: bold; color: #666666; cursor: pointer; }
    .close-btn:hover { color: #111111; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 12px; font-weight: 600; color: #666666; }
    .form-group select, .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; color: #111111; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    [data-theme="dark"] .modal-content, body.dark-mode .modal-content { background-color: #1e1e1e; color: #ecf0f1; border-color: #2a2a2a; }
    [data-theme="dark"] .close-btn, body.dark-mode .close-btn { color: #a0aec0; }
    [data-theme="dark"] .form-group label, body.dark-mode .form-weight label { color: #a0aec0; }
    [data-theme="dark"] .form-group select, [data-theme="dark"] .form-group input, body.dark-mode .form-group select, body.dark-mode .form-group input { background: #2a2a2a; border-color: #3a3a3a; color: #ffffff; }
    .btn-primary { background-color: #00b4d8; color: #ffffff; border: 1px solid #00b4d8; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary:hover { background-color: #0096b4; }
    .btn-secondary { background-color: transparent; color: var(--text-title, #111111); border: 1px solid var(--border-card, #d1d5db); padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-secondary:hover { border-color: #00b4d8; color: #00b4d8; }
    [data-theme="dark"] .btn-secondary, body.dark-mode .btn-secondary { color: #fff; border-color: #3a3a3a; }
    .badge { background: #00b4d820; color: #00b4d8; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    .action-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    
    .pagination-panel { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border-card, #e0e0e0); }
    .pagination-links { display: flex; gap: 5px; }
    .page-node { display: inline-block; padding: 6px 12px; border: 1px solid var(--border-card, #d1d5db); border-radius: 6px; text-decoration: none; color: var(--text-title, #111); font-size: 13px; font-weight: 600; }
    .page-node:hover { border-color: #00b4d8; color: #00b4d8; }
    .page-node.active { background-color: #00b4d8; color: #fff; border-color: #00b4d8; }
    .page-node.disabled { color: #ccc; border-color: #eee; cursor: not-allowed; pointer-events: none; }
    [data-theme="dark"] .page-node, body.dark-mode .page-node { color: #fff; border-color: #3a3a3a; }
    [data-theme="dark"] .page-node.disabled, body.dark-mode .page-node.disabled { color: #555; border-color: #222; }
</style>

<div class="header-action-area">
    <h1>Student Directory Master</h1>
    
    <div style="display: flex; gap: 12px; align-items: center;">
        <form method="GET" class="filter-bar">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Active Context:</label>
            <select name="sy" onchange="this.form.submit()">
                <?php foreach ($available_years as $year): ?>
                    <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $selected_sy == $year ? 'selected' : ''; ?>>
                        S.Y. <?php echo htmlspecialchars($year); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <button onclick="openModal('studentModal')" class="btn-primary">+ New Enrollment</button>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('studentModal')">&times;</span>
        <h3 style="color: var(--text-title); margin-bottom: 20px;">Enroll Student (S.Y. <?php echo htmlspecialchars($selected_sy); ?>)</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="add_student">
            
            <div class="form-group">
                <label>Learner Reference Number (LRN)</label>
                <input type="text" 
                       name="lrn" 
                       id="lrn_input_node"
                       placeholder="12-digit structural identity key" 
                       maxlength="12"
                       inputmode="numeric"
                       pattern="[0-9]{12}" 
                       title="Must be exactly 12 numeric units"
                       required>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Gender Profile</label>
                    <select name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Date of Birth</label>
                    <input type="date" name="birth_date" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Target Classroom Section</label>
                <select name="section_id" required>
                    <option value="">Select Target Destination</option>
                    <?php foreach ($active_sections as $sec): ?>
                        <option value="<?php echo $sec['id']; ?>">
                            Grade <?php echo $sec['grade_level']; ?> - <?php echo htmlspecialchars($sec['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal('studentModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Execute Enrollment</button>
            </div>
        </form>
    </div>
</div>

<div id="promoteModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('promoteModal')">&times;</span>
        <h3 style="color: var(--text-title); margin-bottom: 20px;">Promote Selected Batch</h3>
        <form method="POST" id="promotionForm">
            <input type="hidden" name="action_type" value="promote_students">
            
            <div class="form-group">
                <label>Target Advancement Destination Section</label>
                <select name="target_section_id" required>
                    <option value="">Select Target Destination Room</option>
                    <?php foreach ($all_global_sections as $gsec): ?>
                        <option value="<?php echo $gsec['id']; ?>">
                            S.Y. <?php echo htmlspecialchars($gsec['school_year']); ?> &rarr; Grade <?php echo $gsec['grade_level']; ?> - <?php echo htmlspecialchars($gsec['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="runtime_student_payload_container"></div>

            <div class="modal-footer">
                <button type="button" onclick="closeModal('promoteModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Confirm & Process Batch</button>
            </div>
        </form>
    </div>
</div>

<div class="card-panel">
    <div class="action-row">
        <span style="font-size: 13px; font-weight: 600; color: var(--text-subtitle);">
            Showing <?php echo count($enrolled_students); ?> of <?php echo $total_students; ?> active registry items
        </span>
        <button type="button" class="btn-secondary" onclick="prepareBatchPromotion()">Advancement Action Selected</button>
    </div>

    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="toggle_master_checkbox" onclick="toggleAllCheckboxes(this)"></th>
                    <th>System ID</th>
                    <th>LRN Reference</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Class Assignment Mapping</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrolled_students as $student): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="selected_student_identities[]" value="<?php echo $student['id']; ?>" class="student-checkbox">
                    </td>
                    <td style="font-weight: 700; color: var(--text-title); font-size: 13px;"><?php echo htmlspecialchars($student['school_id_number']); ?></td>
                    <td style="font-family: monospace; color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($student['lrn']); ?></td>
                    <td style="text-align: left; font-weight: 600; color: var(--text-title);">
                        <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($student['gender']); ?></td>
                    <td style="font-size: 13px; color: var(--text-muted);">
                        <?php echo $student['birth_date'] ? date('M d, Y', strtotime($student['birth_date'])) : '—'; ?>
                    </td>
                    <td>
                        <span class="badge">Grade <?php echo htmlspecialchars($student['grade_level'] . ' - ' . $student['section_name']); ?></span>
                    </td>
                    <td>
                        <a href="?delete_id=<?php echo $student['id']; ?>&sy=<?php echo urlencode($selected_sy); ?>" class="delete" onclick="return confirm('WARNING: Erasing this permanent profile completely clears all historical enrollment nodes and structural scores across all years. Proceed?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($enrolled_students) == 0): ?>
                    <tr><td colspan="8" style="color: var(--text-muted); padding: 40px;">No students tracked inside the database registry for S.Y. <?php echo htmlspecialchars($selected_sy); ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination-panel">
            <span style="font-size: 13px; color: var(--text-muted);">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <div class="pagination-links">
                <a href="?sy=<?php echo urlencode($selected_sy); ?>&page=<?php echo $page - 1; ?>" class="page-node <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo; Previous</a>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?sy=<?php echo urlencode($selected_sy); ?>&page=<?php echo $i; ?>" class="page-node <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <a href="?sy=<?php echo urlencode($selected_sy); ?>&page=<?php echo $page + 1; ?>" class="page-node <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next &raquo;</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = 'none'; } }

function toggleAllCheckboxes(masterBox) {
    const targets = document.querySelectorAll('.student-checkbox');
    targets.forEach(box => box.checked = masterBox.checked);
}

function prepareBatchPromotion() {
    const activeCheckedBoxes = document.querySelectorAll('.student-checkbox:checked');
    if (activeCheckedBoxes.length === 0) { 
        alert("Please highlight student profile checkbox selectors before firing advancement workflows."); 
        return; 
    }
    
    const payloadContainer = document.getElementById('runtime_student_payload_container');
    payloadContainer.innerHTML = ''; 
    
    activeCheckedBoxes.forEach(box => {
        const structuralHiddenNode = document.createElement('input');
        structuralHiddenNode.type = 'hidden';
        structuralHiddenNode.name = 'student_ids[]';
        structuralHiddenNode.value = box.value;
        payloadContainer.appendChild(structuralHiddenNode);
    });
    
    openModal('promoteModal');
}

document.getElementById('lrn_input_node').addEventListener('input', function (e) {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length > 12) {
        this.value = this.value.slice(0, 12);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>