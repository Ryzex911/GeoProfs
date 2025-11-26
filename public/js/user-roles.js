document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const modal = document.getElementById('roleModal');
    const form = document.getElementById('roleForm');

    if (!modal || !form) return;

    // Open rol modal bij klik op "Bewerken" knop
    document.addEventListener('click', function (e) {
        const button = e.target.closest('[data-user-id]');
        if (button && button.dataset.userId) {
            const userId = parseInt(button.dataset.userId);
            const currentRoles = JSON.parse(button.dataset.userRoles || '[]');
            openModal(userId, currentRoles);
        }
    });

    // Open modal
    function openModal(userId, currentRoles) {
        modal.style.display = 'block';
        form.action = `/users/${userId}/roles`;

        const checkboxes = form.querySelectorAll('input[type="checkbox"][name="roles[]"]');
        checkboxes.forEach(checkbox => {
            const roleId = parseInt(checkbox.value);
            checkbox.checked = currentRoles.includes(roleId);
        });
    }

    // Sluit modal
    function closeModal() {
        modal.style.display = 'none';
    }

    // Close button
    const closeButton = document.getElementById('closeModalBtn');
    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    // Click buiten modal
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // Escape toets
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
});
