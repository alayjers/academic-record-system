<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// ============================================
// TEACHER SECTION FILTERING
// ============================================
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get allowed sections based on role
if ($role == 'admin') {
    $stmt = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section");
    $allowed_sections = $stmt->fetchAll();
} elseif ($role == 'advisory_teacher') {
    $stmt = $pdo->prepare("SELECT section FROM advisory_section WHERE teacher_id = ?");
    $stmt->execute([$user_id]);
    $allowed_sections = $stmt->fetchAll();
} elseif ($role == 'subject_teacher') {
    $stmt = $pdo->prepare("SELECT DISTINCT section FROM teacher_subject_section WHERE teacher_id = ?");
    $stmt->execute([$user_id]);
    $allowed_sections = $stmt->fetchAll();
} else {
    $allowed_sections = [];
}

// Convert to simple array of section names
$allowed_section_names = array_column($allowed_sections, 'section');

// Get current section from URL
$selected_section = isset($_GET['section']) ? $_GET['section'] : '';
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : 1;
$message = '';
$message_type = '';

// If no section selected and sections exist, pick first one
if (empty($selected_section) && !empty($allowed_section_names)) {
    $selected_section = $allowed_section_names[0];
}

// Verify teacher has access to selected section
if (!empty($selected_section) && !in_array($selected_section, $allowed_section_names) && $role != 'admin') {
    $selected_section = '';
    $message = "You do not have access to that section.";
    $message_type = "error";
}

// ============================================
// HANDLE SAVE (with audit log)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grades'])) {
    // Save max scores
    if (isset($_POST['max_score'])) {
        foreach ($_POST['max_score'] as $assignment_id => $max_score) {
            $stmt = $pdo->prepare("SELECT max_score FROM assignments WHERE id = ?");
            $stmt->execute([$assignment_id]);
            $old = $stmt->fetch();
            
            if ($old && $old['max_score'] != $max_score) {
                $stmt = $pdo->prepare("UPDATE assignments SET max_score = ? WHERE id = ?");
                $stmt->execute([$max_score, $assignment_id]);
                
                $details = "Assignment ID $assignment_id: max_score changed from {$old['max_score']} to $max_score";
                $audit = $pdo->prepare("INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)");
                $audit->execute([$user_id, 'UPDATE_MAX_SCORE', $details]);
            }
        }
    }
    
    // Save student scores
    if (isset($_POST['score'])) {
        foreach ($_POST['score'] as $student_id => $assignments) {
            if (empty($student_id) || !is_numeric($student_id)) continue;
            
            foreach ($assignments as $assignment_id => $score) {
                if ($score !== '' && $score !== null) {
                    $stmt = $pdo->prepare("SELECT score FROM scores WHERE student_id = ? AND assignment_id = ? AND semester = ?");
                    $stmt->execute([$student_id, $assignment_id, $selected_semester]);
                    $old = $stmt->fetch();
                    $old_score = $old ? $old['score'] : null;
                    
                    $stmt = $pdo->prepare("SELECT id FROM scores WHERE student_id = ? AND assignment_id = ? AND semester = ?");
                    $stmt->execute([$student_id, $assignment_id, $selected_semester]);
                    $exists = $stmt->fetch();
                    
                    if ($exists) {
                        $stmt = $pdo->prepare("UPDATE scores SET score = ? WHERE student_id = ? AND assignment_id = ? AND semester = ?");
                        $stmt->execute([$score, $student_id, $assignment_id, $selected_semester]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO scores (student_id, assignment_id, score, semester) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$student_id, $assignment_id, $score, $selected_semester]);
                    }
                    
                    if ($old_score != $score) {
                        $details = "Student $student_id, Assignment $assignment_id, Semester $selected_semester: score changed from " . ($old_score ?? 'NULL') . " to $score";
                        $audit = $pdo->prepare("INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)");
                        $audit->execute([$user_id, 'UPDATE_SCORE', $details]);
                    }
                }
            }
        }
    }
    
    $message = "All grades saved successfully!";
    $message_type = "success";
}

