<?php 
require_once 'includes/header.php'; 
require_once 'config/config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? '';
$is_admin = ($role === 'admin');

$display_role = 'User';
if ($is_admin) {
    $display_role = 'Admin';
} elseif (strpos(strtolower($role), 'teacher') !== false) {
    try {
        $stmt_sub = $pdo->prepare("
            SELECT DISTINCT sub.name 
            FROM teacher_subject_section tss
            INNER JOIN subjects sub ON tss.subject_id = sub.id
            WHERE tss.teacher_id = ? LIMIT 1
        ");
        $stmt_sub->execute([$user_id]);
        $subject_name = $stmt_sub->fetchColumn();
        
        $display_role = $subject_name ? htmlspecialchars($subject_name) . " Teacher" : "Teacher";
    } catch (Exception $e) {
        $display_role = "Teacher";
    }
}

$total_students = 0;
$total_sections = 0;
$total_teachers = 0;
$my_sections = [];

if (!$is_admin) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT s.id, s.name, s.grade_level, s.school_year 
            FROM sections s
            LEFT JOIN advisory_section adv ON s.id = adv.section_id
            LEFT JOIN teacher_subject_section tss ON s.id = tss.section_id
            WHERE adv.teacher_id = ? OR tss.teacher_id = ?
        ");
        $stmt->execute([$user_id, $user_id]);
        $my_sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $my_sections = [];
    }
} else {
    try {
        $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $total_sections = $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn();
        $total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role LIKE '%teacher%'")->fetchColumn();
    } catch (Exception $e) {
        $total_students = 0;
        $total_sections = 0;
        $total_teachers = 0;
    }
}

// Dynamic Calendar Navigation State Setup
$target_month = isset($_GET['m']) ? intval($_GET['m']) : intval(date('n'));
$target_year  = isset($_GET['y']) ? intval($_GET['y']) : intval(date('Y'));

if ($target_month < 1) { $target_month = 12; $target_year--; }
if ($target_month > 12) { $target_month = 1; $target_year++; }

$prev_month = $target_month - 1; $prev_year = $target_year;
$next_month = $target_month + 1; $next_year = $target_year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
if ($next_month > 12) { $next_month = 1; $next_year++; }

// Official 2026 Philippine Holidays Dictionary Map
$ph_holidays = [
    '1-1'   => 'New Year\'s Day',
    '1-2'   => 'Special Non-Working Day',
    '2-25'  => 'EDSA People Power Anniversary',
    '4-2'   => 'Maundy Thursday',
    '4-3'   => 'Good Friday',
    '4-4'   => 'Black Saturday',
    '4-9'   => 'Araw ng Kagitingan (Day of Valor)',
    '5-1'   => 'Labor Day',
    '6-12'  => 'Independence Day',
    '8-21'  => 'Ninoy Aquino Day',
    '8-31'  => 'National Heroes Day',
    '11-1'  => 'All Saints\' Day (Undas)',
    '11-2'  => 'All Souls\' Day (Special Non-Working Day)',
    '11-30' => 'Bonifacio Day',
    '12-8'  => 'Feast of the Immaculate Conception',
    '12-24' => 'Christmas Eve (Special Non-Working Day)',
    '12-25' => 'Christmas Day',
    '12-30' => 'Rizal Day',
    '12-31' => 'Last Day of the Year'
];
?> 

<div style="background: var(--surface-card); border: 1px solid var(--border-card); padding: 16px 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: var(--shadow-card); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
    <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: var(--text-title);">
        Welcome, <?= $display_role ?>!
    </h2>
</div>

