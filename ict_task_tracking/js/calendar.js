// Mini calendar for dashboard
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('mini-calendar');
    if (!calendarEl) return;
    
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    
    function renderCalendar(month, year) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();
        
        const monthNames = ["January", "February", "March", "April", "May", "June",
                          "July", "August", "September", "October", "November", "December"];
        
        // Header with month/year and navigation
        let calendarHTML = `
            <div class="header">
                <span class="month-year">${monthNames[month]} ${year}</span>
            </div>
            <div class="days">
        `;
        
        // Day headers
        const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        dayNames.forEach(day => {
            calendarHTML += `<div class="day-header">${day}</div>`;
        });
        
        // Previous month's days
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = 0; i < startingDay; i++) {
            calendarHTML += `<div class="day other-month">${prevMonthLastDay - startingDay + i + 1}</div>`;
        }
        
        // Current month's days
        for (let i = 1; i <= daysInMonth; i++) {
            const dayClass = (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) ? 'today' : '';
            calendarHTML += `<div class="day ${dayClass}">${i}</div>`;
        }
        
        // Next month's days
        const totalCells = startingDay + daysInMonth;
        const remainingCells = totalCells <= 35 ? 35 - totalCells : 42 - totalCells;
        
        for (let i = 1; i <= remainingCells; i++) {
            calendarHTML += `<div class="day other-month">${i}</div>`;
        }
        
        calendarHTML += `</div>`;
        calendarEl.innerHTML = calendarHTML;
    }
    
    renderCalendar(currentMonth, currentYear);
});