// ============================================
// GET DATA FOR DISPLAY
// ============================================
$students = [];
if ($selected_section) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE section = ? ORDER BY name");
    $stmt->execute([$selected_section]);
    $students = $stmt->fetchAll();
}

// Get assignments
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE semester = ? ORDER BY 
    CASE category 
        WHEN 'written' THEN 1 
        WHEN 'performance' THEN 2 
        WHEN 'exam' THEN 3 
    END, id");
$stmt->execute([$selected_semester]);
$assignments = $stmt->fetchAll();

// Get existing scores
$scores = [];
if (!empty($students)) {
    $student_ids = array_column($students, 'id');
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $stmt = $pdo->prepare("SELECT student_id, assignment_id, score FROM scores WHERE student_id IN ($placeholders) AND semester = ?");
    $params = array_merge($student_ids, [$selected_semester]);
    $stmt->execute($params);
    while ($row = $stmt->fetch()) {
        $scores[$row['student_id']][$row['assignment_id']] = $row['score'];
    }
}

// Separate assignments by category
$written = [];
$performance = [];
$exams = [];
foreach ($assignments as $a) {
    if ($a['category'] == 'written') $written[] = $a;
    elseif ($a['category'] == 'performance') $performance[] = $a;
    elseif ($a['category'] == 'exam') $exams[] = $a;
}

