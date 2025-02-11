let touchStartY = 0;
let touchEndY = 0;
const refreshThreshold = 100; // Distance minimale pour déclencher le refresh

document.addEventListener("touchstart", (event) => {
  // Désactive le pull-to-refresh si l'utilisateur touche un champ de saisie
  if (["INPUT", "TEXTAREA", "SELECT"].includes(event.target.tagName)) {
    return;
  }
  touchStartY = event.touches[0].clientY;
});

document.addEventListener("touchmove", (event) => {
  touchEndY = event.touches[0].clientY;
});

document.addEventListener("touchend", (event) => {
  // Vérifie si l'utilisateur est en haut de la page et a glissé vers le bas
  if (window.scrollY === 0 && touchEndY - touchStartY > refreshThreshold) {
    event.preventDefault(); // Empêche les comportements anormaux (évite un conflit)
    setTimeout(() => {
      location.reload(); // Recharge la page après un court délai
    }, 100);
  }
});

// ✅ Corrige le bug où le clavier ne s'ouvre pas sur mobile
document.addEventListener("focusin", (event) => {
  if (["INPUT", "TEXTAREA", "SELECT"].includes(event.target.tagName)) {
    document.body.classList.add("keyboard-open");
  }
});

document.addEventListener("focusout", () => {
  document.body.classList.remove("keyboard-open");
});
