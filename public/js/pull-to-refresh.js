// Fonction pour récupérer les nouvelles données
async function refreshContent() {
  const loader = document.getElementById("pull-to-refresh-loader");

  // Affiche l'animation de chargement
  loader.classList.remove("hidden");

  try {
    // Remplace cette URL par ton endpoint API
    const response = await fetch("/api/mangas");
    const data = await response.json();

    // Mise à jour dynamique du contenu (adapte selon ton projet)
    const contentContainer = document.getElementById("content"); // ID de ton conteneur principal
    contentContainer.innerHTML = ""; // Efface l'ancien contenu

    data.forEach((manga) => {
      const div = document.createElement("div");
      div.classList.add("p-4", "border-b", "bg-gray-100");
      div.innerHTML = `<h2 class="text-xl font-bold">${manga.title}</h2>
                             <p>${manga.description}</p>`;
      contentContainer.appendChild(div);
    });
  } catch (error) {
    console.error("Erreur lors du chargement des données :", error);
  } finally {
    // Masque l'animation après un court délai
    setTimeout(() => loader.classList.add("hidden"), 1000);
  }
}

// Initialisation de PullToRefresh.js
PullToRefresh.init({
  mainElement: "body",
  onRefresh() {
    return refreshContent();
  },
});
