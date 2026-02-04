let currentClickId = null;
let uploadedScreenshot = null;

// Show fullscreen offer popup and confetti on page load
window.addEventListener('DOMContentLoaded', function() {
    // Show popup for 2 seconds
    const popup = document.getElementById('offerPopup');
    setTimeout(function() {
        popup.style.display = 'none';
    }, 2000);
    
    // Start confetti
    createConfetti();
});

// Create confetti effect
function createConfetti() {
    const container = document.getElementById('confetti-container');
    const colors = ['#FE8B0B', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];
    const confettiCount = 150;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        container.appendChild(confetti);
    }
    
    // Remove confetti after animation
    setTimeout(function() {
        container.innerHTML = '';
    }, 5000);
}

// Change main image when thumbnail is clicked
function changeImage(thumbnail, imageUrl) {
    // Update main image
    document.getElementById('mainImage').src = imageUrl;
    
    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked thumbnail
    thumbnail.classList.add('active');
}

// Apply Now button
async function applyNow() {
    try {
        const response = await fetch('api/apply.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentClickId = data.click_id;
            
            // Open Bajaj Finserv URL with click_id appended after 1114_A2_
            const url = `https://www.bajajfinserv.in/webform/v1/emicard/login?utm_source=Expartner&utm_medium=AGON&utm_campaign=F_${currentClickId}`;
            window.open(url, '_blank');
            
            // Show upload modal after 2 seconds
            setTimeout(() => {
                document.getElementById('uploadModal').style.display = 'block';
            }, 2000);
        }
    } catch (error) {
        console.log('Error: ' + error.message);
        // Fallback: open with default campaign ID
        window.open('https://www.bajajfinserv.in/webform/v1/emicard/login?utm_source=Expartner&utm_medium=AGON&utm_campaign=F_', '_blank');
    }
}

// Handle file selection
async function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImage').src = e.target.result;
        document.getElementById('previewArea').style.display = 'block';
        document.getElementById('uploadArea').style.display = 'none';
    };
    reader.readAsDataURL(file);
    
    // Upload and validate with AI
    const formData = new FormData();
    formData.append('screenshot', file);
    
    try {
        const response = await fetch('api/validate_screenshot.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.valid) {
            uploadedScreenshot = data.filename;
            
            // Show success
            document.getElementById('aiValidation').innerHTML = `
                <div class="success">
                    <span class="check">✓</span>
                    <p>Screenshot verified! Confidence: ${data.confidence}%</p>
                </div>
            `;
            
            // Auto-fill extracted data
            if (data.extracted_data.name) {
                document.getElementById('userName').value = data.extracted_data.name;
            }
            
            // Show details form after 1 second
            setTimeout(() => {
                closeModal();
                showDetailsModal();
            }, 1500);
        } else {
            document.getElementById('aiValidation').innerHTML = `
                <div class="error">
                    <span class="cross">✗</span>
                    <p>Screenshot validation failed. Please upload a valid approval screenshot.</p>
                </div>
            `;
        }
    } catch (error) {
        alert('Upload error: ' + error.message);
    }
}

// Show details modal
function showDetailsModal() {
    document.getElementById('detailsModal').style.display = 'block';
    document.getElementById('clickId').value = currentClickId;
    document.getElementById('screenshotData').value = uploadedScreenshot;
}

// Submit application
async function submitApplication(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('api/submit_application_simple.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        console.log('Response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            alert('Server error. Response: ' + text.substring(0, 200));
            return;
        }
        
        if (data.success) {
            alert('Application submitted successfully! Application ID: ' + data.application_id);
            closeDetailsModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Submission error: ' + error.message);
    }
}

// Modal controls
function closeModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    const uploadModal = document.getElementById('uploadModal');
    const detailsModal = document.getElementById('detailsModal');
    
    if (event.target == uploadModal) {
        closeModal();
    }
    if (event.target == detailsModal) {
        closeDetailsModal();
    }
}
