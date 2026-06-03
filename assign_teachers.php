<?php
require_once 'includes/header.php';
require_once 'config/config.php';
// Production Sync tracking version 1.0.1

if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action_type']) && $_POST['action_type'] == 'create_teacher') {
        $full_name = $_POST['full_name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "Username already exists. Choose a different one.";
            $message_type = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $full_name, $role]);
            $message = "Teacher account for " . htmlspecialchars($full_name) . " created successfully!";
            $message_type = "success";
        }
    } else {
        $teacher_id = $_POST['teacher_id'];
        $section = $_POST['section'];
        $subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
        $assignment_type = $_POST['assignment_type'];
        
        if ($assignment_type == 'subject') {
            $check = $pdo->prepare("SELECT id FROM teacher_subject_section WHERE teacher_id = ? AND section = ? AND subject_id = ?");
            $check->execute([$teacher_id, $section, $subject_id]);
            if ($check->fetch()) {
                $message = "Teacher already assigned to this section for this subject.";
                $message_type = "error";
            } else {
                $stmt = $pdo->prepare("INSERT INTO teacher_subject_section (teacher_id, section, subject_id) VALUES (?, ?, ?)");
                $stmt->execute([$teacher_id, $section, $subject_id]);
                $message = "Teacher assigned successfully!";
                $message_type = "success";
            }
        } elseif ($assignment_type == 'advisory') {
            $check = $pdo->prepare("SELECT id FROM advisory_section WHERE teacher_id = ? AND section = ?");
            $check->execute([$teacher_id, $section]);
            if ($check->fetch()) {
                $message = "Teacher already assigned as advisory for $section.";
                $message_type = "error";
            } else {
                $stmt = $pdo->prepare("INSERT INTO advisory_section (teacher_id, section) VALUES (?, ?)");
                $stmt->execute([$teacher_id, $section]);
                $message = "Teacher assigned as advisory for $section successfully!";
                $message_type = "success";
            }
        }
    }
}

if (isset($_GET['delete_subject_group'])) {
    $teacher_id = $_GET['teacher_id'];
    $subject_id = $_GET['subject_id'];
    $stmt = $pdo->prepare("DELETE FROM teacher_subject_section WHERE teacher_id = ? AND subject_id = ?");
    $stmt->execute([$teacher_id, $subject_id]);
    header('Location: assign_teachers.php');
    exit();
}

