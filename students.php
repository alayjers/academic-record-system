<?php
require_once 'includes/header.php';
require_once 'config/config.php';

// Restrict to admin only
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: students.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO students (student_id, name, grade_level, section) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['student_id'], $_POST['name'], $_POST['grade_level'], $_POST['section']]);
        $message = "Student added successfully!";
        $message_type = "success";
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE students SET student_id = ?, name = ?, grade_level = ?, section = ? WHERE id = ?");
        $stmt->execute([$_POST['student_id'], $_POST['name'], $_POST['grade_level'], $_POST['section'], $_POST['id']]);
        $message = "Student updated successfully!";
        $message_type = "success";
    } elseif (isset($_POST['import_csv'])) {
        // CSV Import Logic (same as before)
        if ($_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            $headers = fgetcsv($handle);
            $import_count = 0;
            $error_count = 0;
            $errors = [];
            
            while (($data = fgetcsv($handle)) !== false) {
                $student_id = trim($data[0]);
                $name = trim($data[1]);
                $grade_level = trim($data[2]);
                $section = trim($data[3]);
                
                if (empty($student_id) || empty($name) || empty($grade_level) || empty($section)) {
                    $error_count++;
                    $errors[] = "Missing data in row: " . implode(',', $data);
                    continue;
                }
                
                $check = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
                $check->execute([$student_id]);
                if ($check->fetch()) {
                    $error_count++;
                    $errors[] = "Duplicate student_id: $student_id";
                    continue;
                }
                
                $stmt = $pdo->prepare("INSERT INTO students (student_id, name, grade_level, section) VALUES (?, ?, ?, ?)");
                $stmt->execute([$student_id, $name, $grade_level, $section]);
                $import_count++;
            }
            
            fclose($handle);
            
            if ($import_count > 0) {
                $message = "Imported $import_count students successfully.";
                if ($error_count > 0) {
                    $message .= " $error_count rows skipped with errors.";
                }
                $message_type = "success";
            } else {
                $message = "No students imported. Please check your CSV format.";
                $message_type = "error";
            }
            
            if (!empty($errors)) {
                $_SESSION['import_errors'] = $errors;
            }
        } else {
            $message = "File upload failed.";
            $message_type = "error";
        }
    }
    
    header('Location: students.php');
    exit();
}

$import_errors = isset($_SESSION['import_errors']) ? $_SESSION['import_errors'] : [];
unset($_SESSION['import_errors']);

$students = $pdo->query("SELECT * FROM students ORDER BY grade_level, section, name")->fetchAll();
?>

<style>
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .csv-import { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; }
    .csv-import input[type="file"] { margin: 10px 0; }
    .btn-import { background: #2196F3; color: white; border: none; padding: 5px 15px; cursor: pointer; border-radius: 3px; }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
    .modal-content { background: white; margin: 10% auto; padding: 20px; width: 400px; border-radius: 5px; position: relative; }
    .modal-content input, .modal-content select { width: 100%; padding: 8px; margin: 8px 0; }
    .close { position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer; }
    .btn-edit { background: #FF9800; color: white; padding: 2px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; cursor: pointer; display: inline-block; }
</style>

<h1>Student Management</h1>

<?php if(isset($message)): ?>
    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<?php if(!empty($import_errors)): ?>
    <div class="error-list">
        <strong>Import Errors:</strong>
        <ul>
            <?php foreach($import_errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add Single Student Form -->
<div style="background:white; padding:20px; margin-bottom:20px; border-radius:5px;">
    <h3>Add New Student</h3>
    <form method="POST">
        <input type="text" name="student_id" placeholder="Student ID" required style="padding:5px; margin:5px; width:150px;">
        <input type="text" name="name" placeholder="Full Name" required style="padding:5px; margin:5px; width:200px;">
        <select name="grade_level" style="padding:5px; margin:5px;">
            <option value="7">Grade 7</option>
            <option value="8">Grade 8</option>
            <option value="9">Grade 9</option>
            <option value="10">Grade 10</option>
        </select>
        <input type="text" name="section" placeholder="Section (e.g., 7-A)" style="padding:5px; margin:5px; width:100px;">
        <button type="submit" name="add" style="padding:5px 15px;">Add Student</button>
    </form>
</div>

<!-- CSV Import Form -->
<div class="csv-import">
    <h3>Import Students from CSV</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit" name="import_csv" class="btn-import">📂 Import CSV</button>
    </form>
    <small>
        <strong>CSV Format:</strong> student_id, name, grade_level, section<br>
        <strong>Example:</strong><br>
        <code>2024-001,Juan Dela Cruz,7,7-A</code>
    </small>
</div>

<!-- Student List Table -->
<div style="overflow-x: auto; margin-top: 20px; background: white; border-radius: 5px;">
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>ID</th><th>Student ID</th><th>Name</th><th>Grade</th><th>Section</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $s): ?>
            <tr>
                <td><?php echo $s['id']; ?>;</td>
                <td><?php echo htmlspecialchars($s['student_id']); ?>;</td>
                <td><?php echo htmlspecialchars($s['name']); ?>;</td>
                <td>Grade <?php echo $s['grade_level']; ?>;</td>
                <td><?php echo htmlspecialchars($s['section']); ?>;</td>
                <td>
                    <span class="btn-edit" onclick="openEditModal(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars($s['student_id']); ?>', '<?php echo htmlspecialchars($s['name']); ?>', <?php echo $s['grade_level']; ?>, '<?php echo htmlspecialchars($s['section']); ?>')">Edit</span>
                    <a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('Delete this student?')" style="color:#f44336; margin-left:10px;">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($students) == 0): ?>
            <tr><td colspan="6" style="text-align:center;">No students found.<?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Edit Student</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="student_id" id="edit_student_id" placeholder="Student ID" required>
            <input type="text" name="name" id="edit_name" placeholder="Full Name" required>
            <select name="grade_level" id="edit_grade_level">
                <option value="7">Grade 7</option>
                <option value="8">Grade 8</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
            </select>
            <input type="text" name="section" id="edit_section" placeholder="Section">
            <button type="submit" name="edit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, student_id, name, grade_level, section) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_student_id').value = student_id;
    document.getElementById('edit_name').value = name;
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