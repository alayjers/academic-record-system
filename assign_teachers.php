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
    $teacher_id = $_POST['teacher_id'];
    $section = $_POST['section'];
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $assignment_type = $_POST['assignment_type'];
    
    if ($assignment_type == 'subject') {
        $check = $pdo->prepare("SELECT id FROM teacher_subject_section WHERE teacher_id = ? AND section = ? AND subject = ?");
        $check->execute([$teacher_id, $section, $subject]);
        if ($check->fetch()) {
            $message = "Teacher already assigned to $section for $subject.";
            $message_type = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO teacher_subject_section (teacher_id, section, subject) VALUES (?, ?, ?)");
            $stmt->execute([$teacher_id, $section, $subject]);
            $message = "Teacher assigned to $section for $subject successfully!";
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

if (isset($_GET['delete_subject'])) {
    $stmt = $pdo->prepare("DELETE FROM teacher_subject_section WHERE id = ?");
    $stmt->execute([$_GET['delete_subject']]);
    header('Location: assign_teachers.php');
    exit();
}

if (isset($_GET['delete_advisory'])) {
    $stmt = $pdo->prepare("DELETE FROM advisory_section WHERE id = ?");
    $stmt->execute([$_GET['delete_advisory']]);
    header('Location: assign_teachers.php');
    exit();
}

$teachers = $pdo->query("SELECT id, username, full_name, role FROM users WHERE role != 'admin' ORDER BY full_name")->fetchAll();

$sections = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section")->fetchAll();

$stmt = $pdo->query("
    SELECT tss.*, u.full_name as teacher_name 
    FROM teacher_subject_section tss 
    JOIN users u ON tss.teacher_id = u.id 
    ORDER BY u.full_name, tss.section
");
$subject_assignments = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT asec.*, u.full_name as teacher_name 
    FROM advisory_section asec 
    JOIN users u ON asec.teacher_id = u.id 
    ORDER BY u.full_name, asec.section
");
$advisory_assignments = $stmt->fetchAll();
?>

<style>
    .tab-bar { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 8px; }
    .tab { background: transparent; border: 1px solid transparent; color: var(--text-muted); padding: 10px 20px; font-size: 14px; font-weight: 500; cursor: pointer; border-radius: 8px; transition: all 0.2s ease; }
    .tab:hover, .tab.active { background: var(--mode-btn-bg); border-color: var(--mode-btn-border); color: var(--mode-btn-text); font-weight: 600; }
    .content-tab { display: none; }
    .content-tab.active { display: block; }
</style>

<h1>Assign Teachers to Sections</h1>

<?php if (!empty($message)): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card-panel">
    <h3 style="color: var(--text-title); margin-bottom: 20px;">New Assignment</h3>
    <form method="POST" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 220px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Select Teacher</label>
            <select name="teacher_id" required style="width: 100%;">
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>">
                        <?php echo htmlspecialchars($t['full_name']); ?> (<?php echo htmlspecialchars($t['role']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Select Section</label>
            <select name="section" required style="width: 100%;">
                <option value="">Select Section</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['section']); ?>">
                        <?php echo htmlspecialchars($s['section']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Assignment Type</label>
            <select name="assignment_type" id="assignment_type" required onchange="toggleSubject()" style="width: 100%;">
                <option value="subject">Subject Teacher</option>
                <option value="advisory">Advisory Teacher</option>
            </select>
        </div>
        
        <div id="subject_field_wrapper" style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Subject</label>
            <input type="text" name="subject" id="subject_field" placeholder="e.g., Mathematics" style="width: 100%;">
        </div>
        
        <button type="submit">Assign</button>
    </form>
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
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subject_assignments as $a): ?>
                <tr>
                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td><span style="font-weight: 600; color: var(--text-title);"><?php echo htmlspecialchars($a['section']); ?></span></td>
                    <td style="color: var(--text-subtitle); font-weight: 600;"><?php echo htmlspecialchars($a['subject']); ?></td>
                    <td><a href="?delete_subject=<?php echo $a['id']; ?>" class="delete" onclick="return confirm('Remove this assignment?')">Remove</a></td>
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
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($advisory_assignments as $a): ?>
                <tr>
                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td><span style="font-weight: 600; color: var(--text-title);"><?php echo htmlspecialchars($a['section']); ?></span></td>
                    <td><a href="?delete_advisory=<?php echo $a['id']; ?>" class="delete" onclick="return confirm('Remove this assignment?')">Remove</a></td>
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
function toggleSubject() {
    var type = document.getElementById('assignment_type').value;
    var subjectFieldWrapper = document.getElementById('subject_field_wrapper');
    var subjectInput = document.getElementById('subject_field');
    if (type == 'advisory') {
        subjectFieldWrapper.style.display = 'none';
        subjectInput.value = '';
    } else {
        subjectFieldWrapper.style.display = 'flex';
    }
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