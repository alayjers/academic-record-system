<?php
require_once 'includes/header.php';
require_once 'config/config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $lrn = trim($_POST['lrn']);
        
        if (!preg_match('/^\d{12}$/', $lrn)) {
            $message = "Error: LRN must be exactly 12 numeric digits.";
            $message_type = "error";
        } else {
            $current_year = date('Y');
            $term_suffix = "1"; 
            $prefix = $current_year . $term_suffix . "-";
            
            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id LIKE ? ORDER BY student_id DESC LIMIT 1");
            $stmt->execute([$prefix . '%']);
            $last_student = $stmt->fetch();
            
            if ($last_student) {
                $parts = explode('-', $last_student['student_id']);
                $next_sequence = intval($parts[1]) + 1;
            } else {
                $next_sequence = 1;
            }
            
            $generated_student_id = $prefix . str_pad($next_sequence, 5, '0', STR_PAD_LEFT);
            
            $full_name = trim($_POST['last_name']) . ', ' . trim($_POST['first_name']);
            
            $stmt = $pdo->prepare("INSERT INTO students (student_id, lrn, name, first_name, last_name, birth_date, gender, grade_level, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $generated_student_id, 
                $lrn,
                $full_name, 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['birth_date'],
                $_POST['gender'],
                $_POST['grade_level'], 
                $_POST['section']
            ]);
            $message = "Student added successfully! Generated ID: " . $generated_student_id;
            $message_type = "success";
        }
    } elseif (isset($_POST['edit'])) {
        $lrn = trim($_POST['lrn']);
        
        if (!preg_match('/^\d{12}$/', $lrn)) {
            $message = "Error: LRN must be exactly 12 numeric digits.";
            $message_type = "error";
        } else {
            $full_name = trim($_POST['last_name']) . ', ' . trim($_POST['first_name']);
            
            $stmt = $pdo->prepare("UPDATE students SET student_id = ?, lrn = ?, name = ?, first_name = ?, last_name = ?, birth_date = ?, gender = ?, grade_level = ?, section = ? WHERE id = ?");
            $stmt->execute([
                $_POST['student_id'], 
                $lrn,
                $full_name, 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['birth_date'],
                $_POST['gender'],
                $_POST['grade_level'], 
                $_POST['section'], 
                $_POST['id']
            ]);
            $message = "Student updated successfully!";
            $message_type = "success";
        }
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: students.php');
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $like = "%$search%";
    $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ? OR lrn LIKE ? OR section LIKE ? ORDER BY last_name, first_name");
    $stmt->execute([$like, $like, $like, $like, $like, $like]);
    $students = $stmt->fetchAll();
} else {
    $students = $pdo->query("SELECT * FROM students ORDER BY last_name, first_name")->fetchAll();
}
?>

<h1>Manage Students</h1>

<?php if (!empty($message)): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card-panel" style="margin-bottom: 24px;">
    <h3 style="color: var(--text-title); margin-bottom: 16px;">Add New Student</h3>
    <form method="POST" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
        <input type="hidden" name="add" value="1">
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 140px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Student ID</label>
            <input type="text" placeholder="Auto-generated" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed; background: var(--border-card);">
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 140px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">LRN</label>
            <input type="text" name="lrn" placeholder="12-digit LRN" required maxlength="12" pattern="\d{12}" title="LRN must be exactly 12 digits" style="width: 100%;">
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1.5; min-width: 160px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">First Name</label>
            <input type="text" name="first_name" placeholder="First Name" required style="width: 100%;">
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1.5; min-width: 160px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Last Name</label>
            <input type="text" name="last_name" placeholder="Last Name" required style="width: 100%;">
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 140px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Birth Date</label>
            <input type="date" name="birth_date" required style="width: 100%; height: 45px; display: block; padding: 0 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--input-text);">
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 120px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Gender</label>
            <select name="gender" required style="width: 100%; height: 45px; display: block;">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 120px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Grade Level</label>
            <select name="grade_level" required style="width: 100%; height: 45px; display: block;">
                <option value="7">Grade 7</option>
                <option value="8">Grade 8</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
            </select>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 120px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Section</label>
            <input type="text" name="section" placeholder="e.g., Diamond" required style="width: 100%;">
        </div>
        
        <button type="submit" style="height: 45px; padding: 0 24px;">Add Student</button>
    </form>
</div>

