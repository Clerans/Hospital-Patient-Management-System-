/**
 * Hospital Patient Management System (HPMS)
 * Interactive Client Scripts & UI Handlers
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Clock in Header
    initLiveClock();

    // 2. Auto calculate age from DOB in registration/edit forms
    initDobCalculator();

    // 3. Delete Patient Confirmation Modal
    initDeleteModal();

    // 4. Auto-dismiss alerts after 5 seconds
    initFlashAutoDismiss();
});

/**
 * Update real-time clock in navigation bar
 */
function initLiveClock() {
    const clockEl = document.getElementById('liveClock');
    if (!clockEl) return;

    function updateTime() {
        const now = new Date();
        const options = { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        };
        clockEl.textContent = now.toLocaleDateString('en-US', options);
    }

    updateTime();
    setInterval(updateTime, 1000);
}

/**
 * Calculate age dynamically when Date of Birth field changes
 */
function initDobCalculator() {
    const dobInput = document.getElementById('date_of_birth');
    const ageDisplay = document.getElementById('calculated_age_display');

    if (!dobInput || !ageDisplay) return;

    function updateAge() {
        const val = dobInput.value;
        if (!val) {
            ageDisplay.textContent = 'Enter DOB to calculate';
            return;
        }

        const birthDate = new Date(val);
        const today = new Date();

        if (isNaN(birthDate.getTime())) {
            ageDisplay.textContent = 'Invalid date';
            return;
        }

        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 0) {
            ageDisplay.textContent = 'Future date selected!';
            ageDisplay.style.color = 'var(--danger)';
        } else {
            ageDisplay.textContent = `${age} years old`;
            ageDisplay.style.color = 'var(--primary-dark)';
        }
    }

    dobInput.addEventListener('change', updateAge);
    dobInput.addEventListener('input', updateAge);
    // Initial trigger if value already populated
    if (dobInput.value) {
        updateAge();
    }
}

/**
 * Modal Handling for Delete Patient Confirmation
 */
let targetDeleteUrl = '';

function initDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtns = document.querySelectorAll('.close-modal-btn');
    const patientNameEl = document.getElementById('deletePatientName');

    if (!modal) return;

    // Attach click listener to all delete trigger buttons
    document.querySelectorAll('.btn-delete-trigger').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name') || 'this patient';
            const code = btn.getAttribute('data-code') || '';

            if (patientNameEl) {
                patientNameEl.textContent = `${name} (${code})`;
            }

            targetDeleteUrl = `delete-patient.php?id=${encodeURIComponent(id)}`;
            modal.classList.add('active');
        });
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (targetDeleteUrl) {
                window.location.href = targetDeleteUrl;
            }
        });
    }

    cancelBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('active');
            targetDeleteUrl = '';
        });
    });

    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
            targetDeleteUrl = '';
        }
    });
}

/**
 * Auto dismiss flash alerts
 */
function initFlashAutoDismiss() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });
}
