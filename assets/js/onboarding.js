/* -------------------------------
   ONBOARDING DASHBOARD JS
--------------------------------- */

/* -------------------------------
   MOBILE MENU INTERACTIE
--------------------------------- */
const hamburger = document.getElementById("hamburger");
const mobileMenu = document.getElementById("mobileMenu");

hamburger.addEventListener("click", () => {
    mobileMenu.classList.toggle("open");
    mobileMenu.style.display = mobileMenu.classList.contains("open") ? "flex" : "none";
});

/* -------------------------------
   CHECKLIST + PROGRESS + CONFETTI
--------------------------------- */
document.addEventListener("DOMContentLoaded", () => {
    const progressCards = document.querySelectorAll(".progress-card");

    progressCards.forEach(card => {
        const items = card.parentElement.querySelectorAll(".checklist-item");
        const progressText = card.querySelector(".progress-num");
        const progressBar = card.querySelector(".progress-bar");
        let completed = 0;
        let percentage;

        if (items.length > 0) {
            // Count initial checked
            items.forEach(item => {
                const circle = item.querySelector(".circle");
                if (circle.classList.contains("checked")) {
                    completed++;
                }
            });
            percentage = Math.round((completed / items.length) * 100);
        } else {
            // For admin view, use data-percent
            percentage = parseInt(card.dataset.percent) || 0;
        }

        // Update progress
        function updateProgress() {
            progressText.textContent = percentage + "%";
            const stroke = 339.292;
            const offset = stroke - (stroke * (percentage / 100));
            progressBar.style.strokeDashoffset = offset;

            // Confetti trigger bij 100%
            if (percentage === 100 && items.length > 0) {  // only for users
                launchConfetti();
                sessionStorage.setItem("confettiPlayed", "true");
            }
        }

        updateProgress();

        // Klik-event voor checklist items (only for users)
        if (items.length > 0) {
            items.forEach(item => {
                const circle = item.querySelector(".circle");
                item.addEventListener("click", () => {
                    if (circle.classList.contains("checked")) {
                        circle.classList.remove("checked");
                        completed--;
                    } else {
                        circle.classList.add("checked");
                        completed++;
                    }
                    percentage = Math.round((completed / items.length) * 100);
                    updateProgress();
                });
            });
        }
    });
});

/* -------------------------------
   LAUNCH CONFETTI FUNCTIE
--------------------------------- */
 function launchConfetti() {
    const colors = ["#44205F", "#22c55e", "#a855f7", "#facc15", "#ec4899"];
    const amount = 120;

    for (let i = 0; i < amount; i++) {
        const confetti = document.createElement("div");
        confetti.classList.add("confetti");

        confetti.style.left = Math.random() * 100 + "vw";
        confetti.style.backgroundColor =
            colors[Math.floor(Math.random() * colors.length)];
        confetti.style.width = (Math.random() * 8 + 4) + "px";
        confetti.style.height = (Math.random() * 12 + 6) + "px";
        confetti.style.animationDuration = Math.random() * 1.5 + 1.5 + "s";

        document.body.appendChild(confetti);

        setTimeout(() => confetti.remove(), 2500);
    }
}
