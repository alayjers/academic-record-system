<?php
require_once 'includes/header.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$teacher_name = $_SESSION['name'] ?? 'Teacher';

// Ensure $school_year is safely initialized at the top level
if (!isset($school_year) || empty($school_year)) {
    $school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '2025-2026';
}

$allowed_data = [];
$teacher_subject_map = [];
$teacher_subject_names = [];
$allowed_grades = [];
$grade_sections_map = [];
$advisory_sections = []; 

$selected_grade   = isset($_GET['grade_level']) ? (int)$_GET['grade_level'] : 0;
$selected_section = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$selected_subject = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Grab semester and clamp it between 1 and 3 for the DepEd policy
$selected_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
if ($selected_semester < 1 || $selected_semester > 3) {
    $selected_semester = 1; 
}

// ==========================================
// 1. ROLE-BASED ACCESS CONTROL & LOADING
// ==========================================
if ($role == 'admin') {
    $sec_stmt = $pdo->query("SELECT id, grade_level, name AS section, school_year FROM sections ORDER BY school_year DESC, grade_level ASC, name ASC");
    $allowed_data = $sec_stmt->fetchAll();
    
    foreach ($allowed_data as $row) {
        if (!in_array($row['grade_level'], $allowed_grades)) {
            $allowed_grades[] = $row['grade_level'];
        }
        // Store full section entity structures safely
        $grade_sections_map[$row['grade_level']][] = [
            'id' => (int)$row['id'],
            'name' => $row['section']
        ];
    }
} else {
    // Pull sections and subjects assigned to this teacher
    $sub_stmt = $pdo->prepare("
        SELECT DISTINCT sec.grade_level, sec.name AS section, tss.section_id, tss.subject_id, sub.name AS subject_name, sec.school_year
        FROM teacher_subject_section tss
        JOIN sections sec ON tss.section_id = sec.id
        JOIN subjects sub ON tss.subject_id = sub.id
        WHERE tss.teacher_id = ?
    ");
    $sub_stmt->execute([$user_id]);
    $allowed_data = $sub_stmt->fetchAll();
    
    foreach ($allowed_data as $row) {
        $sec_id = (int)$row['section_id'];
        $sub_id = (int)$row['subject_id'];
        
        // Key maps off the numeric database tracking identifier directly
        $teacher_subject_map[$sec_id][] = $sub_id;
        $teacher_subject_names[$sub_id] = $row['subject_name'];

        if (!in_array($row['grade_level'], $allowed_grades)) {
            $allowed_grades[] = $row['grade_level'];
        }
        if (!isset($grade_sections_map[$row['grade_level']])) {
            $grade_sections_map[$row['grade_level']] = [];
        }
        
        // Track unique tracking layout structures safely
        $found = false;
        foreach ($grade_sections_map[$row['grade_level']] as $existing_sec) {
            if ($existing_sec['id'] === $sec_id) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $grade_sections_map[$row['grade_level']][] = [
                'id' => $sec_id,
                'name' => $row['section']
            ];
        }
    }
}

// Initialize messages
$message = '';
$message_type = '';

// Default selections if parameters are empty/missing
if ($selected_grade === 0 && !empty($allowed_grades)) {
    $selected_grade = $allowed_grades[0];
}

$valid_sections_for_grade = $grade_sections_map[$selected_grade] ?? [];
if (empty($selected_section) && !empty($valid_sections_for_grade)) {
    $selected_section = $valid_sections_for_grade[0]['id'];
}

// Safely retrieve the default subject for this specific section from our map
if ($role != 'admin' && $selected_subject === 0 && !empty($teacher_subject_map[$selected_section])) {
    $selected_subject = $teacher_subject_map[$selected_section][0]; 
}

// Security Checkpoint: Verify access permission
$has_access = false;
if ($role == 'admin') {
    $has_access = true;
} else {
    foreach ($valid_sections_for_grade as $vs) {
        if ($vs['id'] === $selected_section) {
            $has_access = true;
            break;
        }
    }
}

if (!$has_access && !empty($selected_section)) {
    $details = "Unauthorized access attempt by User ID $user_id to Section ID: $selected_section";
    $audit = $pdo->prepare("INSERT INTO audit_log (user_id, action, details) VALUES (?, 'UNAUTHORIZED_ACCESS', ?)");
    $audit->execute([$user_id, $details]);

    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h2 style='color:#e63946;'>Access Denied</h2>
            <p>You are not authorized to view or edit grades for this section.</p>
            <a href='dashboard.php' style='color:#40916c; font-weight:600;'>Return to Dashboard</a>
         </div>");
}

// ==========================================
// 2. FORM SUBMISSION HANDLER (SINGLE EXECUTION)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    
    // A. Update Max Scores if changed
    if (isset($_POST['max_score'])) {
        foreach ($_POST['max_score'] as $assignment_id => $max_score) {
            $stmt = $pdo->prepare("SELECT max_score FROM assignments WHERE id = ?");
            $stmt->execute([(int)$assignment_id]);
            $old = $stmt->fetch();
            if ($old && $old['max_score'] != $max_score) {
                $stmt = $pdo->prepare("UPDATE assignments SET max_score = ? WHERE id = ?");
                $stmt->execute([$max_score, (int)$assignment_id]);
            }
        }
    }

    // B. Save individual raw assignment scores using bulletproof Upsert rules
    if (isset($_POST['score'])) {
        foreach ($_POST['score'] as $student_id => $posted_assignments) {
            if (empty($student_id) || !is_numeric($student_id)) continue;
            
            foreach ($posted_assignments as $assignment_id => $score) {
                if ($score !== '' && $score !== null) {
                    $sql_scores_upsert = "
                        INSERT INTO `scores` (`student_id`, `assignment_id`, `score`, `semester`, `school_year`) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE `score` = VALUES(`score`)
                    ";
                    $score_stmt = $pdo->prepare($sql_scores_upsert);
                    $score_stmt->execute([
                        (int)$student_id, 
                        (int)$assignment_id, 
                        $score, 
                        $selected_semester, 
                        $school_year
                    ]);
                }
            }
        }

        // C. Calculate and Save Final Term Grades to normalized term_grades table
        if ($selected_subject && $role != 'admin') {
            $section_students = [];
            if ($selected_section) {
                $stmt = $pdo->prepare("
                    SELECT s.id FROM enrollments e
                    JOIN students s ON e.student_id = s.id
                    WHERE e.section_id = ?
                ");
                $stmt->execute([$selected_section]);
                $section_students = $stmt->fetchAll();
            }
            $student_ids = array_column($section_students, 'id');

            if (!empty($student_ids)) {
                $astmt = $pdo->prepare("SELECT * FROM assignments WHERE semester = ? AND subject_id = ?");
                $astmt->execute([$selected_semester, $selected_subject]);
                $current_assignments = $astmt->fetchAll();

                // Clean matrix re-hydration using existing logic functions
                $scores_matrix = [];
                $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
                $s_lookup = $pdo->prepare("SELECT student_id, assignment_id, score FROM `scores` WHERE student_id IN ($placeholders) AND semester = ?");
                $s_lookup->execute(array_merge($student_ids, [$selected_semester]));
                while ($row = $s_lookup->fetch()) {
                    $scores_matrix[$row['student_id']][$row['assignment_id']] = $row['score'];
                }

                foreach ($section_students as $st_row) {
                    $sid = $st_row['id'];
                    $ww_tot = 0; $ww_max = 0; $pt_tot = 0; $pt_max = 0; $st_tot = 0; $st_max = 0;

                    foreach ($current_assignments as $asg) {
                        $sc_val = $scores_matrix[$sid][$asg['id']] ?? null;
                        if ($asg['category'] == 'written') {
                            $ww_max += $asg['max_score'];
                            if ($sc_val !== null) $ww_tot += floatval($sc_val);
                        } elseif ($asg['category'] == 'performance') {
                            $pt_max += $asg['max_score'];
                            if ($sc_val !== null) $pt_tot += floatval($sc_val);
                        } elseif ($asg['category'] == 'exam') {
                            $st_max += $asg['max_score'];
                            if ($sc_val !== null) $st_tot += floatval($sc_val);
                        }
                    }

                    $ww_ws = ($ww_max > 0) ? (($ww_tot / $ww_max) * 100) * 0.20 : 0;
                    $pt_ws = ($pt_max > 0) ? (($pt_tot / $pt_max) * 100) * 0.50 : 0;
                    $st_ws = ($st_max > 0) ? (($st_tot / $st_max) * 100) * 0.30 : 0;

                    $initial_grade = $ww_ws + $pt_ws + $st_ws;
                    $final_grade = ($initial_grade > 0) ? transmutate($initial_grade) : 0;

                    $sql_upsert = "INSERT INTO term_grades (student_id, subject_id, term, school_year, final_grade) 
                                   VALUES (?, ?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE final_grade = VALUES(final_grade)";
                    
                    $rg_stmt = $pdo->prepare($sql_upsert);
                    $rg_stmt->execute([
                        (int)$sid, 
                        (int)$selected_subject, 
                        $selected_semester, 
                        $school_year, 
                        $final_grade
                    ]);
                }
            }
        }
    }
    
    $message = "All raw scores and final term averages synchronized successfully!";
    $message_type = "success";
}

// ==========================================
// 3. CORE HELPER REGISTRY FUNCTIONS
// ==========================================
function transmutate($initial_grade) {
    $score = round($initial_grade, 2);
    if ($score >= 100) return 100;
    
    $transmutation = [
        '98.40' => 99, '96.80' => 98, '95.20' => 97, '93.60' => 96, '92.00' => 95,
        '90.40' => 94, '88.80' => 93, '87.20' => 92, '85.60' => 91, '84.00' => 90,
        '82.40' => 89, '80.80' => 88, '79.20' => 87, '77.60' => 86, '76.00' => 85,
        '74.40' => 84, '72.80' => 83, '71.20' => 82, '69.60' => 81, '68.00' => 80,
        '66.40' => 79, '64.80' => 78, '63.20' => 77, '61.60' => 76, '60.00' => 75,
        '56.00' => 74, '52.00' => 73, '48.00' => 72, '44.00' => 71, '40.00' => 70,
        '36.00' => 69, '32.00' => 68, '28.00' => 67, '24.00' => 66, '20.00' => 65,
        '16.00' => 64, '12.00' => 63, '8.00' => 62, '4.00' => 61, '0.00' => 60
    ];
    
    foreach ($transmutation as $min_range => $transmuted_grade) {
        if ($score >= (float)$min_range) {
            return $transmuted_grade;
        }
    }
    return 60;
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
        
        $ps = 0; $ws = 0;
        if ($total_max > 0) {
            $ps = ($total_score / $total_max) * 100;
            $ws = $ps * $weight;
        }
        return ['total' => $total_score, 'ps' => $ps, 'ws' => $ws];
    }
}

// ==========================================
// 4. RENDERING DATASETS FETCH ROUTINE
// ==========================================
$students_male = [];
$students_female = [];
$all_students = [];
$assignments = [];

if ($selected_section > 0) {
    // Fetch Active Males (Matches 'MALE', 'M', or empty layouts securely)
    $stmt = $pdo->prepare("
        SELECT s.* FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.section_id = ? 
          AND (UPPER(s.gender) LIKE 'M%' OR s.gender IS NULL OR s.gender = '')
        ORDER BY s.last_name ASC, s.first_name ASC
    ");
    $stmt->execute([$selected_section]);
    $students_male = $stmt->fetchAll();

    // Fetch Active Females (Matches 'FEMALE' or 'F' layout rules)
    $stmt = $pdo->prepare("
        SELECT s.* FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.section_id = ? 
          AND UPPER(s.gender) LIKE 'F%'
        ORDER BY s.last_name ASC, s.first_name ASC
    ");
    $stmt->execute([$selected_section]);
    $students_female = $stmt->fetchAll();
    
    $all_students = array_merge($students_male, $students_female);
}

// Fetch assignments based on parameters (Direct lookup via subject context)
if ($selected_subject) {
    $stmt = $pdo->prepare("
        SELECT * FROM assignments 
        WHERE semester = ? AND subject_id = ?
        ORDER BY CASE category WHEN 'written' THEN 1 WHEN 'performance' THEN 2 WHEN 'exam' THEN 3 END, id
    ");
    $stmt->execute([$selected_semester, $selected_subject]);
    $assignments = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM assignments 
        WHERE semester = ?
        ORDER BY CASE category WHEN 'written' THEN 1 WHEN 'performance' THEN 2 WHEN 'exam' THEN 3 END, id
    ");
    $stmt->execute([$selected_semester]);
    $assignments = $stmt->fetchAll();
}

$scores = [];
if (!empty($all_students)) {
    $student_ids = array_column($all_students, 'id');
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT student_id, assignment_id, score 
        FROM `scores` 
        WHERE student_id IN ($placeholders) 
          AND semester = ?
    ");
    
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

    // Aligned to match your DepEd HTML table view metrics (Written: 20%, Performance: 50%, Exam: 30%)
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
    /* Force page to fill entire screen */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    .content-wrapper {
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 15px !important;
        border-radius: 0 !important;
    }

    .card-panel {
        width: 100% !important;
        box-sizing: border-box;
    }

    .tabs-wrapper { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .tab-link { padding: 10px 18px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); text-decoration: none; color: var(--text-muted); border-radius: 8px; font-weight: 600; font-size: 13px; transition: all 0.2s; }
    .tab-link:hover, .tab-link.active { background: var(--mode-btn-bg); border-color: var(--mode-btn-border); color: var(--mode-btn-text); }
    
    .grade-badge-container { border-bottom: 1px solid var(--border-card); padding-bottom: 14px; margin-bottom: 16px; }
    .tab-link.grade-btn { background: rgba(0, 180, 216, 0.05); color: #00d9ff; border-color: rgba(0, 180, 216, 0.2); }
    .tab-link.grade-btn.active { background: #00b4d8; color: #ffffff; border-color: #00b4d8; }

    .tab-link.advisory-badge {
        border-color: rgba(21, 128, 61, 0.3) !important;
        background: rgba(21, 128, 61, 0.06) !important;
        color: #22c55e !important;
    }
    .tab-link.advisory-badge.active {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }

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
    .gender-header-row { background-color: rgba(0, 180, 216, 0.08); font-weight: 700; text-align: left; color: #00b4d8; font-size: 13px; }
    .cell-calc { font-weight: 600; background-color: rgba(0, 0, 0, 0.04); color: var(--text-title); }
    .cell-warning { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
    .text-highlight { color: #1d4ed8; font-weight: 700; }
    
    .deped-grade-table input[type="number"] { background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 6px; color: var(--input-text); padding: 4px; width: 48px; text-align: center; }
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
    <?php foreach ($valid_sections_for_grade as $sec): 
        $is_advisory = in_array($sec['name'], $advisory_sections);
        $badge_class = $is_advisory ? 'advisory-badge' : '';
        $active_class = ($selected_section == $sec['id']) ? 'active' : '';
    ?>
        <a href="?grade_level=<?php echo $selected_grade; ?>&section_id=<?php echo $sec['id']; ?>&semester=<?php echo $selected_semester; ?>&subject_id=<?php echo $selected_subject; ?>" 
           class="tab-link <?php echo $badge_class; ?> <?php echo $active_class; ?>">
            Section <?php echo htmlspecialchars($sec['name']); ?> <?php echo $is_advisory ? '(Advisory)' : ''; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card-panel toolbar-panel">
    <form method="GET" style="display: flex; align-items: center; gap: 10px;">
        <?php if ($role != 'admin' && !empty($teacher_subject_map[$selected_section])): ?>
            <div>
                <label style="font-weight: 600; font-size: 13px;">Subject:</label>
                <select name="subject_id" onchange="this.form.submit()" style="padding: 8px 12px;">
                    <?php 
                    foreach ($teacher_subject_map[$selected_section] as $sub_id) {
                        $s_stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
                        $s_stmt->execute([$sub_id]);
                        $s_name = $s_stmt->fetchColumn();
                        $selected_attr = ($selected_subject == $sub_id) ? 'selected' : '';
                        echo "<option value='{$sub_id}' {$selected_attr}>" . htmlspecialchars($s_name) . "</option>";
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>
        <input type="hidden" name="grade_level" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="section_id" value="<?php echo (int)$selected_section; ?>">
        <label style="font-weight: 600; color: var(--text-muted); font-size: 13px;">Active Term:</label>
        <select name="semester" onchange="this.form.submit()" style="padding: 8px 12px;">
            <option value="1" <?php echo $selected_semester == 1 ? 'selected' : ''; ?>>1st Quarter</option>
            <option value="2" <?php echo $selected_semester == 2 ? 'selected' : ''; ?>>2nd Quarter</option>
            <option value="3" <?php echo $selected_semester == 3 ? 'selected' : ''; ?>>3rd Quarter</option>
        </select>
    </form>
    <?php if (!empty($all_students)): ?>
        <button type="submit" form="classRecordForm" name="save_grades" class="save-btn"> Save All Grades</button>
    <?php endif; ?>
</div>

<?php if (empty($all_students)): ?>
    <div class="card-panel" style="text-align: center; padding: 40px; color: var(--text-muted);">
        <h3>No student profiles tracked inside Grade <?php echo $selected_grade; ?> - Section ID: <?php echo htmlspecialchars($selected_section ?: 'None'); ?></h3>
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
                            <?php $i=1; foreach($written as $w): ?>
                            <th>W<?php echo $i++; ?></th>
                            <?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                            
                            <?php $i=1; foreach($performance as $p): ?>
                                <th>P<?php echo $i++; ?></th>
                            <?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                            
                            <?php $i=1; foreach($exams as $e): ?>
                                <th>Q<?php echo $i++; ?></th>
                            <?php endforeach; ?>
                            <th>Total</th><th>PS</th><th>WS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $hps_ww_total = array_sum($written_max);
                            $hps_pt_total = array_sum($performance_max);
                            $hps_st_total = array_sum($exams_max);
                            $total_active_columns = count($written) + count($performance) + count($exams) + 11;
                        ?>
                        <tr class="row-hps">
                            <td colspan="2" style="text-align: left; font-weight: bold; color: var(--text-title); padding-left: 12px;">Highest Possible Score</td>
                            
                            <?php foreach($written as $w): ?>
                                <td><input type="number" name="max_score[<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($w['max_score']); ?>" step="1" min="1" ></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_ww_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">20%</td>
                            
                            <?php foreach($performance as $p): ?>
                                <td><input type="number" name="max_score[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['max_score']); ?>" step="1" min="1" ></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_pt_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">50%</td>
                            
                            <?php foreach($exams as $e): ?>
                                <td><input type="number" name="max_score[<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($e['max_score']); ?>" step="1" min="1" ></td>
                            <?php endforeach; ?>
                            <td class="cell-calc"><?php echo $hps_st_total; ?></td><td class="cell-calc">100.00</td><td class="cell-calc">30%</td>
                            
                            <td class="cell-calc"></td><td class="cell-calc"></td><td class="cell-calc"></td>
                        </tr>

                        <tr class="gender-header-row">
                            <td colspan="<?php echo $total_active_columns; ?>" style="padding-left: 12px; text-align: left;">MALE</td>
                        </tr>

                        <?php if (empty($students_male)): ?>
                            <tr><td colspan="<?php echo $total_active_columns; ?>" style="color: var(--text-muted); padding: 10px;">No male student records registered in this section.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students_male as $student): 
                                $sid = $student['id'];
                                $data = $student_data[$sid];
                                $warning = ($data['initial_grade'] > 0 && $data['initial_grade'] < 75) ? 'cell-warning' : '';
                            ?>
                            <tr>
                                <td style="font-weight: 600; width: 40px; color: var(--text-muted);"><?php echo htmlspecialchars($student['lrn'] ?? $sid); ?></td>
                                <td style="text-align: left; min-width: 180px; font-weight: 600; padding-left: 12px; border-right: 2px solid var(--border-card);"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                
                                <?php foreach($written as $w): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($data['ww_scores'][$w['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $w['max_score']; ?>"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['ww_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($performance as $p): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($data['pt_scores'][$p['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $p['max_score']; ?>"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['pt_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($exams as $e): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($data['st_scores'][$e['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $e['max_score']; ?>"></td>
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
                            <td colspan="<?php echo $total_active_columns; ?>" style="padding-left: 12px; text-align: left;">FEMALE</td>
                        </tr>

                        <?php if (empty($students_female)): ?>
                            <tr><td colspan="<?php echo $total_active_columns; ?>" style="color: var(--text-muted); padding: 10px;">No female student records registered in this section.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students_female as $student): 
                                $sid = $student['id'];
                                $data = $student_data[$sid];
                                $warning = ($data['initial_grade'] > 0 && $data['initial_grade'] < 75) ? 'cell-warning' : '';
                            ?>
                            <tr>
                                <td style="font-weight: 600; width: 40px; color: var(--text-muted);"><?php echo htmlspecialchars($student['lrn'] ?? $sid); ?></td>
                                <td style="text-align: left; min-width: 180px; font-weight: 600; padding-left: 12px; border-right: 2px solid var(--border-card);"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                
                                <?php foreach($written as $w): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $w['id']; ?>]" value="<?php echo htmlspecialchars($data['ww_scores'][$w['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $w['max_score']; ?>"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['ww_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['ww_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($performance as $p): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($data['pt_scores'][$p['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $p['max_score']; ?>"></td>
                                <?php endforeach; ?>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['total'], 1); ?></td>
                                <td class="cell-calc"><?php echo number_format($data['pt_calc']['ps'], 2); ?></td>
                                <td class="cell-calc" style="border-right: 2px solid var(--border-card);"><?php echo number_format($data['pt_calc']['ws'], 2); ?></td>
                                
                                <?php foreach($exams as $e): ?>
                                    <td><input type="number" name="score[<?php echo $sid; ?>][<?php echo $e['id']; ?>]" value="<?php echo htmlspecialchars($data['st_scores'][$e['id']] ?? ''); ?>" step="0.5" min="0" max="<?php echo $e['max_score']; ?>"></td>
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