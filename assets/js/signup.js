// Signup form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const userTypeSelect = document.getElementById('user_type');
    const freelancerFields = document.getElementById('freelancer-fields');
    const clientFields = document.getElementById('client-fields');
    const form = document.querySelector('form');
    
    // Handle user type change
    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', function() {
            const userType = this.value;
            
            if (userType === 'freelancer') {
                freelancerFields.classList.remove('hidden');
                clientFields.classList.add('hidden');
                document.getElementById('first_name').required = true;
                document.getElementById('last_name').required = true;
                document.getElementById('company_name').required = false;
            } else {
                freelancerFields.classList.add('hidden');
                clientFields.classList.remove('hidden');
                document.getElementById('first_name').required = false;
                document.getElementById('last_name').required = false;
                document.getElementById('company_name').required = true;
            }
        });
    }
    
    // Handle form submission - password validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    }
});

