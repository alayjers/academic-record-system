<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Record System</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="header">
        <div class="logo">Academic Record System</div>
        <div class="nav">
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="students.php" class="<?php echo $current_page == 'students.php' ? 'active' : ''; ?>">Students</a>
                <a href="assign_teachers.php" class="<?php echo $current_page == 'assign_teachers.php' ? 'active' : ''; ?>">Assign Teachers</a>
            <?php endif; ?>
            <a href="grades.php" class="<?php echo $current_page == 'grades.php' ? 'active' : ''; ?>">Grade Entry</a>
            <a href="report_card.php" class="<?php echo $current_page == 'report_card.php' ? 'active' : ''; ?>">Report Card</a>
            <button class="theme-toggle-btn" id="themeToggle" style="margin-left: 8px;">
                <span id="themeText">Light Mode</span>
            </button>
            <a href="logout.php" class="logout-btn" style="margin-left: 8px;">Sign Out</a>
        </div>
    </div>

    <div class="container">