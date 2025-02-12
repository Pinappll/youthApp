let touchStartY = 0;
let touchEndY = 0;
let threshold = 100; // Distance minimale pour détecter un pull-down

window.addEventListener("touchstart", function (event) {
  touchStartY = event.touches[0].clientY;
});

window.addEventListener("touchend", function (event) {
  touchEndY = event.changedTouches[0].clientY;

  if (
    touchEndY - touchStartY > threshold &&
    document.visibilityState === "visible" &&
    window.scrollY === 0
  ) {
    console.log("Pull-to-refresh detected at top of page");
    window.location.reload();
  }
});

