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
  self.skipWaiting(); // Force l'installation immédiate
});

// Activation et suppression des anciens caches
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
  self.clients.claim(); // Prendre immédiatement le contrôle des clients
});

// Intercepter les requêtes
self.addEventListener("fetch", (event) => {
  const requestUrl = new URL(event.request.url);

  // Exclure la requête de logout et toutes les requêtes POST (login, formulaires, etc.)
  if (requestUrl.pathname === "/logout" || event.request.method === "POST") {
    return fetch(event.request).catch(
      () => new Response("Offline", { status: 503 })
    );
  }

  event.respondWith(
    caches.match(event.request).then((response) => {
      return (
        response ||
        fetch(event.request)
          .then((networkResponse) => {
            // Vérifier si la requête est une requête GET (éviter de stocker des requêtes dynamiques)
            if (
              !event.request.url.startsWith("http") ||
              event.request.method !== "GET"
            ) {
              return networkResponse;
            }
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

// Supprimer le cache après logout
self.addEventListener("message", (event) => {
  if (event.data && event.data.action === "clear-cache") {
    caches
      .keys()
      .then((cacheNames) => {
        return Promise.all(cacheNames.map((cache) => caches.delete(cache)));
      })
      .then(() => {
        console.log("Cache supprimé après logout.");
      });
  }
});
