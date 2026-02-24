// toewijzen.js
document.addEventListener("DOMContentLoaded", () => {

    /* ================= Mobile menu ================= */
    const hamburger = document.getElementById("hamburger");
    const mobileMenu = document.getElementById("mobileMenu");

    if (hamburger && mobileMenu) {
        hamburger.addEventListener("click", (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle("show");
        });

        // Sluit menu bij klik buiten
        document.addEventListener("click", (e) => {
            if (
                mobileMenu.classList.contains("show") &&
                !mobileMenu.contains(e.target) &&
                !hamburger.contains(e.target)
            ) {
                mobileMenu.classList.remove("show");
            }
        });
    }

    /* ================= User search ================= */
    const searchInput = document.getElementById("userSearch");
    const users = document.querySelectorAll(".user");

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const query = searchInput.value.toLowerCase();

            users.forEach(user => {
                const name = user.querySelector(".user-name")?.textContent || "";
                const email = user.querySelector(".user-email")?.textContent || "";

                user.style.display =
                    (name + email).toLowerCase().includes(query)
                        ? "flex"
                        : "none";
            });
        });
    }

    /* ================= Selected counter ================= */
    const checkboxes = document.querySelectorAll(".user input[type='checkbox']");
    const counter = document.getElementById("selectedCount");

    function updateCounter() {
        const count = [...checkboxes].filter(cb => cb.checked).length;
        counter.textContent =
            `${count} gebruiker${count !== 1 ? "s" : ""} geselecteerd`;
    }

    checkboxes.forEach(cb => cb.addEventListener("change", updateCounter));
    updateCounter();

    /* ================= Limit visible users ================= */
    const MAX_VISIBLE = 6;

    function limitVisibleUsers() {
        let shown = 0;

        users.forEach(user => {
            if (shown < MAX_VISIBLE) {
                user.style.display = "flex";
                shown++;
            } else {
                user.style.display = "none";
            }
        });
    }

    limitVisibleUsers();
});