<div class="card-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
        <h3 style="color: var(--text-title);">Student Directory</h3>
        <form method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search records..." style="padding: 10px 14px;">
            <button type="submit" style="padding: 10px 16px;">Search</button>
            <?php if (!empty($search)): ?>
                <a href="students.php" style="background: rgba(255,255,255,0.05); padding: 12px 16px; border-radius: 10px; color: var(--text-muted); text-decoration: none; font-size: 14px; display: inline-block;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>LRN</th>
                    <th style="text-align: left; padding-left: 12px;">Last Name</th>
                    <th style="text-align: left; padding-left: 12px;">First Name</th>
                    <th>Birth Date</th>
                    <th>Gender</th>
                    <th>Grade Level</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td style="font-weight: 600; color: var(--text-subtitle);"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($s['lrn'] ?? 'N/A'); ?></td>
                    <td style="text-align: left; padding-left: 12px; font-weight: 500;"><?php echo htmlspecialchars($s['last_name'] ?? ''); ?></td>
                    <td style="text-align: left; padding-left: 12px; font-weight: 500;"><?php echo htmlspecialchars($s['first_name'] ?? ''); ?></td>
                    <td><?php echo !empty($s['birth_date']) ? date('M d, Y', strtotime($s['birth_date'])) : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($s['gender'] ?? 'N/A'); ?></td>
                    <td>Grade <?php echo htmlspecialchars($s['grade_level']); ?></td>
                    <td><span style="font-weight: 600; color: var(--text-title);"><?php echo htmlspecialchars($s['section']); ?></span></td>
                    <td>
                        <a href="#" class="edit" onclick="openEditModal(<?php echo $s['id']; ?>, '<?php echo addslashes($s['student_id']); ?>', '<?php echo addslashes($s['lrn'] ?? ''); ?>', '<?php echo addslashes($s['first_name'] ?? ''); ?>', '<?php echo addslashes($s['last_name'] ?? ''); ?>', '<?php echo $s['birth_date'] ?? ''; ?>', '<?php echo addslashes($s['gender'] ?? 'Male'); ?>', '<?php echo $s['grade_level']; ?>', '<?php echo addslashes($s['section']); ?>')">Edit</a>
                        <a href="?delete=<?php echo $s['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to remove this student record?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($students) == 0): ?>
                    <tr><td colspan="9" style="color: var(--text-muted); padding: 30px;">No student records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" style="display:none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
    <div class="card-panel" style="max-width: 500px; margin: 3% auto; position: relative; border: 1px solid rgba(255,255,255,0.1); padding: 28px; max-height: 90vh; overflow-y: auto;">
        <h3 style="color: var(--text-title); margin-bottom: 20px;">Modify Student Record</h3>
        <form method="POST" id="editForm" style="display: flex; flex-direction: column; gap: 14px;">
            <input type="hidden" name="edit" value="1">
            <input type="hidden" name="id" id="edit_id">
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Student ID</label>
                <input type="text" name="student_id" id="edit_student_id" required readonly style="width: 100%; opacity: 0.7; background: var(--border-card); cursor: not-allowed;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">LRN</label>
                <input type="text" name="lrn" id="edit_lrn" required maxlength="12" pattern="\d{12}" title="LRN must be exactly 12 digits" style="width: 100%;">
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">First Name</label>
                <input type="text" name="first_name" id="edit_first_name" required style="width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Last Name</label>
                <input type="text" name="last_name" id="edit_last_name" required style="width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Birth Date</label>
                <input type="date" name="birth_date" id="edit_birth_date" required style="width: 100%; height: 45px; display: block; padding: 0 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--input-text);">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Gender</label>
                <select name="gender" id="edit_gender" required style="width: 100%; height: 45px; display: block;">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Grade Level</label>
                <select name="grade_level" id="edit_grade_level" required style="width: 100%; height: 45px; display: block;">
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Section</label>
                <input type="text" name="section" id="edit_section" required style="width: 100%;">
            </div>
            
            <div style="display: gap: 10px; margin-top: 10px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" style="background: rgba(255,255,255,0.05); color: #ffffff; height: 42px; padding: 0 20px;">Cancel</button>
                <button type="submit" style="height: 42px; padding: 0 20px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, student_id, lrn, first_name, last_name, birth_date, gender, grade_level, section) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_student_id').value = student_id;
    document.getElementById('edit_lrn').value = lrn;
    document.getElementById('edit_first_name').value = first_name;
    document.getElementById('edit_last_name').value = last_name;
    document.getElementById('edit_birth_date').value = birth_date;
    document.getElementById('edit_gender').value = gender;
    document.getElementById('edit_grade_level').value = grade_level;
    document.getElementById('edit_section').value = section;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeEditModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>