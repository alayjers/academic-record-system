<?php
require_once 'includes/header.php';
require_once 'config/config.php';

// Admin only
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$message_type = '';

// Handle assignment
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

// Handle deletion
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

// Get all teachers (excluding admin)
$teachers = $pdo->query("SELECT id, username, full_name, role FROM users WHERE role != 'admin' ORDER BY full_name")->fetchAll();

// Get all sections from students
$sections = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section")->fetchAll();

// Get current assignments
$subject_assignments = $pdo->query("
    SELECT tss.*, u.full_name as teacher_name 
    FROM teacher_subject_section tss 
    JOIN users u ON tss.teacher_id = u.id 
    ORDER BY u.full_name, tss.section
")->fetchAll();

$advisory_assignments = $pdo->query("
    SELECT asec.*, u.full_name as teacher_name 
    FROM advisory_section asec 
    JOIN users u ON asec.teacher_id = u.id 
    ORDER BY u.full_name, asec.section
")->fetchAll();
?>

<style>
    .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
    .card h3 { margin-top: 0; }
    select, button { padding: 8px 12px; margin: 5px; }
    button { background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 3px; }
    button:hover { background: #45a049; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
    .delete { color: red; text-decoration: none; }
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .tab { padding: 10px 20px; background: #e0e0e0; cursor: pointer; border-radius: 5px; }
    .tab.active { background: #4CAF50; color: white; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>

<h1>Assign Teachers to Sections</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Assignment Form -->
<div class="card">
    <h3>New Assignment</h3>
    <form method="POST">
        <select name="teacher_id" required>
            <option value="">Select Teacher</option>
            <?php foreach ($teachers as $t): ?>
                <option value="<?php echo $t['id']; ?>">
                    <?php echo htmlspecialchars($t['full_name']); ?> (<?php echo $t['role']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="section" required>
            <option value="">Select Section</option>
            <?php foreach ($sections as $s): ?>
                <option value="<?php echo htmlspecialchars($s['section']); ?>">
                    <?php echo htmlspecialchars($s['section']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="assignment_type" id="assignment_type" required onchange="toggleSubject()">
            <option value="subject">Subject Teacher</option>
            <option value="advisory">Advisory Teacher</option>
        </select>
        
        <input type="text" name="subject" id="subject_field" placeholder="Subject (e.g., Math)">
        
        <button type="submit">Assign</button>
    </form>
</div>

<!-- Current Assignments Tabs -->
<div class="tabs">
    <div class="tab active" onclick="showTab('subject')">Subject Teacher Assignments</div>
    <div class="tab" onclick="showTab('advisory')">Advisory Teacher Assignments</div>
</div>

<div id="subject_tab" class="tab-content active">
    <div class="card">
        <h3>Subject Teacher Assignments</h3>
        <table>
            <thead>
                <tr><th>Teacher</th><th>Section</th><th>Subject</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($subject_assignments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['section']); ?></td>
                    <td><?php echo htmlspecialchars($a['subject']); ?></td>
                    <td><a href="?delete_subject=<?php echo $a['id']; ?>" class="delete" onclick="return confirm('Remove this assignment?')">Remove</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($subject_assignments) == 0): ?>
                    <tr><td colspan="4">No subject teacher assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="advisory_tab" class="tab-content">
    <div class="card">
        <h3>Advisory Teacher Assignments</h3>
        <table>
            <thead>
                <tr><th>Teacher</th><th>Section</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($advisory_assignments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['section']); ?></td>
                    <td><a href="?delete_advisory=<?php echo $a['id']; ?>" class="delete" onclick="return confirm('Remove this assignment?')">Remove</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($advisory_assignments) == 0): ?>
                    <tr><td colspan="3">No advisory teacher assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleSubject() {
    var type = document.getElementById('assignment_type').value;
    var subjectField = document.getElementById('subject_field');
    if (type == 'advisory') {
        subjectField.style.display = 'none';
        subjectField.value = '';
    } else {
        subjectField.style.display = 'inline-block';
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