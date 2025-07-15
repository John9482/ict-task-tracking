// Task filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tasksTable = document.getElementById('tasksTable');
    
    if (!searchInput || !statusFilter || !tasksTable) return;
    
    function filterTasks() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        const rows = tasksTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const taskText = cells[2].textContent.toLowerCase();
            const statusText = cells[5].querySelector('.status-badge').textContent;
            
            const matchesSearch = taskText.includes(searchTerm);
            const matchesStatus = statusValue === '' || statusText === statusValue;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterTasks);
    statusFilter.addEventListener('change', filterTasks);
});