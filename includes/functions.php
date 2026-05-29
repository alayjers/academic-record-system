<?php
function getSections($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section");
    return $stmt->fetchAll();
}

function getStudentsBySection($pdo, $section) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE section = ? ORDER BY name");
    $stmt->execute([$section]);
    return $stmt->fetchAll();
}

function getAssignmentsBySemester($pdo, $semester) {
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE semester = ? ORDER BY 
        CASE category 
            WHEN 'written' THEN 1 
            WHEN 'performance' THEN 2 
            WHEN 'exam' THEN 3 
        END, id");
    $stmt->execute([$semester]);
    return $stmt->fetchAll();
}

function getScores($pdo, $student_ids, $semester) {
    if (empty($student_ids)) return [];
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $stmt = $pdo->prepare("SELECT student_id, assignment_id, score FROM scores WHERE student_id IN ($placeholders) AND semester = ?");
    $params = array_merge($student_ids, [$semester]);
    $stmt->execute($params);
    $scores = [];
    while ($row = $stmt->fetch()) {
        $scores[$row['student_id']][$row['assignment_id']] = $row['score'];
    }
    return $scores;
}

function calculateStudentGrades($students, $assignments, $scores) {
    $written = array_filter($assignments, function($a) { return $a['category'] == 'written'; });
    $performance = array_filter($assignments, function($a) { return $a['category'] == 'performance'; });
    $exams = array_filter($assignments, function($a) { return $a['category'] == 'exam'; });
    
    $student_data = [];
    foreach ($students as $student) {
        // Written Works
        $written_total = 0;
        $written_ps = 0;
        $written_wa = 0;
        $written_count = 0;
        $written_scores = [];
        foreach ($written as $w) {
            $score = isset($scores[$student['id']][$w['id']]) ? $scores[$student['id']][$w['id']] : null;
            $written_scores[$w['id']] = $score;
            if ($score !== null && $w['max_score'] > 0) {
                $percentage = ($score / $w['max_score']) * 100;
                $written_total += $score;
                $written_ps += $percentage;
                $written_count++;
            }
        }
        $written_ps = $written_count > 0 ? $written_ps / $written_count : 0;
        $written_wa = $written_ps * 0.4;
        
        // Performance Tasks
        $performance_total = 0;
        $performance_ps = 0;
        $performance_wa = 0;
        $performance_count = 0;
        $performance_scores = [];
        foreach ($performance as $p) {
            $score = isset($scores[$student['id']][$p['id']]) ? $scores[$student['id']][$p['id']] : null;
            $performance_scores[$p['id']] = $score;
            if ($score !== null && $p['max_score'] > 0) {
                $percentage = ($score / $p['max_score']) * 100;
                $performance_total += $score;
                $performance_ps += $percentage;
                $performance_count++;
            }
        }
        $performance_ps = $performance_count > 0 ? $performance_ps / $performance_count : 0;
        $performance_wa = $performance_ps * 0.4;
        
        // Exams
        $exam_total = 0;
        $exam_ps = 0;
        $exam_wa = 0;
        $exam_count = 0;
        $exam_scores = [];
        foreach ($exams as $e) {
            $score = isset($scores[$student['id']][$e['id']]) ? $scores[$student['id']][$e['id']] : null;
            $exam_scores[$e['id']] = $score;
            if ($score !== null && $e['max_score'] > 0) {
                $percentage = ($score / $e['max_score']) * 100;
                $exam_total += $score;
                $exam_ps += $percentage;
                $exam_count++;
            }
        }
        $exam_ps = $exam_count > 0 ? $exam_ps / $exam_count : 0;
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
    return $student_data;
}

function saveGrades($pdo, $scores_data, $max_scores, $quarterly_grades, $semester) {
    // Save max scores
    foreach ($max_scores as $assignment_id => $max_score) {
        $stmt = $pdo->prepare("UPDATE assignments SET max_score = ? WHERE id = ?");
        $stmt->execute([$max_score, $assignment_id]);
    }
    
    // Save student scores
    foreach ($scores_data as $student_id => $assignments) {
        foreach ($assignments as $assignment_id => $score) {
            if ($score !== '' && $score !== null) {
                $stmt = $pdo->prepare("SELECT id FROM scores WHERE student_id = ? AND assignment_id = ? AND semester = ?");
                $stmt->execute([$student_id, $assignment_id, $semester]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE scores SET score = ? WHERE student_id = ? AND assignment_id = ? AND semester = ?");
                    $stmt->execute([$score, $student_id, $assignment_id, $semester]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO scores (student_id, assignment_id, score, semester) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$student_id, $assignment_id, $score, $semester]);
                }
            }
        }
    }
}
?>