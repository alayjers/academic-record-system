<?php
require_once 'includes/header.php';
require_once 'config/config.php';

// Strict Admin Security Check
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$message_type = '';

// 1. Gather all unique school years from sections to drive our master workspace filter
$years_stmt = $pdo->query("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
$available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

// Default to the newest historical tier if the admin hasn't clicked one yet
$selected_sy = isset($_GET['sy']) ? $_GET['sy'] : ($available_years[0] ?? '2025-2026');

// 2. Handle Form Engine Transactions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type'])) {
    
    // ACTION A: NEW ENROLLMENT TRANSACTION
    if ($_POST['action_type'] == 'add_student') {
        $lrn = trim($_POST['lrn']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $gender = $_POST['gender'];
        $section_id = intval($_POST['section_id']);

        $check = $pdo->prepare("SELECT id FROM students WHERE lrn = ?");
        $check->execute([$lrn]);
        
        if ($check->fetch()) {
            $message = "A student with this LRN already exists.";
            $message_type = "error";
        } else {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO students (lrn, first_name, last_name, gender) VALUES (?, ?, ?, ?)");
                $stmt->execute([$lrn, $first_name, $last_name, $gender]);
                $new_student_id = $pdo->lastInsertId();

                $enroll_stmt = $pdo->prepare("INSERT INTO enrollments (student_id, section_id) VALUES (?, ?)");
                $enroll_stmt->execute([$new_student_id, $section_id]);

                $pdo->commit();
                $message = "Student " . htmlspecialchars($first_name . ' ' . $last_name) . " enrolled successfully!";
                $message_type = "success";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Database fault: Unable to complete enrollment workflow.";
                $message_type = "error";
            }
        }
    }
    
    // ACTION B: BATCH PROMOTION MAP ENGINE
    else if ($_POST['action_type'] == 'promote_students') {
        $target_section_id = intval($_POST['target_section_id']);
        $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];

        if (empty($student_ids)) {
            $message = "No student profiles selected for batch advancement.";
            $message_type = "error";
        } else {
            try {
                $pdo->beginTransaction();
                // Safe unique inserts preventing duplicate assignments for the target school tier
                $promo_stmt = $pdo->prepare("INSERT IGNORE INTO enrollments (student_id, section_id) VALUES (?, ?)");
                foreach ($student_ids as $sid) {
                    $promo_stmt->execute([intval($sid), $target_section_id]);
                }
                $pdo->commit();
                
                // Redirect cleanly to show the target school year workspace to confirm success
                $target_sy_stmt = $pdo->prepare("SELECT school_year FROM sections WHERE id = ?");
                $target_sy_stmt->execute([$target_section_id]);
                $target_sy = $target_sy_stmt->fetchColumn();
                
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

// 3. Handle Single Record Erasures
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: students.php?sy=" . urlencode($selected_sy));
    exit();
}

// 4. Resolve Context Registries for Render Pass
$sections_stmt = $pdo->prepare("SELECT id, name, grade_level FROM sections WHERE school_year = ? ORDER BY grade_level ASC, name ASC");
$sections_stmt->execute([$selected_sy]);
$active_sections = $sections_stmt->fetchAll();

$all_sections_stmt = $pdo->query("SELECT id, name, grade_level, school_year FROM sections ORDER BY school_year DESC, grade_level ASC, name ASC");
$all_global_sections = $all_sections_stmt->fetchAll();

$students_stmt = $pdo->prepare("
    SELECT s.id, s.lrn, s.first_name, s.last_name, s.gender, sec.name as section_name, sec.grade_level 
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN sections sec ON e.section_id = sec.id
    WHERE sec.school_year = ?
    ORDER BY sec.grade_level ASC, sec.name ASC, s.last_name ASC, s.first_name ASC
");
$students_stmt->execute([$selected_sy]);
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
    [data-theme="dark"] .form-group label, body.dark-mode .form-group label { color: #a0aec0; }
    [data-theme="dark"] .form-group select, [data-theme="dark"] .form-group input, body.dark-mode .form-group select, body.dark-mode .form-group input { background: #2a2a2a; border-color: #3a3a3a; color: #ffffff; }
    .btn-primary { background-color: #00b4d8; color: #ffffff; border: 1px solid #00b4d8; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary:hover { background-color: #0096b4; }
    .btn-secondary { background-color: transparent; color: var(--text-title, #111111); border: 1px solid var(--border-card, #d1d5db); padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-secondary:hover { border-color: #00b4d8; color: #00b4d8; }
    [data-theme="dark"] .btn-secondary, body.dark-mode .btn-secondary { color: #fff; border-color: #3a3a3a; }
    .badge { background: #00b4d820; color: #00b4d8; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    .action-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
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
                <input type="text" name="lrn" placeholder="12-digit structural identity key" required pattern="\d{12}" title="Must be exactly 12 numeric tracking units">
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

            <div class="form-group">
                <label>Gender Profile</label>
                <select name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
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
            Found: <?php echo count($enrolled_students); ?> active entries matching criteria
        </span>
        <button type="button" class="btn-secondary" onclick="prepareBatchPromotion()">Advancement Action Selected</button>
    </div>

    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="toggle_master_checkbox" onclick="toggleAllCheckboxes(this)"></th>
                    <th>LRN Reference</th>
                    <th>Full Name</th>
                    <th>Gender</th>
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
                    <td style="font-family: monospace; color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($student['lrn']); ?></td>
                    <td style="text-align: left; font-weight: 600; color: var(--text-title);">
                        <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($student['gender']); ?></td>
                    <td>
                        <span class="badge">Grade <?php echo htmlspecialchars($student['grade_level'] . ' - ' . $student['section_name']); ?></span>
                    </td>
                    <td>
                        <a href="?delete_id=<?php echo $student['id']; ?>&sy=<?php echo urlencode($selected_sy); ?>" class="delete" onclick="return confirm('WARNING: Erasing this permanent profile completely clears all historical enrollment nodes and structural scores across all years. Proceed?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($enrolled_students) == 0): ?>
                    <tr><td colspan="6" style="color: var(--text-muted); padding: 40px;">No students tracked inside the database registry for S.Y. <?php echo htmlspecialchars($selected_sy); ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
</script>

<?php require_once 'includes/footer.php'; ?>