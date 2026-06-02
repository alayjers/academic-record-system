<?php
require_once 'includes/header.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$teacher_name = $_SESSION['name'] ?? 'Teacher';

if ($role == 'admin') {
    $stmt = $pdo->query("SELECT DISTINCT grade_level, section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY grade_level ASC, section ASC");
    $allowed_data = $stmt->fetchAll();
} elseif ($role == 'advisory_teacher') {
    $stmt = $pdo->prepare("SELECT DISTINCT s.grade_level, s.section FROM advisory_section a JOIN students s ON a.section = s.section WHERE a.teacher_id = ? ORDER BY s.grade_level ASC, s.section ASC");
    $stmt->execute([$user_id]);
    $allowed_data = $stmt->fetchAll();
} elseif ($role == 'subject_teacher') {
    $stmt = $pdo->prepare("SELECT DISTINCT s.grade_level, tss.section FROM teacher_subject_section tss JOIN students s ON tss.section = s.section WHERE tss.teacher_id = ? ORDER BY s.grade_level ASC, s.section ASC");
    $stmt->execute([$user_id]);
    $allowed_data = $stmt->fetchAll();
} else {
    $allowed_data = [];
}

$grade_sections_map = [];
foreach ($allowed_data as $row) {
    if (!empty($row['grade_level'])) {
        $grade_sections_map[$row['grade_level']][] = $row['section'];
    }
}

$allowed_grades = array_keys($grade_sections_map);

$selected_grade = isset($_GET['grade_level']) ? (int)$_GET['grade_level'] : 0;
$selected_section = isset($_GET['section']) ? $_GET['section'] : '';
$selected_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
$message = '';
$message_type = '';

if ($selected_grade === 0 && !empty($allowed_grades)) {
    $selected_grade = $allowed_grades[0];
}

$valid_sections_for_grade = $grade_sections_map[$selected_grade] ?? [];

if (empty($selected_section) && !empty($valid_sections_for_grade)) {
    $selected_section = $valid_sections_for_grade[0];
}

if (!empty($selected_section) && !in_array($selected_section, $valid_sections_for_grade) && $role != 'admin') {
    $selected_section = '';
    $message = "You do not have access to that section within this Grade Level.";
    $message_type = "error";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grades'])) {
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
                        $stmt->execute([$score, $student_id, $assignment_id, $selected_semester]);
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

if (!function_exists('transmutate')) {
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
}

if (!function_exists('getDescriptor')) {
    function getDescriptor($term_grade) {
        if ($term_grade >= 90) return 'Outstanding';
        if ($term_grade >= 85) return 'Very Satisfactory';
        if ($term_grade >= 80) return 'Satisfactory';
        if ($term_grade >= 75) return 'Fairly Satisfactory';
        return 'Did Not Meet Expectations';
    }
}

if (!function_exists('calculateCategory')) {
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
}

$students_male = [];
$students_female = [];

if ($selected_section && $selected_grade) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE grade_level = ? AND section = ? AND (gender = 'Male' OR gender IS NULL OR gender = '') ORDER BY last_name, first_name");
    $stmt->execute([$selected_grade, $selected_section]);
    $students_male = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM students WHERE grade_level = ? AND section = ? AND gender = 'Female' ORDER BY last_name, first_name");
    $stmt->execute([$selected_grade, $selected_section]);
    $students_female = $stmt->fetchAll();
}

$all_students = array_merge($students_male, $students_female);

