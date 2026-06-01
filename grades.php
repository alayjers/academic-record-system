<?php
require_once 'includes/header.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// ============================================
// TEACHER SECTION FILTERING
// ============================================
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$teacher_name = $_SESSION['name'] ?? 'Teacher'; // Assuming name is stored in session

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
// DEPED CALCULATION FUNCTIONS
// ============================================
function transmutate($initial_grade) {
    $score = round($initial_grade, 2);
    if ($score >= 100) return 100;
    
    $transmutation = [
        98.40 => 99, 96.80 => 98, 95.20 => 97, 93.60 => 96, 92.00 => 95,
        90.40 => 94, 88.80 => 93, 87.20 => 92, 85.60 => 91, 84.00 => 90,
        82.40 => 89, 80.80 => 88, 79.20 => 87, 77.60 => 86, 76.00 => 85,
        74.40 => 84, 72.80 => 83, 71.20 => 82, 69.60 => 81, 68.00 => 80,
        66.40 => 79, 64.80 => 78, 63.20 => 77, 61.60 => 76, 60.00 => 75,
        56.00 => 74, 52.00 => 73, 48.00 => 72, 44.00 => 71, 40.00 => 70,
        36.00 => 69, 32.00 => 68, 28.00 => 67, 24.00 => 66, 20.00 => 65,
        16.00 => 64, 12.00 => 63, 8.00 => 62, 4.00 => 61, 0.00 => 60
    ];
    
    foreach ($transmutation as $min_range => $transmuted_grade) {
        if ($score >= $min_range) return $transmuted_grade;
    }
    return 60;
}

function getDescriptor($term_grade) {
    if ($term_grade >= 90) return 'Outstanding';
    if ($term_grade >= 85) return 'Very Satisfactory';
    if ($term_grade >= 80) return 'Satisfactory';
    if ($term_grade >= 75) return 'Fairly Satisfactory';
    return 'Did Not Meet Expectations';
}

