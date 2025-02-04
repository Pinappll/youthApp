const CACHE_NAME = "youth-app-v1";
const urlsToCache = [
  "/",
  "/manifest.json",
  "/icons/icon-192x192.png",
  "/icons/icon-512x512.png",
  "/offline.html",
];

// Installation du Service Worker et mise en cache des fichiers essentiels
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
      .catch((err) => console.error("Cache addAll error:", err))
  );
});

// Intercepter les requêtes
self.addEventListener("fetch", (event) => {
  const requestUrl = new URL(event.request.url);

  // 🚀 Exclure la requête de logout et toutes les requêtes POST (ex: login, logout, formulaires)
  if (requestUrl.pathname === "/logout" || event.request.method === "POST") {
    return fetch(event.request);
  }

  event.respondWith(
    caches.match(event.request).then((response) => {
      return (
        response ||
        fetch(event.request)
          .then((networkResponse) => {
            return caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, networkResponse.clone());
              return networkResponse;
            });
          })
          .catch(() => caches.match("/offline.html"))
      );
    })
  );
});

// Nettoyage des anciens caches
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    })
  );
});

// Supprimer le cache après logout
self.addEventListener("message", (event) => {
  if (event.data.action === "clear-cache") {
    caches.delete(CACHE_NAME);
  }
});
