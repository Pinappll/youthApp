document.addEventListener("DOMContentLoaded", () => {
  const loader = document.getElementById("pull-to-refresh-loader");
  const contentContainer = document.getElementById("content"); // Assure-toi que cet ID existe
  const API_URL = "/api/mangas"; // Remplace par ton endpoint API

  // Fonction pour charger de nouvelles données
  async function refreshContent() {
    if (!contentContainer) {
      console.error("⚠️ Erreur : L'élément #content est introuvable.");
      return;
    }

    // Afficher l'animation de chargement
    loader.classList.remove("hidden");

    try {
      const response = await fetch(API_URL);
      if (!response.ok) {
        throw new Error(`Erreur API : ${response.statusText}`);
      }

      const data = await response.json();
      console.log("✅ Données reçues :", data);

      // Vérifie si des données sont disponibles
      if (data.length === 0) {
        console.warn("⚠️ Aucune donnée reçue !");
        return;
      }

      // Effacer uniquement les anciennes entrées sans casser la structure HTML
      contentContainer.innerHTML = "";

      // Insérer les nouvelles données
      data.forEach((manga) => {
        const div = document.createElement("div");
        div.classList.add(
          "p-4",
          "border-b",
          "bg-gray-100",
          "rounded-lg",
          "shadow-sm"
        );
        div.innerHTML = `
                    <h2 class="text-xl font-bold">${manga.title}</h2>
                    <p class="text-gray-700">${manga.description}</p>
                `;
        contentContainer.appendChild(div);
      });
    } catch (error) {
      console.error("❌ Erreur lors du chargement des données :", error);
    } finally {
      // Masquer le loader après 800ms pour un effet fluide
      setTimeout(() => {
        loader.classList.add("hidden");
      }, 800);
    }
  }

  // Initialiser PullToRefresh.js avec la fonction de mise à jour
  PullToRefresh.init({
    mainElement: "body",
    onRefresh() {
      return refreshContent();
    },
  });
});
