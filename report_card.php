<?php
require_once 'includes/header.php';
require_once 'config/config.php';

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1; 
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '2025-2026';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];

// Search for students
if (!empty($search)) {
    $like = "%$search%";
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, lrn, student_id, grade_level, section 
        FROM students 
        WHERE first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ? OR student_id LIKE ?
        ORDER BY last_name, first_name
        LIMIT 20
    ");
    $stmt->execute([$like, $like, $like, $like]);
    $search_results = $stmt->fetchAll();
}

// Get selected student data
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
            
            $total_weighted = 0;
            $total_weight = 0;
            foreach ($assignments as $a) {
                if ($a['score'] !== null && $a['score'] !== '') {
                    $percentage = ($a['score'] / $a['max_score']) * 100;
                    $total_weighted += $percentage * ($a['weight'] / 100);
                    $total_weight += $a['weight'];
                }
            }
            $final_grade = $total_weight > 0 ? round($total_weighted, 1) : 0;
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
    body { font-family: Arial, sans-serif; }
    .search-container { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .search-box { display: flex; gap: 10px; align-items: center; }
    .search-box input { flex: 1; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px; }
    .search-box button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
    
    .search-results { background: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .result-item { padding: 8px; border-bottom: 1px solid #eee; }
    .result-item a { text-decoration: none; color: #333; display: block; }
    .result-item:hover { background: #f5f5f5; }
    
    .report-card { background: white; padding: 20px; border-radius: 5px; }
    .student-info { margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px; border: 1px solid #ddd; }
    .toolbar { margin-bottom: 20px; background: white; padding: 10px 0; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
    .btn-print { background: #2196F3; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 3px; font-weight: bold; }
    
    /* 2-Column Layout */
    .report-card-body {
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }
    .report-column {
        flex: 1;
        min-width: 0;
    }
    
    .header-text { 
        font-weight: bold; 
        text-transform: uppercase; 
        text-align: center;
        margin-bottom: 10px; 
        font-size: 14px;
    }
    
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
    th, td { border: 1px solid black; padding: 6px; text-align: center; }
    th { background-color: #fff; }
    .text-left { text-align: left; padding-left: 10px; }
    .text-center { text-align: center; }
    
    /* MAPEH Indentation */
    .indented-subject { padding-left: 25px !important; }

    /* Legends */
    .legend-container {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-top: -5px;
    }
    .legend-table {
        width: auto;
        margin: 0 auto;
        border: none;
    }
    .legend-table th, .legend-table td {
        border: none;
        padding: 4px 15px;
        text-align: left;
    }
    .legend-table th { font-weight: bold; padding-bottom: 8px; }
    
    @media print { 
        /* Removes browser headers/footers (URL, time, page num) */
        @page { margin: 0; size: auto; }
        
        /* Hides any navigation from header.php */
        nav, header, footer, .navbar, .no-print { display: none !important; } 
        
        body { padding: 20px; margin: 0; background: white; }
        .report-card { padding: 0; box-shadow: none; }
        .report-card-body { gap: 15px; }
        th, td { padding: 4px; font-size: 10px; }
        .header-text { font-size: 12px; }
        .legend-container { font-size: 9px; }
    }
</style>

<h1 class="no-print">Report Card</h1>

<div class="search-container no-print">
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search by name, LRN, or Student ID..." value="<?php echo htmlspecialchars($search); ?>" autofocus>
        <button type="submit">🔍 Search</button>
    </form>
</div>

<?php if (!empty($search) && !empty($search_results)): ?>
<div class="search-results no-print">
    <h3>Select a student:</h3>
    <?php foreach ($search_results as $result): ?>
        <div class="result-item">
            <a href="?student_id=<?php echo $result['id']; ?>&semester=<?php echo $semester; ?>&school_year=<?php echo urlencode($school_year); ?>">
                <?php echo htmlspecialchars($result['last_name'] . ', ' . $result['first_name']); ?> 
                (<?php echo htmlspecialchars($result['lrn'] ?? 'N/A'); ?>) - Grade <?php echo $result['grade_level']; ?> - <?php echo htmlspecialchars($result['section'] ?? 'No Section'); ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php elseif (!empty($search) && empty($search_results)): ?>
<div class="search-results no-print">
    <p>No students found matching "<?php echo htmlspecialchars($search); ?>"</p>
</div>
<?php endif; ?>

<?php if ($student): ?>
<div class="report-card">
    <div class="toolbar no-print">
        <form method="GET" style="display: inline;">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <label>Semester:</label>
            <select name="semester" onchange="this.form.submit()">
                <option value="1" <?php echo $semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                <option value="2" <?php echo $semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                <option value="3" <?php echo $semester == 3 ? 'selected' : ''; ?>>3rd Semester</option>
            </select>
            <label style="margin-left: 15px;">School Year:</label>
            <input type="text" name="school_year" value="<?php echo htmlspecialchars($school_year); ?>" style="width: 100px;">
            <button type="submit" class="btn-print" style="margin-left: 10px;">Load</button>
        </form>
        <button onclick="window.print()" class="btn-print">🖨️ Print Report Card</button>
    </div>
    
    <div class="student-info no-print">
        <strong>Student:</strong> <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?> | 
        <strong>LRN:</strong> <?php echo htmlspecialchars($student['lrn'] ?? '_________________'); ?> | 
        <strong>Grade & Section:</strong> <?php echo $student['grade_level']; ?> - <?php echo htmlspecialchars($student['section'] ?? ''); ?> | 
        <strong>School Year:</strong> <?php echo htmlspecialchars($school_year); ?>
    </div>
    
    <div class="report-card-body">
        
        <div class="report-column">
            <div class="header-text">Report on Learning Progress and Achievement</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40%;">Learning Areas</th>
                        <th colspan="3">Semester</th>
                        <th rowspan="2" style="width: 15%;">Final Rating</th>
                        <th rowspan="2" style="width: 15%;">Remarks</th>
                    </tr>
                    <tr>
                        <th style="width: 10%;">1</th>
                        <th style="width: 10%;">2</th>
                        <th style="width: 10%;">3</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $mapeh_printed = false;
                    foreach ($subject_grades as $sg): 
                        $subject_name = trim($sg['name']);
                        $is_mapeh_component = in_array($subject_name, ['Music', 'Arts', 'Physical Education', 'Health', 'P.E.', 'PE']);
                        
                        // Print MAPEH Header exactly once if a MAPEH component is detected
                        if ($is_mapeh_component && !$mapeh_printed):
                            $mapeh_printed = true;
                    ?>
                        <tr>
                            <td class="text-left" style="font-weight: bold;">MAPEH</td>
                            <td style="background-color: #f2f2f2;"></td>
                            <td style="background-color: #f2f2f2;"></td>
                            <td style="background-color: #f2f2f2;"></td>
                            <td style="background-color: #f2f2f2;"></td>
                            <td style="background-color: #f2f2f2;"></td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <td class="text-left <?php echo $is_mapeh_component ? 'indented-subject' : ''; ?>">
                            <?php echo htmlspecialchars($subject_name); ?>
                        </td>
                        <td><?php echo ($semester == 1 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                        <td><?php echo ($semester == 2 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                        <td><?php echo ($semester == 3 && $sg['grade']) ? $sg['grade'] : ''; ?></td>
                        <td><?php echo $sg['grade'] ?: ''; ?></td>
                        <td><?php echo $sg['grade'] ? ($sg['passed'] ? 'Passed' : 'Failed') : ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold;">
                        <td class="text-left" style="padding-top: 10px; padding-bottom: 10px;">General Average</td>
                        <td colspan="3"></td>
                        <td><?php echo $general_average > 0 ? $general_average : ''; ?></td>
                        <td><?php echo $general_average > 0 ? $general_remarks : ''; ?></td>
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
                    <tr><th>Grading Scale</th></tr>
                    <tr><td class="text-center">90-100</td></tr>
                    <tr><td class="text-center">85-89</td></tr>
                    <tr><td class="text-center">80-84</td></tr>
                    <tr><td class="text-center">75-79</td></tr>
                    <tr><td class="text-center">Below 75</td></tr>
                </table>
                <table class="legend-table">
                    <tr><th>Remarks</th></tr>
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
                        <th rowspan="2" style="width: 45%;">Behavior Statements</th>
                        <th colspan="3">Semester</th>
                    </tr>
                    <tr>
                        <th style="width: 10%;">1</th>
                        <th style="width: 10%;">2</th>
                        <th style="width: 10%;">3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="2"><strong>1. Maka-Diyos</strong></td>
                        <td class="text-left">Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td class="text-left">Shows adherence to ethical principles by upholding truth in all undertakings.</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td rowspan="2"><strong>2. Makatao</strong></td>
                        <td class="text-left">Is sensitive to individual, social, and cultural differences;</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td class="text-left">Demonstrates contributions towards solidarity.</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td><strong>3. Maka-Kalikasan</strong></td>
                        <td class="text-left">Cares for environment and utilizes resources wisely, judiciously and economically.</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td rowspan="2"><strong>4. Maka-Bansa</strong></td>
                        <td class="text-left">Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.</td>
                        <td></td><td></td><td></td>
                    </tr>
                    <tr>
                        <td class="text-left">Demonstrate appropriate behavior in carrying out activities in school, community and country.</td>
                        <td></td><td></td><td></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="legend-container" style="justify-content: center; margin-top: 15px;">
                <table class="legend-table">
                    <tr>
                        <th style="text-align: center;">Marking</th>
                        <th>Non-Numerical Rating</th>
                    </tr>
                    <tr>
                        <td class="text-center">AO</td>
                        <td>Always Observed</td>
                    </tr>
                    <tr>
                        <td class="text-center">SO</td>
                        <td>Sometimes Observed</td>
                    </tr>
                    <tr>
                        <td class="text-center">RO</td>
                        <td>Rarely Observed</td>
                    </tr>
                    <tr>
                        <td class="text-center">NO</td>
                        <td>Not Observed</td>
                    </tr>
                </table>
            </div>
        </div>

    </div> </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>