function calculateCategory($scores_array, $max_scores_array, $weight) {
    $total_score = 0;
    $total_max = 0;
    
    foreach ($scores_array as $score) {
        if ($score !== '' && $score !== null) {
            $total_score += floatval($score);
        }
    }
    
    foreach ($max_scores_array as $max) {
        $total_max += floatval($max);
    }
    
    $ps = 0;
    $ws = 0;
    if ($total_max > 0) {
        $ps = ($total_score / $total_max) * 100;
        $ws = $ps * $weight;
    }
    
    return [
        'total' => $total_score,
        'ps' => $ps,
        'ws' => $ws
    ];
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

// Separate assignments by category and prepare max scores array
$written = []; $written_max = [];
$performance = []; $performance_max = [];
$exams = []; $exams_max = [];

foreach ($assignments as $a) {
    if ($a['category'] == 'written') {
        $written[] = $a;
        $written_max[$a['id']] = $a['max_score'];
    }
    elseif ($a['category'] == 'performance') {
        $performance[] = $a;
        $performance_max[$a['id']] = $a['max_score'];
    }
    elseif ($a['category'] == 'exam') {
        $exams[] = $a;
        $exams_max[$a['id']] = $a['max_score'];
    }
}

// Calculate grades for each student
$student_data = [];
foreach ($students as $student) {
    $sid = $student['id'];
    
    // Extract student's scores mapped to assignment IDs
    $ww_scores = [];
    foreach ($written as $w) { $ww_scores[$w['id']] = $scores[$sid][$w['id']] ?? null; }
    
    $pt_scores = [];
    foreach ($performance as $p) { $pt_scores[$p['id']] = $scores[$sid][$p['id']] ?? null; }
    
    $st_scores = [];
    foreach ($exams as $e) { $st_scores[$e['id']] = $scores[$sid][$e['id']] ?? null; }

    // Calculate using DepEd logic (20%, 50%, 30%)
    $ww_calc = calculateCategory($ww_scores, $written_max, 0.20);
    $pt_calc = calculateCategory($pt_scores, $performance_max, 0.50);
    $st_calc = calculateCategory($st_scores, $exams_max, 0.30);
    
    $initial_grade = $ww_calc['ws'] + $pt_calc['ws'] + $st_calc['ws'];
    $term_grade = ($initial_grade > 0) ? transmutate($initial_grade) : 0;

    $student_data[$sid] = [
        'ww_scores' => $ww_scores, 'ww_calc' => $ww_calc,
        'pt_scores' => $pt_scores, 'pt_calc' => $pt_calc,
        'st_scores' => $st_scores, 'st_calc' => $st_calc,
        'initial_grade' => $initial_grade,
        'term_grade' => $term_grade,
        'descriptor' => ($term_grade > 0) ? getDescriptor($term_grade) : ''
    ];

    
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DepEd Class Record</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; background: #e0e0e0; }
        
        /* UI Tools Styling */
        .tabs { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 20px; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .tab { padding: 8px 16px; background: #f0f0f0; text-decoration: none; color: #333; border-radius: 5px; font-weight: bold; }
        .tab.active { background: #4CAF50; color: white; }
        .toolbar { background: white; padding: 15px; margin-bottom: 20px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* DepEd Record Layout */
        .record-container { width: 100%; overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-title { text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .header-grid { display: grid; grid-template-columns: auto 1fr auto 1fr; gap: 5px 10px; align-items: center; margin-bottom: 10px; font-weight: bold; }
        .header-grid .line { border: 1px solid black; height: 20px; background-color: white; padding-left: 5px; display: flex; align-items: center; font-weight: normal; }
        .logos-container { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .logo-placeholder { width: 80px; height: 80px; border: 2px solid #4CAF50; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 10px; color: #333; }
        .info-bar { display: grid; grid-template-columns: 100px 1fr 1fr 1fr; border: 2px solid black; border-bottom: none; font-weight: bold; background: white; }
        .info-bar div { border-right: 1px solid black; padding: 5px; display: flex; align-items: center; }
        .info-bar div:last-child { border-right: none; }
        
        table { width: 100%; border-collapse: collapse; text-align: center; border: 2px solid black; }
        th, td { border: 1px solid black; padding: 4px; white-space: nowrap; }
        th { font-weight: bold; padding: 6px; }
        .bg-blue { background-color: #00B0F0; color: white; }
        .bg-magenta { background-color: #FF00FF; color: white; }
        .bg-yellow { background-color: #FFFF00; }
        .bg-green { background-color: #00FF00; }
        .bg-white { background-color: #FFFFFF; }
        .text-red { color: red; font-weight: bold; }
        .text-left { text-align: left; padding-left: 5px; }
        
        input[type="number"] { width: 45px; text-align: center; border: 1px solid #ccc; padding: 4px; font-size: 11px; border-radius: 3px; }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        
        .save-btn { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; font-weight: bold; }
        .save-btn:hover { background-color: #45a049; }
        
        .warning-cell { background-color: #ffcdd2 !important; }
        .calculated-cell { font-weight: bold; background-color: #f5f5f5; }
        
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .record-container { box-shadow: none; padding: 0; }
            .tabs, .toolbar, .no-print, header, nav, footer { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="tabs no-print">
        <?php foreach ($allowed_section_names as $sec): ?>
            <a href="?section=<?php echo urlencode($sec); ?>&semester=<?php echo $selected_semester; ?>" 
               class="tab <?php echo $selected_section == $sec ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($sec); ?>
            </a>
        <?php endforeach; ?>
        <?php if (empty($allowed_section_names)): ?>
            <span style="color:red; padding: 8px;">No sections assigned to you. Contact admin.</span>
        <?php endif; ?>
    </div>
    
    <div class="toolbar no-print">
        <form method="GET" style="display: inline; font-size: 14px;">
            <input type="hidden" name="section" value="<?php echo htmlspecialchars($selected_section); ?>">
            <label style="font-weight: bold;">Select Term:</label>
            <select name="semester" onchange="this.form.submit()" style="padding: 5px; border-radius: 3px; margin-left: 10px;">
                <option value="1" <?php echo $selected_semester == 1 ? 'selected' : ''; ?>>1st Quarter</option>
                <option value="2" <?php echo $selected_semester == 2 ? 'selected' : ''; ?>>2nd Quarter</option>
                <option value="3" <?php echo $selected_semester == 3 ? 'selected' : ''; ?>>3rd Quarter</option>
                <option value="4" <?php echo $selected_semester == 4 ? 'selected' : ''; ?>>4th Quarter</option>
            </select>
        </form>
        <button type="submit" form="classRecordForm" class="save-btn">💾 Save All Grades</button>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?> no-print"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <div class="record-container" style="text-align: center; padding: 50px;">
            <h3>No students found in section <?php echo htmlspecialchars($selected_section); ?>.</h3>
        </div>
    <?php else: ?>

    <div class="record-container">

        <form method="POST" action="" id="classRecordForm">
            <table>
                <thead>
                    <tr>
                        <th colspan="2" rowspan="2" class="bg-blue">LEARNERS' NAMES</th>
                        <th colspan="<?php echo count($written) + 3; ?>" class="bg-magenta">WRITTEN WORKS (20%)</th>
                        <th colspan="<?php echo count($performance) + 3; ?>" class="bg-yellow">PERFORMANCE TASKS (50%)</th>
                        <th colspan="<?php echo count($exams) + 3; ?>" class="bg-green">SUMMATIVE TESTS (30%)</th>
                        <th rowspan="2" class="bg-blue">Initial<br>Grade</th>
                        <th rowspan="2" class="bg-magenta">Term<br>Grade</th>
                        <th rowspan="2" class="bg-blue">Descriptor</th>
                    </tr>
                    <tr>
                        <?php $i=1; foreach($written as $w): ?><th><?php echo $i++; ?></th><?php endforeach; ?>
                        <th>Total</th><th>PS</th><th>WS</th>
                        
                        <?php $i=1; foreach($performance as $p): ?><th><?php echo $i++; ?></th><?php endforeach; ?>
                        <th>Total</th><th>PS</th><th>WS</th>
                        
                        <?php $i=1; foreach($exams as $e): ?><th><?php echo $i++; ?></th><?php endforeach; ?>
                        <th>Total</th><th>PS</th><th>WS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $hps_ww_total = array_sum($written_max);
                        $hps_pt_total = array_sum($performance_max);
                        $hps_st_total = array_sum($exams_max);
                    ?>
                    
                    <tr style="background-color: #fef9e6;">
                        <td colspan="2" class="text-left" style="font-size: 10px; font-weight: bold;">Highest Possible Score</td>
                        
                        <?php foreach($written as $w): ?>
                            <td><input type="number" name="max_score[<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($w['max_score']); ?>" step="1"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo $hps_ww_total; ?></td><td class="calculated-cell">100.00</td><td class="calculated-cell">20%</td>
                        
                        <?php foreach($performance as $p): ?>
                            <td><input type="number" name="max_score[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['max_score']); ?>" step="1"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo $hps_pt_total; ?></td><td class="calculated-cell">100.00</td><td class="calculated-cell">50%</td>
                        
                        <?php foreach($exams as $e): ?>
                            <td><input type="number" name="max_score[<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($e['max_score']); ?>" step="1"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo $hps_st_total; ?></td><td class="calculated-cell">100.00</td><td class="calculated-cell">30%</td>
                        
                        <td class="calculated-cell"></td><td class="calculated-cell"></td><td class="calculated-cell"></td>
                    </tr>


                    <?php foreach ($students as $student): 
                        $sid = $student['id'];
                        $data = $student_data[$sid];
                        $warning = ($data['initial_grade'] > 0 && $data['initial_grade'] < 75) ? 'warning-cell' : '';
                    ?>
                    <tr class="bg-white">
                        <td style="font-weight: bold; width: 30px;"><?php echo htmlspecialchars($student['student_id'] ?? $sid); ?></td>
                        <td class="text-left" style="width: 200px; font-weight: bold;"><?php echo htmlspecialchars($student['name']); ?></td>
                        
                        <?php foreach($written as $w): ?>
                            <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($data['ww_scores'][$w['id']]); ?>" step="0.5"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo number_format($data['ww_calc']['total'], 1); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['ww_calc']['ps'], 2); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['ww_calc']['ws'], 2); ?></td>
                        
                        <?php foreach($performance as $p): ?>
                            <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($data['pt_scores'][$p['id']]); ?>" step="0.5"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo number_format($data['pt_calc']['total'], 1); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['pt_calc']['ps'], 2); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['pt_calc']['ws'], 2); ?></td>
                        
                        <?php foreach($exams as $e): ?>
                            <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($data['st_scores'][$e['id']]); ?>" step="0.5"></td>
                        <?php endforeach; ?>
                        <td class="calculated-cell"><?php echo number_format($data['st_calc']['total'], 1); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['st_calc']['ps'], 2); ?></td>
                        <td class="calculated-cell"><?php echo number_format($data['st_calc']['ws'], 2); ?></td>
                        
                        <td class="calculated-cell <?php echo $warning; ?>"><?php echo ($data['initial_grade'] > 0) ? number_format($data['initial_grade'], 2) : ''; ?></td>
                        <td class="text-red"><?php echo ($data['term_grade'] > 0) ? $data['term_grade'] : ''; ?></td>
                        <td class="text-red" style="font-size: 11px;"><?php echo $data['descriptor']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                </tbody>
            </table>
            
        </form>
    </div>
    <?php endif; ?>

</body>
</html>