// Elite Car Hire - Main JavaScript

// Notification handling
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
});

// AJAX helper
async function apiCall(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('API call failed:', error);
        throw error;
    }
}

// Mark notification as read
async function markNotificationRead(notificationId) {
    try {
        await apiCall('/api/notifications/mark-read', 'POST', { id: notificationId });
    } catch (error) {
        console.error('Failed to mark notification as read');
    }
}

// Calendar integration (for owner/admin calendars)
function initCalendar(elementId) {
    // Placeholder for calendar initialization
    // In production, you would integrate with a calendar library
    console.log('Calendar initialized for', elementId);
}

// Analytics charts (for dashboard analytics)
function initCharts() {
    // Placeholder for chart initialization
    // In production, you would integrate with a charting library like Chart.js
    console.log('Charts initialized');
}

// Image preview for uploads
function previewImages(input) {
    const preview = document.getElementById('image-preview');
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '150px';
                img.style.margin = '10px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Search functionality
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            let txtValue = rows[i].textContent || rows[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
}

// Payment form handling
function handlePaymentForm() {
    const form = document.getElementById('payment-form');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        try {
            const result = await apiCall('/api/payment/process', 'POST', data);
            if (result.success) {
                showNotification('Payment processed successfully!', 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showNotification(result.message || 'Payment failed', 'error');
            }
        } catch (error) {
            showNotification('Payment processing error', 'error');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    handlePaymentForm();
});