// Calculate grades for each student
$student_data = [];
foreach ($students as $student) {
    // Written Works
    $written_total = 0;
    $written_ps_sum = 0;
    $written_count = 0;
    $written_scores = [];
    foreach ($written as $w) {
        $score = isset($scores[$student['id']][$w['id']]) ? $scores[$student['id']][$w['id']] : null;
        $written_scores[$w['id']] = $score;
        if ($score !== null && $score !== '' && $w['max_score'] > 0) {
            $percentage = ($score / $w['max_score']) * 100;
            $written_total += $score;
            $written_ps_sum += $percentage;
            $written_count++;
        }
    }
    $written_ps = $written_count > 0 ? $written_ps_sum / $written_count : 0;
    $written_wa = $written_ps * 0.4;
    
    // Performance Tasks
    $performance_total = 0;
    $performance_ps_sum = 0;
    $performance_count = 0;
    $performance_scores = [];
    foreach ($performance as $p) {
        $score = isset($scores[$student['id']][$p['id']]) ? $scores[$student['id']][$p['id']] : null;
        $performance_scores[$p['id']] = $score;
        if ($score !== null && $score !== '' && $p['max_score'] > 0) {
            $percentage = ($score / $p['max_score']) * 100;
            $performance_total += $score;
            $performance_ps_sum += $percentage;
            $performance_count++;
        }
    }
    $performance_ps = $performance_count > 0 ? $performance_ps_sum / $performance_count : 0;
    $performance_wa = $performance_ps * 0.4;
    
    // Exams
    $exam_total = 0;
    $exam_ps_sum = 0;
    $exam_count = 0;
    $exam_scores = [];
    foreach ($exams as $e) {
        $score = isset($scores[$student['id']][$e['id']]) ? $scores[$student['id']][$e['id']] : null;
        $exam_scores[$e['id']] = $score;
        if ($score !== null && $score !== '' && $e['max_score'] > 0) {
            $percentage = ($score / $e['max_score']) * 100;
            $exam_total += $score;
            $exam_ps_sum += $percentage;
            $exam_count++;
        }
    }
    $exam_ps = $exam_count > 0 ? $exam_ps_sum / $exam_count : 0;
    $exam_wa = $exam_ps * 0.2;
    
    $initial_grade = $written_wa + $performance_wa + $exam_wa;
    
    $student_data[$student['id']] = [
        'written_scores' => $written_scores,
        'written_total' => $written_total,
        'written_ps' => $written_ps,
        'written_wa' => $written_wa,
        'performance_scores' => $performance_scores,
        'performance_total' => $performance_total,
        'performance_ps' => $performance_ps,
        'performance_wa' => $performance_wa,
        'exam_scores' => $exam_scores,
        'exam_total' => $exam_total,
        'exam_ps' => $exam_ps,
        'exam_wa' => $exam_wa,
        'initial_grade' => $initial_grade
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Entry</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .tabs { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 20px; background: white; padding: 10px; border-radius: 5px; }
        .tab { padding: 8px 16px; background: #e0e0e0; text-decoration: none; color: #333; border-radius: 5px; }
        .tab.active { background: #4CAF50; color: white; }
        .toolbar { background: white; padding: 10px; margin-bottom: 20px; border-radius: 5px; display: flex; justify-content: space-between; }
        .btn-save { background: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; }
        .table-wrapper { overflow-x: auto; }
        .grade-table { border-collapse: collapse; min-width: 2100px; background: white; }
        .grade-table th, .grade-table td { border: 1px solid #ccc; padding: 6px; text-align: center; font-size: 12px; }
        .student-name { position: sticky; left: 0; background: white; font-weight: bold; }
        .highest-score-row td { background: #e8f4f8; }
        .total-col { background: #fef9e6; }
        .ps-wa-col { background: #f0f8ff; }
        .final-grade-col { background: #d4edda; font-weight: bold; }
        .warning-row { background-color: #fff3e0; }
        .warning-cell { background-color: #ffcdd2; }
        .score-input, .max-input { width: 40px; padding: 4px; text-align: center; font-size: 11px; border: 1px solid #ccc; border-radius: 3px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Grade Entry</h1>
        
        <!-- Section Tabs -->
        <div class="tabs">
            <?php foreach ($allowed_section_names as $sec): ?>
                <a href="?section=<?php echo urlencode($sec); ?>&semester=<?php echo $selected_semester; ?>" 
                   class="tab <?php echo $selected_section == $sec ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($sec); ?>
                </a>
            <?php endforeach; ?>
            <?php if (empty($allowed_section_names)): ?>
                <span style="color:red;">No sections assigned to you. Contact admin.</span>
            <?php endif; ?>
        </div>
        
        <!-- Toolbar -->
        <div class="toolbar">
            <form method="GET" style="display: inline;">
                <input type="hidden" name="section" value="<?php echo htmlspecialchars($selected_section); ?>">
                <label>Semester:</label>
                <select name="semester" onchange="this.form.submit()">
                    <option value="1" <?php echo $selected_semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                    <option value="2" <?php echo $selected_semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                    <option value="3" <?php echo $selected_semester == 3 ? 'selected' : ''; ?>>3rd Semester</option>
                </select>
            </form>
            <button type="submit" form="gradeForm" class="btn-save">💾 Save All Grades</button>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($students)): ?>
            <p style="background:white; padding:20px;">No students found in section <?php echo htmlspecialchars($selected_section); ?>.</p>
        <?php else: ?>
        <div class="table-wrapper">
            <form method="POST" id="gradeForm">
                <table class="grade-table">
                    <!-- ROW 1: Categories -->
                    <tr>
                        <th rowspan="3" style="min-width:150px;">LEARNER'S NAME</th>
                        <th colspan="<?php echo count($written) + 3; ?>">Written Works (40%)</th>
                        <th colspan="<?php echo count($performance) + 3; ?>">Performance Tasks (40%)</th>
                        <th colspan="<?php echo count($exams) + 3; ?>">Quarterly Assessments (20%)</th>
                        <th rowspan="3">Initial Grade</th>
                        <th rowspan="3">Quarterly Grade</th>
                    </tr>
                    <!-- ROW 2: Numbers -->
                    <tr>
                        <?php for ($i = 1; $i <= count($written); $i++): ?><th><?php echo $i; ?></th><?php endfor; ?>
                        <th>Total</th><th>PS</th><th>WA</th>
                        <?php for ($i = 1; $i <= count($performance); $i++): ?><th><?php echo $i; ?></th><?php endfor; ?>
                        <th>Total</th><th>PS</th><th>WA</th>
                        <?php for ($i = 1; $i <= count($exams); $i++): ?><th><?php echo $i; ?></th><?php endfor; ?>
                        <th>Total</th><th>PS</th><th>WA</th>
                    </tr>
                    <!-- ROW 3: Highest Possible Score -->
                    <tr class="highest-score-row">
                        <?php foreach ($written as $w): ?>
                            <td><input type="number" class="max-input" name="max_score[<?php echo $w['id']; ?>]" value="<?php echo $w['max_score']; ?>" min="1" step="1"></td>
                        <?php endforeach; ?>
                        <td></td><td></td><td></td>
                        <?php foreach ($performance as $p): ?>
                            <td><input type="number" class="max-input" name="max_score[<?php echo $p['id']; ?>]" value="<?php echo $p['max_score']; ?>" min="1" step="1"></td>
                        <?php endforeach; ?>
                        <td></td><td></td><td></td>
                        <?php foreach ($exams as $e): ?>
                            <td><input type="number" class="max-input" name="max_score[<?php echo $e['id']; ?>]" value="<?php echo $e['max_score']; ?>" min="1" step="1"></td>
                        <?php endforeach; ?>
                        <td></td><td></td><td></td>
                    </tr>
                    <!-- ROW 4+: Student Data -->
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php $data = $student_data[$student['id']]; ?>
                            <?php $warning = $data['initial_grade'] < 75; ?>
                            <tr class="<?php echo $warning ? 'warning-row' : ''; ?>">
                                <td class="student-name"><?php echo htmlspecialchars($student['name']); ?></td>
                                <?php foreach ($written as $w): ?>
                                    <td><input type="number" class="score-input" name="score[<?php echo $student['id']; ?>][<?php echo $w['id']; ?>]" value="<?php echo isset($data['written_scores'][$w['id']]) ? $data['written_scores'][$w['id']] : ''; ?>" min="0" max="<?php echo $w['max_score']; ?>" step="1"></td>
                                <?php endforeach; ?>
                                <td class="total-col"><?php echo number_format($data['written_total'], 1); ?></td>
                                <td class="ps-wa-col"><?php echo number_format($data['written_ps'], 1); ?>%</td>
                                <td class="ps-wa-col"><?php echo number_format($data['written_wa'], 1); ?>%</td>
                                <?php foreach ($performance as $p): ?>
                                    <td><input type="number" class="score-input" name="score[<?php echo $student['id']; ?>][<?php echo $p['id']; ?>]" value="<?php echo isset($data['performance_scores'][$p['id']]) ? $data['performance_scores'][$p['id']] : ''; ?>" min="0" max="<?php echo $p['max_score']; ?>" step="1"></td>
                                <?php endforeach; ?>
                                <td class="total-col"><?php echo number_format($data['performance_total'], 1); ?></td>
                                <td class="ps-wa-col"><?php echo number_format($data['performance_ps'], 1); ?>%</td>
                                <td class="ps-wa-col"><?php echo number_format($data['performance_wa'], 1); ?>%</td>
                                <?php foreach ($exams as $e): ?>
                                    <td><input type="number" class="score-input" name="score[<?php echo $student['id']; ?>][<?php echo $e['id']; ?>]" value="<?php echo isset($data['exam_scores'][$e['id']]) ? $data['exam_scores'][$e['id']] : ''; ?>" min="0" max="<?php echo $e['max_score']; ?>" step="1"></td>
                                <?php endforeach; ?>
                                <td class="total-col"><?php echo number_format($data['exam_total'], 1); ?></td>
                                <td class="ps-wa-col"><?php echo number_format($data['exam_ps'], 1); ?>%</td>
                                <td class="ps-wa-col"><?php echo number_format($data['exam_wa'], 1); ?>%</td>
                                <td class="final-grade-col <?php echo $warning ? 'warning-cell' : ''; ?>"><strong><?php echo number_format($data['initial_grade'], 1); ?>%</strong></td>
                                <td><input type="number" class="score-input" name="quarterly_grade[<?php echo $student['id']; ?>]" value="<?php echo number_format($data['initial_grade'], 1); ?>" min="0" max="100" step="0.1" style="width:55px;"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="save_grades" value="1">
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>