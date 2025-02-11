let touchStartY = 0;
let touchEndY = 0;
const refreshThreshold = 100; // Distance minimale pour déclencher le refresh

document.addEventListener("touchstart", (event) => {
  const target = event.target;

  // 🔹 Vérifie si l'utilisateur touche un champ de formulaire (empêche le pull-to-refresh)
  if (
    target.tagName === "INPUT" ||
    target.tagName === "TEXTAREA" ||
    target.tagName === "SELECT"
  ) {
    return;
  }

  touchStartY = event.touches[0].clientY;
});

document.addEventListener("touchmove", (event) => {
  touchEndY = event.touches[0].clientY;
});

document.addEventListener("touchend", (event) => {
  // 🔹 Vérifie si l'utilisateur est en haut de la page et a glissé vers le bas
  if (window.scrollY === 0 && touchEndY - touchStartY > refreshThreshold) {
    event.preventDefault(); // 🔹 Empêche les comportements inattendus
    setTimeout(() => {
      location.reload(); // 🔄 Recharge la page après un court délai
    }, 100);
  }
});

// ✅ Fix du bug du clavier qui se ferme immédiatement sur mobile
document.addEventListener("focusin", (event) => {
  const target = event.target;

  if (
    target.tagName === "INPUT" ||
    target.tagName === "TEXTAREA" ||
    target.tagName === "SELECT"
  ) {
    console.log("Clavier ouvert"); // Debug
    document.body.classList.add("keyboard-open");

    // 🔹 Ajoute un léger délai pour éviter que le clavier ne se ferme immédiatement
    setTimeout(() => {
      window.scrollTo(0, document.body.scrollHeight); // Fait descendre la page si nécessaire
    }, 300);
  }
});

document.addEventListener("focusout", () => {
  console.log("Clavier fermé"); // Debug
  document.body.classList.remove("keyboard-open");
});
