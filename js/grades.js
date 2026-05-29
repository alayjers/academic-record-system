// Auto-save when input changes
document.addEventListener('DOMContentLoaded', function() {
    // Update max attributes when max_score changes
    document.querySelectorAll('.max-input').forEach(input => {
        input.addEventListener('change', function() {
            const newMax = this.value;
            const colIndex = this.closest('td').cellIndex;
            document.querySelectorAll('.grade-table tbody tr').forEach(row => {
                const cell = row.cells[colIndex];
                if (cell) {
                    const scoreInput = cell.querySelector('.score-input');
                    if (scoreInput) scoreInput.max = newMax;
                }
            });
        });
    });
});

function saveAllGrades() {
    document.getElementById('gradeForm').submit();
}