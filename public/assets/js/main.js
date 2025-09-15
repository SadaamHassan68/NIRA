/**
 * NIRA System - Main JavaScript
 * Somalia National Identification & Registration Authority
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Load dashboard statistics
    loadDashboardStats();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize file upload previews
    initializeFileUploads();
    
    // Initialize QR code scanner
    initializeQRScanner();
});

/**
 * Load dashboard statistics
 */
function loadDashboardStats() {
    fetch('api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                animateCounter('total-citizens', data.stats.total_citizens);
                animateCounter('total-ids', data.stats.total_ids);
                animateCounter('total-verifications', data.stats.total_verifications);
                animateCounter('active-regions', data.stats.active_regions);
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

/**
 * Animate counter numbers
 */
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = 0;
    const duration = 2000;
    const increment = targetValue / (duration / 16);
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.textContent = Math.floor(currentValue).toLocaleString();
    }, 16);
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize file upload previews
 */
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById(input.dataset.preview);
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
                
                // Validate file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('File size must be less than 5MB', 'danger');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    showAlert('Only JPG, PNG, and PDF files are allowed', 'danger');
                    input.value = '';
                    return;
                }
            }
        });
    });
}

/**
 * Initialize QR code scanner
 */
function initializeQRScanner() {
    const scannerButton = document.getElementById('qr-scanner-btn');
    if (scannerButton) {
        scannerButton.addEventListener('click', function() {
            // This would integrate with a QR scanner library
            showAlert('QR Scanner functionality will be implemented with device camera access', 'info');
        });
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Create alert container if it doesn't exist
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Generate NIN (National Identification Number)
 */
function generateNIN() {
    // Format: SO-YYYY-NNNNNN (SO + Year + 6-digit number)
    const year = new Date().getFullYear();
    const randomNum = Math.floor(Math.random() * 900000) + 100000;
    return `SO-${year}-${randomNum}`;
}

/**
 * Format phone number
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 10) {
        value = value.substring(0, 10);
    }
    input.value = value;
}

/**
 * Validate Somali phone number
 */
function validateSomaliPhone(phone) {
    const somaliPhoneRegex = /^(\+252|252|0)?(61|62|63|64|65|66|67|68|69|70|71|72|73|74|75|76|77|78|79|80|81|82|83|84|85|86|87|88|89|90|91|92|93|94|95|96|97|98|99)\d{7}$/;
    return somaliPhoneRegex.test(phone);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Copied to clipboard!', 'success');
    }).catch(() => {
        showAlert('Failed to copy to clipboard', 'danger');
    });
}

/**
 * Download file
 */
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Confirm action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Show loading spinner
 */
function showLoading(element) {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border spinner-border-sm me-2';
    spinner.setAttribute('role', 'status');
    element.prepend(spinner);
    element.disabled = true;
}

/**
 * Hide loading spinner
 */
function hideLoading(element) {
    const spinner = element.querySelector('.spinner-border');
    if (spinner) {
        spinner.remove();
    }
    element.disabled = false;
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}