<h1>Dashboard</h1> 
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php if ($is_admin): ?>
            <div class="card-panel" style="margin-bottom: 0;">
                <h3 style="color: var(--text-title); font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 10px;">System Overview</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                    <div style="background: var(--bg-base); padding: 16px; border-radius: 8px; border: 1px solid var(--border-card);">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Students</span>
                        <p style="font-size: 24px; font-weight: 700; color: var(--text-title); margin-top: 4px;"><?= intval($total_students) ?></p>
                    </div>
                    <div style="background: var(--bg-base); padding: 16px; border-radius: 8px; border: 1px solid var(--border-card);">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Sections</span>
                        <p style="font-size: 24px; font-weight: 700; color: var(--text-title); margin-top: 4px;"><?= intval($total_sections) ?></p>
                    </div>
                    <div style="background: var(--bg-base); padding: 16px; border-radius: 8px; border: 1px solid var(--border-card);">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Active Teachers</span>
                        <p style="font-size: 24px; font-weight: 700; color: var(--text-title); margin-top: 4px;"><?= intval($total_teachers) ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card-panel" style="margin-bottom: 0;">
                <h3 style="color: var(--text-title); font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 10px;">My Assigned Sections</h3>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php if (empty($my_sections)): ?>
                        <p style="font-size: 13.5px; color: var(--text-muted);">No assigned classes found.</p>
                    <?php else: ?>
                        <?php foreach ($my_sections as $sec): ?>
                            <div style="font-size: 13.5px; display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed var(--border-card);">
                                <span style="color: var(--input-text); font-weight: 500;"><?= htmlspecialchars($sec['name']) ?></span>
                                <span style="color: var(--text-subtitle); font-weight: 600;">Grade <?= htmlspecialchars($sec['grade_level']) ?> (<?= htmlspecialchars($sec['school_year']) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-panel" style="margin-bottom: 0;">
                <h3 style="color: var(--text-title); font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 10px;">Tasks Tracker</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; font-weight: 600;">Unfinished Grading</h4>
                        <p style="color: #2d6a4f; font-size: 13.5px; font-weight: 500;">✓ Grades are up to date.</p>
                    </div>
                    <div>
                        <h4 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; font-weight: 600;">Did Not Meet Expectations</h4>
                        <p style="color: #2d6a4f; font-size: 13.5px; font-weight: 500;">✓ No students currently falling behind.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-panel" style="margin-bottom: 0; min-height: 380px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-card); padding-bottom: 10px;">
            <h3 style="color: var(--text-title); font-size: 18px; font-weight: 600; margin: 0;">Calendar</h3>
            <div style="display: flex; align-items: center; gap: 6px;">
                <a href="dashboard.php?m=<?= $prev_month ?>&y=<?= $prev_year ?>" style="text-decoration: none; font-size: 14px; font-weight: 700; color: var(--mode-btn-text); background: var(--mode-btn-bg); padding: 4px 10px; border-radius: 8px; border: 1px solid var(--mode-btn-border); transition: all 0.2s ease;">&lt;</a>
                <span style="font-size: 13px; font-weight: 600; color: var(--text-title); background: var(--mode-btn-bg); padding: 4px 12px; border-radius: 12px; border: 1px solid var(--mode-btn-border); min-width: 110px; text-align: center;">
                    <?= date('F Y', strtotime("$target_year-$target_month-01")) ?>
                </span>
                <a href="dashboard.php?m=<?= $next_month ?>&y=<?= $next_year ?>" style="text-decoration: none; font-size: 14px; font-weight: 700; color: var(--mode-btn-text); background: var(--mode-btn-bg); padding: 4px 10px; border-radius: 8px; border: 1px solid var(--mode-btn-border); transition: all 0.2s ease;">&gt;</a>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; gap: 4px; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;">
            <div>MO</div><div>TU</div><div>WE</div><div>TH</div><div>FR</div><div>SA</div><div>SU</div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; gap: 4px; row-gap: 8px;">
            <?php
            $first_day_of_month = date('Y-m-01', strtotime("$target_year-$target_month-01"));
            $days_in_month = date('t', strtotime($first_day_of_month));
            $day_of_week_index = date('N', strtotime($first_day_of_month));
            
            $real_today_day = date('j');
            $real_today_month = date('n');
            $real_today_year = date('Y');

            for ($i = 1; $i < $day_of_week_index; $i++) {
                echo '<div></div>';
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $is_today = ($day == $real_today_day && $target_month == $real_today_month && $target_year == $real_today_year);
                $holiday_key = "$target_month-$day";
                $is_holiday = isset($ph_holidays[$holiday_key]);

                $bg_style = 'color: var(--input-text); position: relative; cursor: default;';
                $title_attr = '';

                if ($is_today) {
                    $bg_style = 'background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%); color: #ffffff; font-weight: 700; border-radius: 50%; box-shadow: 0 4px 10px var(--primary-shadow); position: relative;';
                } elseif ($is_holiday) {
                    $bg_style = 'background: var(--banner-bg); color: var(--banner-text); font-weight: 700; border-radius: 50%; position: relative; border: 1px solid var(--banner-border);';
                    $title_attr = 'title="' . htmlspecialchars($ph_holidays[$holiday_key]) . '"';
                }

                echo '<div ' . $title_attr . ' style="font-size: 13px; height: 32px; width: 32px; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto; ' . $bg_style . '">';
                echo $day;
                
                if ($is_holiday && !$is_today) {
                    echo '<span style="position: absolute; bottom: 2px; width: 4px; height: 4px; background-color: var(--banner-text); border-radius: 50%;"></span>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <div style="margin-top: 24px; padding-top: 14px; border-top: 1px dashed var(--border-card);">
            <h4 style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px; letter-spacing: 0.5px;">Holidays this month:</h4>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <?php
                $found_holidays = false;
                foreach ($ph_holidays as $key => $name) {
                    list($m, $d) = explode('-', $key);
                    if (intval($m) === $target_month) {
                        $found_holidays = true;
                        echo '<div style="font-size: 12px; display: flex; align-items: center; gap: 8px; color: var(--input-text); font-weight: 500;">';
                        echo '<span style="width: 6px; height: 6px; background: var(--banner-text); border-radius: 50%; display: inline-block;"></span>';
                        echo '<strong>' . date('M d', strtotime("$target_year-$m-$d")) . ':</strong> ' . htmlspecialchars($name);
                        echo '</div>';
                    }
                }
                if (!$found_holidays) {
                    echo '<p style="font-size: 12px; color: var(--text-muted); font-style: italic;">No public holidays scheduled.</p>';
                }
                ?>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>