$stmt = $pdo->prepare("SELECT * FROM assignments WHERE semester = ? ORDER BY 
    CASE category 
        WHEN 'written' THEN 1 
        WHEN 'performance' THEN 2 
        WHEN 'exam' THEN 3 
    END, id");
$stmt->execute([$selected_semester]);
$assignments = $stmt->fetchAll();

$scores = [];
if (!empty($all_students)) {
    $student_ids = array_column($all_students, 'id');
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $stmt = $pdo->prepare("SELECT student_id, assignment_id, score FROM scores WHERE student_id IN ($placeholders) AND semester = ?");
    $params = array_merge($student_ids, [$selected_semester]);
    $stmt->execute($params);
    while ($row = $stmt->fetch()) {
        $scores[$row['student_id']][$row['assignment_id']] = $row['score'];
    }
}

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

$student_data = [];
foreach ($all_students as $student) {
    $sid = $student['id'];
    
    $ww_scores = [];
    foreach ($written as $w) { $ww_scores[$w['id']] = $scores[$sid][$w['id']] ?? null; }
    
    $pt_scores = [];
    foreach ($performance as $p) { $pt_scores[$p['id']] = $scores[$sid][$p['id']] ?? null; }
    
    $st_scores = [];
    foreach ($exams as $e) { $st_scores[$e['id']] = $scores[$sid][$e['id']] ?? null; }

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

<style>
    .tabs-wrapper { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .tab-link { padding: 10px 18px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); text-decoration: none; color: var(--text-muted); border-radius: 8px; font-weight: 600; font-size: 13px; transition: all 0.2s; }
    .tab-link:hover, .tab-link.active { background: var(--mode-btn-bg); border-color: var(--mode-btn-border); color: var(--mode-btn-text); }
    
    .grade-badge-container { border-bottom: 1px solid var(--border-card); padding-bottom: 14px; margin-bottom: 16px; }
    .tab-link.grade-btn { background: rgba(0, 180, 216, 0.05); color: #00b4d8; border-color: rgba(0, 180, 216, 0.2); }
    .tab-link.grade-btn.active { background: #00b4d8; color: #ffffff; border-color: #00b4d8; }

    .toolbar-panel { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    
    .deped-table-container { width: 100%; overflow-x: auto; margin-top: 15px; border-radius: 12px; border: 1px solid var(--border-card); }
    .deped-grade-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 12px; }
    .deped-grade-table th, .deped-grade-table td { border: 1px solid var(--border-card); padding: 8px; color: var(--input-text); }
    .deped-grade-table th { font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .th-blue { background-color: #0284c7; color: #ffffff !important; }
    .th-magenta { background-color: #be185d; color: #ffffff !important; }
    .th-yellow { background-color: #b45309; color: #ffffff !important; }
    .th-green { background-color: #15803d; color: #ffffff !important; }
    
    .row-hps { background-color: rgba(0, 0, 0, 0.04); }
    [data-theme="dark"] .row-hps, .dark-mode .row-hps { background-color: rgba(255, 255, 255, 0.05); }
    
    .gender-header-row { background-color: rgba(0, 180, 216, 0.08); font-weight: 700; text-align: left; color: #00b4d8; font-size: 13px; }
    
    .cell-calc { font-weight: 600; background-color: rgba(0, 0, 0, 0.04); color: var(--text-title); }
    [data-theme="dark"] .cell-calc, .dark-mode .cell-calc { background-color: rgba(255, 255, 255, 0.04); }
    
    .cell-warning { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
    
    .text-highlight { color: #1d4ed8; font-weight: 700; }
    [data-theme="dark"] .text-highlight, .dark-mode .text-highlight { color: #38bdf8; }
    
    .deped-grade-table input[type="number"] { background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 6px; color: var(--input-text); padding: 4px; width: 48px; text-align: center; }
    .deped-grade-table input[type="number"]:focus { border-color: #38bdf8; outline: none; }
</style>

<h1>DepEd Class Record</h1>

<?php if ($message): ?>
    <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="tabs-wrapper grade-badge-container">
    <?php foreach ($allowed_grades as $grade): ?>
        <a href="?grade_level=<?php echo $grade; ?>&semester=<?php echo $selected_semester; ?>" 
           class="tab-link grade-btn <?php echo $selected_grade == $grade ? 'active' : ''; ?>">
            Grade <?php echo $grade; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="tabs-wrapper">
    <?php foreach ($valid_sections_for_grade as $sec): ?>
        <a href="?grade_level=<?php echo $selected_grade; ?>&section=<?php echo urlencode($sec); ?>&semester=<?php echo $selected_semester; ?>" 
           class="tab-link <?php echo $selected_section == $sec ? 'active' : ''; ?>">
            Section <?php echo htmlspecialchars($sec); ?>
        </a>
    <?php endforeach; ?>
    <?php if (empty($allowed_grades)): ?>
        <p style="color: #f87171; font-weight: 500;">No assigned grade configurations mapped to your profile.</p>
    <?php endif; ?>
</div>

<div class="card-panel toolbar-panel">
    <form method="GET" style="display: flex; align-items: center; gap: 10px;">
        <input type="hidden" name="grade_level" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="section" value="<?php echo htmlspecialchars($selected_section); ?>">
        <label style="font-weight: 600; color: var(--text-muted); font-size: 13px;">Active Term:</label>
        <select name="semester" onchange="this.form.submit()" style="padding: 8px 12px;">
            <option value="1" <?php echo $selected_semester == 1 ? 'selected' : ''; ?>>1st Quarter</option>
            <option value="2" <?php echo $selected_semester == 2 ? 'selected' : ''; ?>>2nd Quarter</option>
            <option value="3" <?php echo $selected_semester == 3 ? 'selected' : ''; ?>>3rd Quarter</option>
            <option value="4" <?php echo $selected_semester == 4 ? 'selected' : ''; ?>>4th Quarter</option>
        </select>
    </form>
    <?php if (!empty($all_students)): ?>
        <button type="submit" form="classRecordForm" name="save_grades" class="save-btn">💾 Save All Grades</button>
    <?php endif; ?>
</div>

<?php if (empty($all_students)): ?>
    <div class="card-panel" style="text-align: center; padding: 40px; color: var(--text-muted);">
        <h3>No student profiles tracked inside Grade <?php echo $selected_grade; ?> - Section: <?php echo htmlspecialchars($selected_section ?: 'None'); ?></h3>
    </div>
<?php else: ?>
    <div class="card-panel" style="padding: 20px;">
        <form method="POST" action="" id="classRecordForm">
            <div class="deped-table-container">
                <table class="deped-grade-table">
                    <thead>
                        <tr>
                            <th colspan="2" rowspan="2" class="th-blue">LEARNERS' NAMES</th>
                            <th colspan="<?php echo count($written) + 3; ?>" class="th-magenta">WRITTEN WORKS (20%)</th>
                            <th colspan="<?php echo count($performance) + 3; ?>" class="th-yellow">PERFORMANCE TASKS (50%)</th>
                            <th colspan="<?php echo count($exams) + 3; ?>" class="th-green">SUMMATIVE TESTS (30%)</th>
                            <th rowspan="2" class="th-blue">Initial</th>
                            <th rowspan="2" class="th-magenta">Term</th>
                            <th rowspan="2" class="th-blue">Descriptor</th>
                        </tr>
                        <tr>
                            <?php $i=1; foreach($written as $w): ?><th>W<?php echo $i++; ?></th><?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                            
                            <?php $i=1; foreach($performance as $p): ?><th>P<?php echo $i++; ?></th><?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                            
                            <?php $i=1; foreach($exams as $e): ?><th>Q<?php echo $i++; ?></th><?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $hps_ww_total = array_sum($written_max);
                            $hps_pt_total = array_sum($performance_max);
                            $hps_st_total = array_sum($exams_max);
                        ?>
                        <tr class="row-hps">
                            <td colspan="2" style="text-align: left; font-weight: bold; color: var(--text-title); padding-left: 12px;">Highest Possible Score</td>
                            
                            <?php foreach($written as $w): ?>
                                <td><input type="number" name="max_score[<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($w['max_score']); ?>" step="1"></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_ww_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">20%</td>
                            
                            <?php foreach($performance as $p): ?>
                                <td><input type="number" name="max_score[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['max_score']); ?>" step="1"></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_pt_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">50%</td>
                            
                            <?php foreach($exams as $e): ?>
                                <td><input type="number" name="max_score[<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($e['max_score']); ?>" step="1"></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_st_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">30%</td>
                            
                            <td class="cell-calc"></td><td class="cell-calc"></td><td class="cell-calc"></td>
                        </tr>

                        <tr class="gender-header-row">
                            <td colspan="<?php echo count($assignments) + 14; ?>" style="padding-left: 12px; text-align: left;">MALE</td>
                        </tr>

                        <?php if (empty($students_male)): ?>
                            <tr><td colspan="<?php echo count($assignments) + 14; ?>" style="color: var(--text-muted); padding: 10px;">No male student records registered in this section.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students_male as $student): 
                                $sid = $student['id'];
                                $data = $student_data[$sid];
                                $warning = ($data['initial_grade'] > 0 && $data['initial_grade'] < 75) ? 'cell-warning' : '';
                            ?>
                            <tr>
                                <td style="font-weight: 600; width: 40px; color: var(--text-muted);"><?php echo htmlspecialchars($student['student_id'] ?? $sid); ?></td>
                                <td style="text-align: left; min-width: 180px; font-weight: 600; padding-left: 12px; border-right: 2px solid var(--border-card);"><?php echo htmlspecialchars($student['name']); ?></td>
                                
                                <?php foreach($written as $w): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($data['ww_scores'][$w['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['ww_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($performance as $p): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($data['pt_scores'][$p['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['pt_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($exams as $e): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($data['st_scores'][$e['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['st_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['st_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['st_calc']['ws'], 2); ?></td>
                                
                                <td class="cell-calc <?php echo $warning; ?>"><?php echo ($data['initial_grade'] > 0) ? number_format($data['initial_grade'], 2) : '0.00'; ?></td>
                                <td class="text-highlight"><?php echo ($data['term_grade'] > 0) ? $data['term_grade'] : '60'; ?></td>
                                <td style="font-size: 11px; font-weight: 500; color: var(--text-muted); text-align: left; padding-left: 6px;"><?php echo $data['descriptor'] ?: 'Did Not Meet Expectations'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr class="gender-header-row">
                            <td colspan="<?php echo count($assignments) + 14; ?>" style="padding-left: 12px; text-align: left;">FEMALE</td>
                        </tr>

                        <?php if (empty($students_female)): ?>
                            <tr><td colspan="<?php echo count($assignments) + 14; ?>" style="color: var(--text-muted); padding: 10px;">No female student records registered in this section.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students_female as $student): 
                                $sid = $student['id'];
                                $data = $student_data[$sid];
                                $warning = ($data['initial_grade'] > 0 && $data['initial_grade'] < 75) ? 'cell-warning' : '';
                            ?>
                            <tr>
                                <td style="font-weight: 600; width: 40px; color: var(--text-muted);"><?php echo htmlspecialchars($student['student_id'] ?? $sid); ?></td>
                                <td style="text-align: left; min-width: 180px; font-weight: 600; padding-left: 12px; border-right: 2px solid var(--border-card);"><?php echo htmlspecialchars($student['name']); ?></td>
                                
                                <?php foreach($written as $w): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($data['ww_scores'][$w['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['ww_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($performance as $p): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($data['pt_scores'][$p['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['pt_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($exams as $e): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($data['st_scores'][$e['id']] ?? ''); ?>" step="0.5"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['st_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['st_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['st_calc']['ws'], 2); ?></td>
                                
                                <td class="cell-calc <?php echo $warning; ?>"><?php echo ($data['initial_grade'] > 0) ? number_format($data['initial_grade'], 2) : '0.00'; ?></td>
                                <td class="text-highlight"><?php echo ($data['term_grade'] > 0) ? $data['term_grade'] : '60'; ?></td>
                                <td style="font-size: 11px; font-weight: 500; color: var(--text-muted); text-align: left; padding-left: 6px;"><?php echo $data['descriptor'] ?: 'Did Not Meet Expectations'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="save_grades" value="1">
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>