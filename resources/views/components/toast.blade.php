<script>
    window.showToast = function(message, type = 'error') {
        let container = document.getElementById('custom-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'custom-toast-container';
            container.style.position = 'fixed';
            container.style.top = '100px';
            container.style.right = '20px';
            container.style.zIndex = '999999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            container.style.maxWidth = '350px';
            container.style.width = 'calc(100% - 40px)';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.justifyContent = 'space-between';
        toast.style.padding = '15px 20px';
        toast.style.borderRadius = '12px';
        toast.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.3)';
        toast.style.backdropFilter = 'blur(10px)';
        toast.style.webkitBackdropFilter = 'blur(10px)';
        toast.style.transition = 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        
        let borderColor, iconClass;
        if (type === 'error') {
            borderColor = 'rgba(239, 68, 68, 0.5)';
            iconClass = 'fas fa-exclamation-circle';
        } else if (type === 'success') {
            borderColor = 'rgba(16, 185, 129, 0.5)';
            iconClass = 'fas fa-check-circle';
        } else {
            borderColor = 'rgba(59, 130, 246, 0.5)';
            iconClass = 'fas fa-info-circle';
        }
        
        toast.style.backgroundColor = 'rgba(15, 23, 42, 0.95)'; // dark theme-compatible background
        toast.style.border = `1px solid ${borderColor}`;
        toast.style.color = '#ffffff';

        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <i class="${iconClass}" style="color: ${type === 'error' ? '#ef4444' : (type === 'success' ? '#10b981' : '#3b82f6')}; font-size: 1.25rem;"></i>
                <span style="font-size: 0.9rem; font-weight: 500; line-height: 1.4; font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">${message}</span>
            </div>
            <button class="custom-toast-close-btn" style="background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; font-size: 1.1rem; display: flex; align-items: center; transition: color 0.2s; outline: none;">
                <i class="fas fa-times"></i>
            </button>
        `;

        const closeBtn = toast.querySelector('.custom-toast-close-btn');
        closeBtn.addEventListener('mouseenter', () => closeBtn.style.color = '#ffffff');
        closeBtn.addEventListener('mouseleave', () => closeBtn.style.color = '#94a3b8');

        container.appendChild(toast);

        // Slide in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);

        const removeToast = () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 500);
        };

        closeBtn.addEventListener('click', removeToast);

        // Auto dismiss after 5 seconds
        setTimeout(removeToast, 5000);
    };
</script>
