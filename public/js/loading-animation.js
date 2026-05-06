// Loading Animation Function
function showLoading(text = 'Logging in...') {
    let loadingOverlay = document.getElementById('loadingOverlay');
    
    if (!loadingOverlay) {
        // Create loading overlay if it doesn't exist
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loadingOverlay';
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="loading-logo">
                <img src="/images/EMB-Logo.png" alt="Loading..." />
            </div>
            <div class="loading-text">${text}</div>
            <div class="loading-spinner"></div>
        `;
        document.body.appendChild(loadingOverlay);
    }
    
    // Update text
    const textElement = loadingOverlay.querySelector('.loading-text');
    if (textElement) {
        textElement.textContent = text;
    }
    
    // Show loading
    loadingOverlay.classList.add('active');
}

function hideLoading() {
    let loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('active');
    }
}

// Enhanced load function with loading animation
function load(url, vars = '') {
    // Show loading animation
    showLoading();
    
    // Create and send request
    var req = new XMLHttpRequest();
    req.open("POST", url, true);
    
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    req.onreadystatechange = function () {
        if (req.readyState == 4) {
            // Hide loading animation
            hideLoading();
            
            if (req.status == 200) {
                // Content is loaded... display content
                if (req.responseText) {
                    let contentDiv = document.getElementById('content');
                    if (contentDiv) {
                        contentDiv.innerHTML = req.responseText;
                    }
                }
            } else {
                // Handle error
                console.error('Request failed with status:', req.status);
            }
        }
    };
    
    // Send request
    req.send(vars);
}

// Button loading animation
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.classList.add('btn-loading');
        button.disabled = true;
        
        // Store original text
        if (!button.hasAttribute('data-original-text')) {
            button.setAttribute('data-original-text', button.textContent);
        }
        
        // Show loading indicator
        button.innerHTML = '<span class="btn-text">' + button.getAttribute('data-original-text') + '</span>';
    } else {
        button.classList.remove('btn-loading');
        button.disabled = false;
        
        // Restore original text
        if (button.hasAttribute('data-original-text')) {
            button.textContent = button.getAttribute('data-original-text');
            button.removeAttribute('data-original-text');
        }
    }
}

// Initialize loading for login and logout forms
document.addEventListener('DOMContentLoaded', function() {
    // Find login forms and add loading animation
    const loginForms = document.querySelectorAll('form[action*="login"], form[action*="auth"]');
    
    loginForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitButton) {
                setButtonLoading(submitButton, true);
            }
        });
    });
    
    // Find logout links and add loading animation
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show logout loading animation
            showLoading('Logging out...');
            
            // Navigate to logout URL after showing loading
            setTimeout(() => {
                window.location.href = this.href;
            }, 500);
        });
    });
    
    // Hide loading if page is loaded
    if (document.readyState === 'complete') {
        setTimeout(hideLoading, 1000);
    } else {
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 1000);
        });
    }
});

// Export functions for global use
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.load = load;
window.setButtonLoading = setButtonLoading;
