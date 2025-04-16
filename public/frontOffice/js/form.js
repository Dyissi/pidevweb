document.addEventListener('DOMContentLoaded', function() {
    // Show success toast on form submission
    const form = document.querySelector('form.needs-validation');
    const successToast = new bootstrap.Toast(document.querySelector('.toast'));
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.preventDefault();
            
            // Submit form via AJAX
            fetch(form.action, {
                method: form.method,
                body: new FormData(form)
            })
            .then(response => {
                if (response.ok) {
                    // Show success message
                    successToast.show();
                    
                    // Reset form
                    form.classList.remove('was-validated');
                    form.reset();
                    
                    // Optional: Clear all invalid states
                    document.querySelectorAll('.is-invalid').forEach(el => {
                        el.classList.remove('is-invalid');
                    });
                }
            });
        }
        
        form.classList.add('was-validated');
    }, false);
});