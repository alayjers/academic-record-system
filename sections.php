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

// Handle Form Submission: Create New Section
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type']) && $_POST['action_type'] == 'create_section') {
    $name = trim($_POST['name']);
    $grade_level = intval($_POST['grade_level']);
    $school_year = trim($_POST['school_year']);

    if (empty($name) || empty($grade_level) || empty($school_year)) {
        $message = "All fields are required.";
        $message_type = "error";
    } else {
        // Check if this exact section already exists for this grade level and school year
        $check = $pdo->prepare("SELECT id FROM sections WHERE name = ? AND grade_level = ? AND school_year = ?");
        $check->execute([$name, $grade_level, $school_year]);
        
        if ($check->fetch()) {
            $message = "Section '$name' already exists for Grade $grade_level in School Year $school_year.";
            $message_type = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO sections (name, grade_level, school_year) VALUES (?, ?, ?)");
            $stmt->execute([$name, $grade_level, $school_year]);
            $message = "Section '" . htmlspecialchars($name) . "' created successfully!";
            $message_type = "success";
        }
    }
}

// Handle Section Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Check if students are currently enrolled in this section before deleting
    $check_enrollment = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE section_id = ?");
    $check_enrollment->execute([$delete_id]);
    
    if ($check_enrollment->fetchColumn() > 0) {
        $message = "Cannot delete section. There are currently students enrolled in it.";
        $message_type = "error";
    } else {
        $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: sections.php');
        exit();
    }
}

// Fetch all sections with an active student count counter
$stmt = $pdo->query("
    SELECT s.*, COUNT(e.id) as student_count 
    FROM sections s
    LEFT JOIN enrollments e ON s.id = e.section_id
    GROUP BY s.id
    ORDER BY s.school_year DESC, s.grade_level ASC, s.name ASC
");
$all_sections = $stmt->fetchAll();
?>

<style>
    .header-action-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { background-color: #ffffff; color: #111111; margin: 10% auto; padding: 24px; border: 1px solid #e0e0e0; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: relative; }
    .close-btn { position: absolute; right: 20px; top: 16px; font-size: 28px; font-weight: bold; color: #666666; cursor: pointer; }
    .close-btn:hover { color: #111111; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 12px; font-weight: 600; color: #666666; }
    .form-group select, .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; color: #111111; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    [data-theme="dark"] .modal-content, body.dark-mode .modal-content { background-color: #1e1e1e; color: #ecf0f1; border-color: #2a2a2a; }
    [data-theme="dark"] .form-group label, body.dark-mode .form-group label { color: #a0aec0; }
    [data-theme="dark"] .form-group select, [data-theme="dark"] .form-group input, body.dark-mode .form-group select, body.dark-mode .form-group input { background: #2a2a2a; border-color: #3a3a3a; color: #ffffff; }
    .btn-primary { background-color: #00b4d8; color: #ffffff; border: 1px solid #00b4d8; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary:hover { background-color: #0096b4; border-color: #0096b4; }
    .btn-secondary { background-color: transparent; color: var(--text-title, #111111); border: 1px solid var(--border-card, #d1d5db); padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .badge-count { background: var(--mode-btn-bg, rgba(0, 0, 0, 0.06)); padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
</style>

<div class="header-action-area">
    <h1>Master Sections Registry</h1>
    <button onclick="openModal('sectionModal')" class="btn-primary">+ Add New Section</button>
</div>

<?php if (!empty($message)): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div id="sectionModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('sectionModal')">&times;</span>
        <h3 style="color: var(--text-title); margin-bottom: 20px;">Create New Classroom Section</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="create_section">
            
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="name" placeholder="e.g., Einstein, Newton, Diamond" required>
            </div>
            
            <div class="form-group">
                <label>Grade Level</label>
                <select name="grade_level" required>
                    <option value="">Select Grade</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>School Year</label>
                <select name="school_year" required>
                    <option value="2025-2026">2025-2026</option>
                    <option value="2026-2027">2026-2027</option>
                    <option value="2027-2028">2027-2028</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal('sectionModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Section</button>
            </div>
        </form>
    </div>
</div>

<div class="card-panel">
    <div class="table-container">
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Grade Level</th>
                    <th>Section Name</th>
                    <th>School Year</th>
                    <th>Active Roster</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_sections as $sec): ?>
                <tr>
                    <td style="font-weight: 600; color: var(--text-title);">Grade <?php echo $sec['grade_level']; ?></td>
                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($sec['name']); ?></td>
                    <td style="color: var(--text-subtitle);"><?php echo htmlspecialchars($sec['school_year']); ?></td>
                    <td><span class="badge-count"><?php echo $sec['student_count']; ?> Students</span></td>
                    <td>
                        <a href="?delete_id=<?php echo $sec['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this section?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($all_sections) == 0): ?>
                    <tr><td colspan="5" style="color: var(--text-muted); padding: 20px;">No sections registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = 'none'; } }
</script>

<?php require_once 'includes/footer.php'; ?>