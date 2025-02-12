// let touchStartY = 0;
// let touchEndY = 0;
// let threshold = 100; // Distance minimale pour détecter un pull-down

// window.addEventListener(
//   "touchstart",
//   function (event) {
//     if (
//       event.target.tagName.toLowerCase() === "input" ||
//       event.target.tagName.toLowerCase() === "textarea"
//     ) {
//       return;
//     }
//     touchStartY = event.touches[0].clientY;
//   },
//   { passive: true } // Ajoute ceci pour éviter de bloquer le scroll natif
// );

// window.addEventListener("touchend", function (event) {
//   if (
//     event.target.tagName.toLowerCase() === "input" ||
//     event.target.tagName.toLowerCase() === "textarea"
//   ) {
//     return;
//   }

//   touchEndY = event.changedTouches[0].clientY;

//   if (
//     touchEndY - touchStartY > threshold &&
//     document.visibilityState === "visible" &&
//     window.scrollY === 0
//   ) {
//     console.log("Pull-to-refresh detected at top of page");
//     window.location.reload();
//   }
// });
