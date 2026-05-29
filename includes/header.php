<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Academic Record System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="logo">Academic Record System</div>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="students.php">Students</a>
                <a href="assign_teachers.php">Assign Teachers</a>
            <?php endif; ?>
            <a href="grades.php">Grade Entry</a>
            <a href="report_card.php">Report Card</a>
            <a href="logout.php">Logout</a>
            
        </div>
        <div class="user">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </div>
    <div class="container">