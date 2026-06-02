<?php
require_once 'includes/header.php';
require_once 'config/config.php';

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

function calculateCategory($scores_array, $max_scores_array, $weight) {
    $total_score = 0;
    $total_max = 0;
    foreach ($scores_array as $score) {
        if ($score !== '' && $score !== null) $total_score += floatval($score);
    }
    foreach ($max_scores_array as $max) {
        $total_max += floatval($max);
    }
    $ws = 0;
    if ($total_max > 0) {
        $ps = ($total_score / $total_max) * 100;
        $ws = $ps * $weight;
    }
    return $ws;
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1; 
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '2025-2026';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];

if (!empty($search)) {
    $like = "%$search%";
    $stmt = $pdo->prepare("
        SELECT id, name, first_name, last_name, lrn, student_id, grade_level, section 
        FROM students 
        WHERE name LIKE ? 
           OR first_name LIKE ? 
           OR last_name LIKE ? 
           OR lrn LIKE ? 
           OR student_id LIKE ?
        ORDER BY last_name ASC, first_name ASC
        LIMIT 20
    ");
    
    $stmt->execute([$like, $like, $like, $like, $like]);
    $search_results = $stmt->fetchAll();
}

$student = null;
$subject_grades = [];
$general_average = 0;
$general_remarks = '';

if ($student_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if ($student) {
        $subjects = $pdo->query("SELECT * FROM subjects ORDER BY sort_order")->fetchAll();
        
        foreach ($subjects as $subject) {
            $stmt = $pdo->prepare("
                SELECT a.*, s.score 
                FROM assignments a
                LEFT JOIN scores s ON a.id = s.assignment_id AND s.student_id = ? AND s.semester = ?
                WHERE a.subject_id = ? AND a.semester = ?
            ");
            $stmt->execute([$student_id, $semester, $subject['id'], $semester]);
            $assignments = $stmt->fetchAll();
            
            $cat_scores = ['written' => [], 'performance' => [], 'exam' => []];
            $cat_max = ['written' => [], 'performance' => [], 'exam' => []];

            foreach ($assignments as $a) {
                $cat = $a['category'];
                $cat_scores[$cat][] = $a['score'];
                $cat_max[$cat][] = $a['max_score'];
            }

            $ww_ws = calculateCategory($cat_scores['written'], $cat_max['written'], 0.20);
            $pt_ws = calculateCategory($cat_scores['performance'], $cat_max['performance'], 0.50);
            $st_ws = calculateCategory($cat_scores['exam'], $cat_max['exam'], 0.30);
            
            $initial_grade = $ww_ws + $pt_ws + $st_ws;
            $final_grade = ($initial_grade > 0) ? transmutate($initial_grade) : 0;

            $subject_grades[] = [
                'name' => $subject['name'],
                'grade' => $final_grade,
                'passed' => $final_grade >= 75
            ];
        }
        
        $total_grades = 0;
        $grade_count = 0;
        foreach ($subject_grades as $sg) {
            if ($sg['grade'] > 0) {
                $total_grades += $sg['grade'];
                $grade_count++;
            }
        }
        $general_average = $grade_count > 0 ? round($total_grades / $grade_count, 1) : 0;
        $general_remarks = $general_average >= 75 ? 'Passed' : 'Failed';
    }
}
?>

<style>
    .search-container { background: rgba(255, 255, 255, 0.02); padding: 22px; border-radius: 12px; margin-bottom: 24px; border: 1px solid var(--border-card); }
    .search-box { display: flex; gap: 12px; align-items: center; }
    .search-box input { 
        flex: 1; 
        padding: 12px 16px; 
        font-size: 14px; 
        border: 1px solid var(--border-card); 
        border-radius: 8px; 
        background: var(--input-bg, #ffffff); 
        color: var(--input-text, #1e293b); 
        outline: none; 
    }
    .search-box input::placeholder {
        color: var(--input-placeholder, #94a3b8);
        opacity: 1;
    }
    .search-box input:focus { border-color: var(--text-subtitle); }
    .search-box button { padding: 12px 24px; background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
    
    .search-results { background: rgba(255, 255, 255, 0.02); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 1px solid var(--border-card); }
    .search-results h3 { font-size: 15px; color: var(--text-subtitle); margin-bottom: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .result-item { padding: 12px 16px; border: 1px solid var(--border-card); border-radius: 8px; margin-bottom: 8px; background: rgba(255, 255, 255, 0.01); transition: all 0.2s ease; }
    .result-item a { text-decoration: none; color: var(--text-title); display: block; font-weight: 500; font-size: 14px; }
    .result-item:hover { background: rgba(0, 180, 216, 0.08); border-color: var(--text-subtitle); }
    
    .report-card { background: transparent; padding: 0; border-radius: 0; }
    .toolbar { margin-bottom: 20px; background: rgba(255, 255, 255, 0.02); padding: 16px 20px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; border-radius: 12px; border: 1px solid var(--border-card); }
    .toolbar label { font-size: 13px; color: var(--text-muted); font-weight: 500; }
    .toolbar select, .toolbar input[type="text"] { padding: 8px 12px; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-card); border-radius: 6px; color: white; font-size: 13px; outline: none; }
    .toolbar select:focus, .toolbar input[type="text"]:focus { border-color: var(--text-subtitle); }
    .btn-print { background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%); color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; }
    .btn-secondary { background: rgba(255, 255, 255, 0.08); border: 1px solid var(--border-card); }
    .btn-secondary:hover { background: rgba(255, 255, 255, 0.15); }
    
    .print-container { background: #ffffff; color: #000000; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-width: 1140px; margin: 0 auto; }
    .deped-header { text-align: center; margin-bottom: 15px; position: relative; border-bottom: 2px solid #000000; padding-bottom: 10px; }
    .deped-header img { height: 55px; position: absolute; left: 15px; top: 0; }
    .deped-header h2 { font-size: 13px; font-weight: 800; margin: 0; letter-spacing: 0.5px; line-height: 1.3; color: #000000; }
    .deped-header p { font-size: 11px; margin: 2px 0 0 0; line-height: 1.3; color: #333333; }
    .deped-header .school-title { font-size: 14px; font-weight: 800; color: #000000; margin-top: 2px; }
    
    .report-card-body { display: flex; gap: 30px; align-items: flex-start; margin-top: 15px; }
    .report-column { flex: 1; min-width: 0; }
    
    .header-text { font-weight: 800; text-transform: uppercase; text-align: center; margin-bottom: 10px; font-size: 12px; color: #000000; letter-spacing: 0.5px; border-bottom: 1px solid #000000; padding-bottom: 4px; }
    
    .print-container table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px; background: transparent; }
    .print-container th, .print-container td { border: 1px solid #000000; padding: 5px 4px; text-align: center; color: #000000; line-height: 1.2; }
    .print-container th { background-color: #f5f5f5; font-weight: 700; text-transform: uppercase; font-size: 10px; }
    .print-container .text-left { text-align: left; padding-left: 8px; }
    .print-container .text-center { text-align: center; }
    
    .indented-subject { padding-left: 20px !important; font-style: italic; }
    .remarks-badge-print { font-weight: 700; font-size: 10px; }
    .remarks-badge-print.passed { color: #000000; }
    .remarks-badge-print.failed { color: #000000; text-decoration: underline; }

    .legend-container { display: flex; justify-content: space-between; font-size: 10px; margin-top: 8px; border-top: 1px dashed #000000; padding-top: 8px; }
    .legend-table { width: auto; margin: 0; border: none; }
    .legend-table th, .legend-table td { border: none !important; padding: 2px 6px; text-align: left; color: #000000 !important; background: transparent !important; }
    .legend-table th { font-weight: 700; padding-bottom: 4px; text-transform: uppercase; font-size: 9px; }
    
    @media print {
    .header,
    .ambient-glow-1,
    .ambient-glow-2,
    .no-print,
    .search-container {
        display: none !important;
    }
    
    body {
        background: #ffffff;
        color: #000000;
        padding: 0;
    }
    
    .container {
        margin: 0;
        padding: 0;
        max-width: 100%;
    }
    
    .card-panel, 
    .report-card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>

<h1 class="no-print">Performance Evaluation & Learner Portfolios</h1>

<div class="search-container no-print">
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search by Last Name, or Student ID..." value="<?php echo htmlspecialchars($search); ?>" autofocus>
        <button type="submit">Search Profile</button>
    </form>
</div>

<?php if (!empty($search) && !empty($search_results)): ?>
<div class="search-results no-print">
    <h3>Matching Student Records Found</h3>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        <?php foreach ($search_results as $result): 
            $student_no = htmlspecialchars($result['student_id']);
            $last = !empty($result['last_name']) ? htmlspecialchars($result['last_name']) : htmlspecialchars($result['name']);
            $first = !empty($result['first_name']) ? htmlspecialchars($result['first_name']) : '';
            $section = !empty($result['section']) ? htmlspecialchars($result['section']) : 'No Section';
            
            $full_name = $first ? "$last, $first" : $last;
        ?>
            <div class="result-item">
                <a href="?student_id=<?php echo $result['id']; ?>&semester=<?php echo $semester; ?>&school_year=<?php echo urlencode($school_year); ?>">
                    <?php echo $student_no; ?> - <?php echo $full_name; ?> - <?php echo $section; ?>
                    <span style="float: right; font-size: 12px; background: rgba(0, 180, 216, 0.1); color: var(--text-subtitle); padding: 2px 8px; border-radius: 4px;">Grade <?php echo htmlspecialchars($result['grade_level']); ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif (!empty($search) && empty($search_results)): ?>
<div class="search-results no-print">
    <p style="color: var(--text-muted); font-size: 14px; margin: 0;">No matching student records found for "<?php echo htmlspecialchars($search); ?>".</p>
</div>
<?php endif; ?>

<?php if ($student): 
    $student_display_name = (!empty($student['last_name']) && !empty($student['first_name'])) ? $student['last_name'] . ', ' . $student['first_name'] : $student['name'];
?>
<div class="report-card">
    <div class="toolbar no-print">
        <form method="GET" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; flex: 1;">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <div>
                <label>Active Term:</label>
                <select name="semester" onchange="this.form.submit()">
                    <option value="1" <?php echo $semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                    <option value="2" <?php echo $semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                    <option value="3" <?php echo $semester == 3 ? 'selected' : ''; ?>>3rd Semester</option>
                </select>
            </div>
            <div>
                <label>School Year:</label>
                <input type="text" name="school_year" value="<?php echo htmlspecialchars($school_year); ?>">
            </div>
            <button type="submit" class="btn-print btn-secondary">Load Framework</button>
        </form>
        <button onclick="window.print()" class="btn-print">🖨️ Print Report Card</button>
    </div>

    <div class="print-container">
        <div class="deped-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Department_of_Education_of_the_Philippines_Seal.svg/1200px-Department_of_Education_of_the_Philippines_Seal.svg.png" alt="DepEd Seal">
            <h2>REPUBLIC OF THE PHILIPPINES<br>DEPARTMENT OF EDUCATION</h2>
            <p>NATIONAL CAPITAL REGION &bull; DIVISION OF MANILA</p>
            <p class="school-title">TIMOTEO PAEZ INTEGRATED HIGH SCHOOL</p>
            <p style="font-size: 9px; color: #555555; font-style: italic;">139 Nepa St, Tondo, Manila, 1013 Metro Manila</p>
            <div style="margin-top: 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Learner's Progress Report Card &bull; SY <?php echo htmlspecialchars($school_year); ?> (Semester <?php echo $semester; ?>)</div>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 6px;">
            <div><strong>NAME:</strong> <?php echo htmlspecialchars($student_display_name); ?></div>
            <div><strong>LRN:</strong> <?php echo htmlspecialchars($student['lrn'] ?? $student['']); ?></div>
            <div><strong>GRADE & SECTION:</strong> Grade <?php echo $student['grade_level']; ?> - <?php echo htmlspecialchars($student['section'] ?? ''); ?></div>
        </div>
        
        <div class="report-card-body">
            <div class="report-column">
                <div class="header-text">Report on Learning Progress and Achievement</div>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 45%;">Learning Areas</th>
                            <th colspan="3">Semester Term Scores</th>
                            <th rowspan="2" style="width: 15%;">Final Rating</th>
                            <th rowspan="2" style="width: 15%;">Remarks</th>
                        </tr>
                        <tr>
                            <th style="width: 8%;">1</th>
                            <th style="width: 8%;">2</th>
                            <th style="width: 8%;">3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $mapeh_printed = false;
                        foreach ($subject_grades as $sg): 
                            $subject_name = trim($sg['name']);
                            $is_mapeh_component = in_array($subject_name, ['Music', 'Arts', 'Physical Education', 'Health', 'P.E.', 'PE']);
                            
                            if ($is_mapeh_component && !$mapeh_printed):
                                $mapeh_printed = true;
                        ?>
                            <tr style="font-weight: bold; background-color: #f9f9f9;">
                                <td class="text-left">MAPEH</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <td class="text-left <?php echo $is_mapeh_component ? 'indented-subject' : ''; ?>">
                                <?php echo htmlspecialchars($subject_name); ?>
                            </td>
                            <td><?php echo ($semester == 1 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                            <td><?php echo ($semester == 2 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                            <td><?php echo ($semester == 3 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                            <td style="font-weight: 700;"><?php echo $sg['grade'] ?: ''; ?></td>
                            <td>
                                <?php if ($sg['grade']): ?>
                                    <span class="remarks-badge-print <?php echo $sg['passed'] ? 'passed' : 'failed'; ?>">
                                        <?php echo $sg['passed'] ? 'Passed' : 'Failed'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td class="text-left" style="padding-top: 6px; padding-bottom: 6px;">GENERAL AVERAGE</td>
                            <td colspan="3"></td>
                            <td style="font-size: 12px; font-weight: 800;"><?php echo $general_average > 0 ? $general_average : ''; ?></td>
                            <td style="text-transform: uppercase; font-weight: 800;"><?php echo $general_average > 0 ? $general_remarks : ''; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="legend-container">
                    <table class="legend-table">
                        <tr><th>Descriptors</th></tr>
                        <tr><td>Outstanding</td></tr>
                        <tr><td>Very Satisfactory</td></tr>
                        <tr><td>Satisfactory</td></tr>
                        <tr><td>Fairly Satisfactory</td></tr>
                        <tr><td>Did Not Meet Expectations</td></tr>
                    </table>
                    <table class="legend-table">
                        <tr><th style="text-align: center;">Grading Scale</th></tr>
                        <tr><td class="text-center">90 - 100</td></tr>
                        <tr><td class="text-center">85 - 89</td></tr>
                        <tr><td class="text-center">80 - 84</td></tr>
                        <tr><td class="text-center">75 - 79</td></tr>
                        <tr><td class="text-center">Below 75</td></tr>
                    </table>
                    <table class="legend-table">
                        <tr><th style="text-align: center;">Remarks</th></tr>
                        <tr><td class="text-center">Passed</td></tr>
                        <tr><td class="text-center">Passed</td></tr>
                        <tr><td class="text-center">Passed</td></tr>
                        <tr><td class="text-center">Passed</td></tr>
                        <tr><td class="text-center">Failed</td></tr>
                    </table>
                </div>
            </div>

            <div class="report-column">
                <div class="header-text">Report on Learner's Observed Values</div>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 25%;">Core Values</th>
                            <th rowspan="2" style="width: 50%;">Behavior Statements</th>
                            <th colspan="3">Semester Term Mark</th>
                        </tr>
                        <tr>
                            <th style="width: 8%;">1</th>
                            <th style="width: 8%;">2</th>
                            <th style="width: 8%;">3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="2" style="font-weight: bold; vertical-align: middle;">1. Maka-Diyos</td>
                            <td class="text-left">Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="text-left">Shows adherence to ethical principles by upholding truth in all undertakings.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="font-weight: bold; vertical-align: middle;">2. Makatao</td>
                            <td class="text-left">Is sensitive to individual, social, and cultural differences.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="text-left">Demonstrates contributions towards solidarity.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; vertical-align: middle;">3. Maka-Kalikasan</td>
                            <td class="text-left">Cares for environment and utilizes resources wisely, judiciously and economically.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="font-weight: bold; vertical-align: middle;">4. Maka-Bansa</td>
                            <td class="text-left">Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="text-left">Demonstrate appropriate behavior in carrying out activities in school, community and country.</td>
                            <td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="legend-container" style="justify-content: center;">
                    <table class="legend-table">
                        <tr>
                            <th style="text-align: center;">Marking</th>
                            <th>Non-Numerical Rating Alignment</th>
                        </tr>
                        <tr>
                            <td class="text-center" style="font-weight: 700;">AO</td>
                            <td>Always Observed</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="font-weight: 700;">SO</td>
                            <td>Sometimes Observed</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="font-weight: 700;">RO</td>
                            <td>Rarely Observed</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="font-weight: 700;">NO</td>
                            <td>Not Observed</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div> 
    </div> 
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>