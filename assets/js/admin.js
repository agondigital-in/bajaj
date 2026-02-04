// View screenshot
function viewScreenshot(path) {
    document.getElementById('screenshotPreview').src = '../uploads/screenshots/' + path;
    document.getElementById('screenshotModal').style.display = 'block';
}

function closeScreenshotModal() {
    document.getElementById('screenshotModal').style.display = 'none';
}

// View application details
function viewDetails(id) {
    window.location.href = 'view_application.php?id=' + id;
}

// Update application status
async function updateStatus(id, status) {
    if (!confirm('Are you sure you want to ' + status + ' this application?')) {
        return;
    }
    
    try {
        const response = await fetch('api/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, status: status})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Status updated successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Real-time updates
setInterval(() => {
    fetch('api/get_new_applications.php')
        .then(response => response.json())
        .then(data => {
            if (data.new_count > 0) {
                showNotification(data.new_count + ' new application(s)');
            }
        });
}, 30000); // Check every 30 seconds

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
