// sw.js - Service Worker optimisé pour MVC PHP

const CACHE_NAME = "web4all-cache-v1";

// Fichiers à mettre en cache pour le offline
const urlsToCache = [
  "/css/style.css",
  "/js/app.js",
  "/offline.html",
  "/app-icons/icon-192.png",
  "/app-icons/icon-512.png"
];

self.addEventListener("install", event => {
  console.log("SW Install event");
  event.waitUntil(
    caches.open(CACHE_NAME).then(async cache => {
      for (const url of urlsToCache) {
        try {
          await cache.add(url);
          console.log("Cached:", url);
        } catch (err) {
          console.error("Failed to cache:", url, err);
        }
      }
    })
  );
});

self.addEventListener("activate", event => {
  console.log("SW Activate event");
  // Optionnel : nettoyage des anciens caches
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME)
            .map(key => caches.delete(key))
      );
    })
  );
});

self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      // Renvoie le fichier caché ou fait un fetch
      return cachedResponse || fetch(event.request)
        .catch(() => {
          // En cas d’erreur (offline ou fichier introuvable), fallback sur offline.html
          return caches.match("/offline.html");
        });
    })
  );
});