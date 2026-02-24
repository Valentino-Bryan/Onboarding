document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const mobileMenu = document.getElementById("mobileMenu");
    
    if (hamburger && mobileMenu) {
        hamburger.addEventListener("click", (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle("show");
        });
        
        document.addEventListener("click", (e) => {
            if (mobileMenu.classList.contains("show") && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.remove("show");
            }
        });
        
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                mobileMenu.classList.remove("show");
            }
        });
    }
});