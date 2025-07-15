// General JavaScript for the application
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.createElement('div');
    sidebarToggle.className = 'sidebar-toggle';
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('header').prepend(sidebarToggle);
    
    sidebarToggle.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && !e.target.closest('.sidebar') && !e.target.closest('.sidebar-toggle')) {
            document.querySelector('.sidebar').classList.remove('active');
        }
    });
    
    // Notification bell click
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            // In a real app, this would show notifications
            alert('You have ' + (this.querySelector('.notification-count')?.textContent || 'no') + ' pending tasks');
        });
    }
});