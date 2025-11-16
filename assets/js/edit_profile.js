// Edit Profile JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide proficiency select when skill checkbox is toggled
    const skillCheckboxes = document.querySelectorAll('input[name="skills[]"]');
    
    skillCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const skillItem = this.closest('.skill-checkbox-item');
            const proficiencySelect = skillItem ? skillItem.querySelector('.proficiency-select') : null;
            
            if (proficiencySelect) {
                if (this.checked) {
                    proficiencySelect.style.display = 'inline-block';
                } else {
                    proficiencySelect.style.display = 'none';
                }
            }
        });
    });
});