if (isset($_GET['delete_advisory_group'])) {
    $teacher_id = $_GET['teacher_id'];
    $stmt = $pdo->prepare("DELETE FROM advisory_section WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    header('Location: assign_teachers.php');
    exit();
}

$teachers = $pdo->query("SELECT id, username, full_name, role FROM users WHERE role != 'admin' ORDER BY full_name")->fetchAll();

$sections = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section")->fetchAll();

$all_subjects = $pdo->query("SELECT id, name as subject_name FROM subjects ORDER BY name ASC")->fetchAll();

$stmt = $pdo->query("
    SELECT tss.teacher_id, tss.subject_id, u.full_name as teacher_name, s.name as subject_name,
           GROUP_CONCAT(tss.section ORDER BY tss.section SEPARATOR ', ') as combined_sections
    FROM teacher_subject_section tss 
    JOIN users u ON tss.teacher_id = u.id 
    JOIN subjects s ON tss.subject_id = s.id
    GROUP BY tss.teacher_id, tss.subject_id, u.full_name, s.name
    ORDER BY u.full_name ASC, s.name ASC
");
$subject_assignments = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT asec.teacher_id, u.full_name as teacher_name,
           GROUP_CONCAT(asec.section ORDER BY asec.section SEPARATOR ', ') as combined_sections
    FROM advisory_section asec 
    JOIN users u ON asec.teacher_id = u.id 
    GROUP BY asec.teacher_id, u.full_name
    ORDER BY u.full_name ASC
");
$advisory_assignments = $stmt->fetchAll();
?>

<style>
    .header-action-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .action-buttons { display: flex; gap: 10px; }
    .tab-bar { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 8px; }
    .tab { background: transparent; border: 1px solid transparent; color: var(--text-muted); padding: 10px 20px; font-size: 14px; font-weight: 500; cursor: pointer; border-radius: 8px; transition: all 0.2s ease; }
    .tab:hover, .tab.active { background: var(--mode-btn-bg); border-color: var(--mode-btn-border); color: var(--mode-btn-text); font-weight: 600; }
    .content-tab { display: none; }
    .content-tab.active { display: block; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { background-color: #ffffff; color: #111111; margin: 10% auto; padding: 24px; border: 1px solid #e0e0e0; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: relative; }
    .close-btn { position: absolute; right: 20px; top: 16px; font-size: 28px; font-weight: bold; color: #666666; cursor: pointer; }
    .close-btn:hover { color: #111111; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 12px; font-weight: 600; color: #666666; }
    .form-group select, .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; color: #111111; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    [data-theme="dark"] .modal-content, body.dark-mode .modal-content { background-color: #1e1e1e; color: #ecf0f1; border-color: #2a2a2a; }
    [data-theme="dark"] .close-btn, body.dark-mode .close-btn { color: #a0aec0; }
    [data-theme="dark"] .close-btn:hover, body.dark-mode .close-btn:hover { color: #ffffff; }
    [data-theme="dark"] .form-group label, body.dark-mode .form-group label { color: #a0aec0; }
    [data-theme="dark"] .form-group select, [data-theme="dark"] .form-group input, body.dark-mode .form-group select, body.dark-mode .form-group input { background: #2a2a2a; border-color: #3a3a3a; color: #ffffff; }
    .btn-primary { background-color: #00b4d8; color: #ffffff; border: 1px solid #00b4d8; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary:hover { background-color: #0096b4; border-color: #0096b4; }
    .btn-secondary { background-color: transparent; color: var(--text-title, #111111); border: 1px solid var(--border-card, #d1d5db); padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-secondary:hover { background-color: var(--mode-btn-bg, rgba(0, 0, 0, 0.04)); border-color: var(--mode-btn-border, #00b4d8); color: var(--mode-btn-text, #00b4d8); }
    [data-theme="dark"] .btn-secondary, body.dark-mode .btn-secondary { color: #ffffff; border-color: #3a3a3a; }
    [data-theme="dark"] .btn-secondary:hover, body.dark-mode .btn-secondary:hover { background-color: rgba(255, 255, 255, 0.05); border-color: #00b4d8; color: #00b4d8; }
</style>

<div class="header-action-area">
    <h1>Assign & Manage Teachers</h1>
    <div class="action-buttons">
        <button onclick="openModal('teacherModal')" class="btn-secondary">+ New Teacher Profile</button>
        <button onclick="openModal('assignModal')" class="btn-primary">+ Assign Subject/Section</button>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div id="teacherModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('teacherModal')">&times;</span>
        <h3 style="color: var(--text-title); margin-bottom: 20px;">Add New Teacher Profile</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="create_teacher">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="e.g., Mr. John Doe" required>
            </div>
            <div class="form-group">
                <label>Username (For Login)</label>
                <input type="text" name="username" placeholder="e.g., johndoe01" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Temporary password" required>
            </div>
            <div class="form-group">
                <label>Account Role</label>
                <select name="role" required>
                    <option value="subject_teacher">Subject Teacher</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('teacherModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<div id="assignModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('assignModal')">&times;</span>
        <h3 style="color: var(--text-title); margin-bottom: 20px;">New Teacher Assignment</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="assign_sections">
            <div class="form-group">
                <label>Select Teacher</label>
                <select name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select Section</label>
                <select name="section" required>
                    <option value="">Select Section</option>
                    <?php foreach ($sections as $s): ?>
                        <option value="<?php echo htmlspecialchars($s['section']); ?>"><?php echo htmlspecialchars($s['section']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Assignment Type</label>
                <select name="assignment_type" id="assignment_type" required onchange="toggleSubject()">
                    <option value="subject">Subject Teacher</option>
                    <option value="advisory">Advisory Teacher</option>
                </select>
            </div>
            <div class="form-group" id="subject_field_wrapper">
                <label>Subject</label>
                <select name="subject_id" id="subject_field">
                    <option value="">Select Subject</option>
                    <?php foreach ($all_subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('assignModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<div class="tab-bar">
    <button class="tab active" onclick="showTab('subject')">Subject Teacher Assignments</button>
    <button class="tab" onclick="showTab('advisory')">Advisory Teacher Assignments</button>
</div>

<div id="subject_tab" class="content-tab active card-panel">
    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Subject</th>
                    <th>Assigned Sections</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subject_assignments as $a): ?>
                <tr>
                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td style="color: var(--text-subtitle); font-weight: 600;"><?php echo htmlspecialchars($a['subject_name']); ?></td>
                    <td><span style="font-weight: 600; color: var(--text-title);"><?php echo htmlspecialchars($a['combined_sections']); ?></span></td>
                    <td><a href="?delete_subject_group=1&teacher_id=<?php echo $a['teacher_id']; ?>&subject_id=<?php echo $a['subject_id']; ?>" class="delete" onclick="return confirm('Remove assignments?')">Remove All</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($subject_assignments) == 0): ?>
                    <tr><td colspan="4" style="color: var(--text-muted); padding: 20px;">No subject teacher assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="advisory_tab" class="content-tab card-panel">
    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Assigned Sections</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($advisory_assignments as $a): ?>
                <tr>
                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td><span style="font-weight: 600; color: var(--text-title);"><?php echo htmlspecialchars($a['combined_sections']); ?></span></td>
                    <td><a href="?delete_advisory_group=1&teacher_id=<?php echo $a['teacher_id']; ?>" class="delete" onclick="return confirm('Remove all advisory assignments?')">Remove All</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($advisory_assignments) == 0): ?>
                    <tr><td colspan="3" style="color: var(--text-muted); padding: 20px;">No advisory teacher assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = 'none'; } }
function toggleSubject() {
    var type = document.getElementById('assignment_type').value;
    var wrapper = document.getElementById('subject_field_wrapper');
    if (type == 'advisory') { wrapper.style.display = 'none'; } else { wrapper.style.display = 'flex'; }
}
function showTab(tab) {
    document.getElementById('subject_tab').classList.remove('active');
    document.getElementById('advisory_tab').classList.remove('active');
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    if (tab == 'subject') {
        document.getElementById('subject_tab').classList.add('active');
        document.querySelectorAll('.tab')[0].classList.add('active');
    } else {
        document.getElementById('advisory_tab').classList.add('active');
        document.querySelectorAll('.tab')[1].classList.add('active');
    }
}
toggleSubject();
</script>

<?php require_once 'includes/footer.php'; ?>