/**
 * assets/js/admin.js - MyFolio CMS Interactivity
 */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts after 5 seconds if desired
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 6000);
    });
});

