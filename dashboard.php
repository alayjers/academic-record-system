<?php
require_once 'includes/header.php';
?>

<h1>Dashboard</h1>
<div style="background:white; padding:20px; border-radius:5px;">
    <p>Welcome to the Academic Record System.</p>
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <p><a href="students.php">Manage Students</a></p>
        <p><a href="assign_teachers.php">Assign Teachers to Sections</a></p>
    <?php endif; ?>
    <p><a href="grades.php">Enter Grades</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>