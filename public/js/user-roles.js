function openModal(userId, currentRoles) {
    const modal = document.getElementById('roleModal');
    const form = document.getElementById('roleForm');
    const roleSelect = document.getElementById('roleSelect');

    modal.style.display = 'block';

    form.action = `/user/${userId}/roles`;

    Array.from(roleSelect.options).forEach(option => {
        option.selected = currentRoles.includes(parseInt(option.value))
    })
}

function closeModal() {
    document.getElementById('roleModal').style.display = 'none';
}
