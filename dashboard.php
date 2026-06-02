<?php
require_once 'includes/header.php';
?>

<h1>Dashboard</h1>
<div class="card-panel">
    <p style="font-size: 16px; margin-bottom: 24px; color: var(--input-text); font-weight: 500;">Welcome to the Academic Record System.</p>
    
    <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 10px;">
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <p><a href="students.php" class="dashboard-link">→ Manage Students</a></p>
            <p><a href="assign_teachers.php" class="dashboard-link">→ Assign Teachers to Sections</a></p>
        <?php endif; ?>
        <p><a href="grades.php" class="dashboard-link">→ Enter Student Grades</a></p>
        <p><a href="report_card.php" class="dashboard-link">→ View Report Cards</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>