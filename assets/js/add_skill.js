// Add Skill Modal JavaScript
let selectedSkillId = null;
let selectedSkillName = null;

function openAddSkillModal() {
    const modal = document.getElementById('add-skill-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    const searchInput = document.getElementById('skill-search');
    searchInput.focus();

    // Show initial suggestions (popular skills not yet added)
    showInitialSuggestions();
}

function showInitialSuggestions() {
    const resultsContainer = document.getElementById('skill-search-results');
    resultsContainer.innerHTML = '';

    // Get skills not yet added, limit to 8
    const availableSkills = allSkills
        .filter(skill => !currentSkillIds.includes(skill.SkillID))
        .slice(0, 8);

    if (availableSkills.length === 0) {
        resultsContainer.style.display = 'none';
        return;
    }

    availableSkills.forEach(skill => {
        const item = document.createElement('div');
        item.className = 'skill-result-item';
        item.setAttribute('data-skill-id', skill.SkillID);
        item.innerHTML = `<span>${skill.SkillName}</span>`;
        item.onclick = () => {
            selectedSkillId = skill.SkillID;
            selectedSkillName = skill.SkillName;
            document.getElementById('skill-search').value = skill.SkillName;
            resultsContainer.style.display = 'none';
        };
        resultsContainer.appendChild(item);
    });

    resultsContainer.style.display = 'block';
}

function closeAddSkillModal() {
    const modal = document.getElementById('add-skill-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('skill-search').value = '';
    document.getElementById('skill-search-results').innerHTML = '';
    selectedSkillId = null;
    selectedSkillName = null;
}


function highlightSelectedSkill(skillId) {
    // Remove previous highlights
    document.querySelectorAll('.skill-result-item').forEach(item => {
        item.classList.remove('selected');
    });

    // Highlight selected
    const selectedItem = document.querySelector(`.skill-result-item[data-skill-id="${skillId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('selected');
    }
}

function filterSkills(searchTerm) {
    const resultsContainer = document.getElementById('skill-search-results');
    resultsContainer.innerHTML = '';

    if (!searchTerm || searchTerm.trim() === '') {
        resultsContainer.style.display = 'none';
        selectedSkillId = null;
        selectedSkillName = null;
        return;
    }

    const searchLower = searchTerm.toLowerCase().trim();
    const searchWords = searchLower.split(' ').filter(word => word.length > 0);

    // Filter and score skills
    const filtered = allSkills
        .filter(skill => {
            const isNotCurrent = !currentSkillIds.includes(skill.SkillID);
            return isNotCurrent;
        })
        .map(skill => {
            const skillNameLower = skill.SkillName.toLowerCase();
            let score = 0;

            // Exact match gets highest score
            if (skillNameLower === searchLower) {
                score = 1000;
            }
            // Starts with search term gets high score
            else if (skillNameLower.startsWith(searchLower)) {
                score = 500;
            }
            // Contains all search words gets medium score
            else if (searchWords.every(word => skillNameLower.includes(word))) {
                score = 100;
            }
            // Contains any search word gets low score
            else if (searchWords.some(word => skillNameLower.includes(word))) {
                score = 50;
            }

            return { ...skill, score };
        })
        .filter(skill => skill.score > 0)
        .sort((a, b) => b.score - a.score)
        .slice(0, 10);

    if (filtered.length === 0) {
        // Show option to add new skill
        const newSkillItem = document.createElement('div');
        newSkillItem.className = 'skill-result-item new-skill-item';
        newSkillItem.innerHTML = `
            <span class="new-skill-icon">+</span>
            <span>Add "${searchTerm}"</span>
        `;
        newSkillItem.onclick = () => {
            selectedSkillName = searchTerm;
            selectedSkillId = null;
            document.getElementById('skill-search').value = searchTerm;
            resultsContainer.style.display = 'none';
        };
        resultsContainer.appendChild(newSkillItem);
        resultsContainer.style.display = 'block';
        return;
    }

    // Show suggestions
    filtered.forEach(skill => {
        const item = document.createElement('div');
        item.className = 'skill-result-item';
        item.setAttribute('data-skill-id', skill.SkillID);

        // Highlight matching text
        const skillName = skill.SkillName;
        let highlightedName = skillName;
        if (searchWords.length > 0) {
            const regex = new RegExp(`(${searchWords.join('|')})`, 'gi');
            highlightedName = skillName.replace(regex, '<strong>$1</strong>');
        }

        item.innerHTML = `<span>${highlightedName}</span>`;
        item.onclick = () => {
            selectedSkillId = skill.SkillID;
            selectedSkillName = skill.SkillName;
            document.getElementById('skill-search').value = skill.SkillName;
            resultsContainer.style.display = 'none';
        };
        resultsContainer.appendChild(item);
    });

    // Add option to create new skill if search doesn't match exactly
    const exactMatch = filtered.find(s => s.SkillName.toLowerCase() === searchLower);
    if (!exactMatch && searchTerm.trim().length > 0) {
        const newSkillItem = document.createElement('div');
        newSkillItem.className = 'skill-result-item new-skill-item';
        newSkillItem.innerHTML = `
            <span class="new-skill-icon">+</span>
            <span>Add "${searchTerm}"</span>
        `;
        newSkillItem.onclick = () => {
            selectedSkillName = searchTerm;
            selectedSkillId = null;
            document.getElementById('skill-search').value = searchTerm;
            resultsContainer.style.display = 'none';
        };
        resultsContainer.appendChild(newSkillItem);
    }

    resultsContainer.style.display = 'block';
}

function saveSkill() {
    if (!selectedSkillName) {
        alert('Please select or enter a skill name.');
        return;
    }

    // Check if skill already exists in current skills
    const currentContainer = document.getElementById('current-skills-container');
    const existingSkill = Array.from(currentContainer.querySelectorAll('.skill-tag-item')).find(item => {
        return item.querySelector('.skill-tag-name').textContent === selectedSkillName;
    });

    if (existingSkill) {
        alert('This skill is already added.');
        return;
    }

    // If skill doesn't exist in database, we'll need to create it
    // For now, we'll use the skill ID if it exists, or create a placeholder
    let skillId = selectedSkillId;

    // Create skill tag element
    const skillTag = document.createElement('div');
    skillTag.className = 'skill-tag-item';
    skillTag.setAttribute('data-skill-id', skillId || 'new');
    skillTag.innerHTML = `
        <span class="skill-tag-name">${selectedSkillName}</span>
        <button type="button" class="skill-remove-btn" onclick="removeSkill(${skillId || 'new'})">×</button>
        <input type="hidden" name="skills[]" value="${skillId || selectedSkillName}">
        <input type="hidden" name="proficiency[${skillId || selectedSkillName}]" value="Intermediate">
    `;

    currentContainer.appendChild(skillTag);

    // Update currentSkillIds if this is a database skill
    if (selectedSkillId) {
        currentSkillIds.push(selectedSkillId);
    }

    closeAddSkillModal();
}

function removeSkill(skillId) {
    const skillTag = document.querySelector(`.skill-tag-item[data-skill-id="${skillId}"]`);
    if (skillTag) {
        skillTag.remove();

        // Remove from currentSkillIds if it's a database skill
        if (skillId !== 'new' && !isNaN(skillId)) {
            const index = currentSkillIds.indexOf(parseInt(skillId));
            if (index > -1) {
                currentSkillIds.splice(index, 1);
            }
        }
    }
}

// Search input event listener
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('skill-search');
    if (searchInput) {
        let debounceTimer;

        // Debounced input for better performance
        searchInput.addEventListener('input', function (e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterSkills(e.target.value);
            }, 150);
        });

        // Show suggestions on focus
        searchInput.addEventListener('focus', function (e) {
            if (e.target.value.trim()) {
                filterSkills(e.target.value);
            } else {
                showInitialSuggestions();
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (e) {
            const searchContainer = document.querySelector('.skill-search-container');
            const results = document.getElementById('skill-search-results');
            if (searchContainer && results && !searchContainer.contains(e.target) && e.target !== searchInput) {
                // Keep results visible if input has value
                if (!searchInput.value.trim()) {
                    results.style.display = 'none';
                }
            }
        });

        // Handle keyboard navigation
        let selectedIndex = -1;
        searchInput.addEventListener('keydown', function (e) {
            const results = document.querySelectorAll('.skill-result-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, results.length - 1);
                updateSelection(results, selectedIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(results, selectedIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && results[selectedIndex]) {
                    results[selectedIndex].click();
                } else {
                    saveSkill();
                }
            } else if (e.key === 'Escape') {
                document.getElementById('skill-search-results').style.display = 'none';
            }
        });

        function updateSelection(results, index) {
            results.forEach((item, i) => {
                if (i === index) {
                    item.classList.add('selected');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('selected');
                }
            });
        }
    }

    // Close modal when clicking outside
    const modal = document.getElementById('add-skill-modal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeAddSkillModal();
            }
        });
    }
});

