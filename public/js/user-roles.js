document.addEventListener("DOMContentLoaded", () => {

    //
    // 1️⃣  Dropdown 3-dots menu
    //
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".action-btn");

        if (btn) {
            const dropdown = btn.nextElementSibling;
            document.querySelectorAll(".dropdown-menu.show")
                .forEach(m => m !== dropdown && m.classList.remove("show"));

            dropdown.classList.toggle("show");
            return;
        }

        if (!e.target.closest(".action-dropdown")) {
            document.querySelectorAll(".dropdown-menu.show")
                .forEach(m => m.classList.remove("show"));
        }
    });


    //
    // 2️⃣ - Search filter
    //
    const searchInput = document.querySelector("#searchUsers");
    const roleFilter = document.querySelector("#roleFilter");

    if (searchInput) {
        searchInput.addEventListener("input", () =>
            filterUsers(searchInput.value.toLowerCase(), roleFilter.value.toLowerCase())
        );
    }

    if (roleFilter) {
        roleFilter.addEventListener("change", () =>
            filterUsers(searchInput.value.toLowerCase(), roleFilter.value.toLowerCase())
        );
    }

    function filterUsers(searchValue, roleValue) {
        document.querySelectorAll(".user-row").forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const roles = row.dataset.roles;

            const matchesSearch =
                name.includes(searchValue) || email.includes(searchValue);

            const matchesRole =
                roleValue === "all" || roles.includes(roleValue);

            row.style.display = (matchesSearch && matchesRole) ? "" : "none";
        });
    }


    //
    // 3️⃣ Rollen wijzig-modal open & checkboxes selecteren
    //
    document.querySelectorAll(".editRoleBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            let modal = document.getElementById("roleModal");
            let userId = btn.dataset.user;
            let row = btn.closest("tr");
            let roles = row.dataset.roles.split(",");

            document.querySelectorAll("#roleForm input[type='checkbox']")
                .forEach(cb => {
                    const label = cb.nextSibling.textContent.trim().toLowerCase();
                    cb.checked = roles.includes(label);
                });

            document.getElementById("roleForm").action = `/users/${userId}/roles`;

            modal.classList.remove("hidden");
        });
    });


    //
    // 4️⃣ Modal sluiten als je buiten klikt
    //
    document.addEventListener("click", (e) => {
        let modal = document.getElementById("roleModal");
        if (!modal.classList.contains("hidden")) {
            if (!e.target.closest(".modal-box") && !e.target.closest(".editRoleBtn")) {
                modal.classList.add("hidden");
            }
        }
    });

});

function closeRoleModal() {
    document.getElementById("roleModal").classList.add("hidden");
}
