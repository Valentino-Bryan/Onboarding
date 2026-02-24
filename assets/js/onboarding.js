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
    const items = document.querySelectorAll(".checklist-item");
    const progressText = document.querySelector(".progress-num");
    const progressBar = document.querySelector(".progress-bar");
    let completed = 0;

    // Initialiseer voortgang op basis van PHP percentage (optioneel)
    const progressCard = document.querySelector(".progress-card");
    if (progressCard && progressCard.dataset.complete === "true") {
        completed = items.length;
        updateProgress();
    }

    // Update voortgang functie
    function updateProgress() {
        const total = items.length;
        const percentage = Math.round((completed / total) * 100);
        progressText.textContent = percentage + "%";

        // Update circle animatie
        const stroke = 339.292;
        const offset = stroke - (stroke * (percentage / 100));
        progressBar.style.strokeDashoffset = offset;

        

        // Confetti trigger bij 100%
        if (percentage === 100) {  
            launchConfetti();
            sessionStorage.setItem("confettiPlayed", "true");
        }
    }

    // Klik-event voor checklist items
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
            updateProgress();
        });
    });

    // Initialiseer progress bij load
    updateProgress();
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
