let touchStartY = 0;
let touchEndY = 0;
const refreshThreshold = 100; // Distance minimale pour déclencher le refresh

document.addEventListener("touchstart", (event) => {
    touchStartY = event.touches[0].clientY;
});

document.addEventListener("touchmove", (event) => {
    touchEndY = event.touches[0].clientY;
});

document.addEventListener("touchend", () => {
    // Vérifie si l'utilisateur est en haut de la page et a glissé vers le bas
    if (window.scrollY === 0 && touchEndY - touchStartY > refreshThreshold) {
        location.reload(); // Recharge la page
    }
});
