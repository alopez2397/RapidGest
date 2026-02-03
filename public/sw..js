self.addEventListener("install", e => {
  e.waitUntil(
    caches.open("tpv-cache").then(cache => {
      return cache.addAll([
        "/rapidgest/tpv.php",
        "/public/css/tactil.css",
        "/public/js/tpv.js"
      ]);
    })
  );
});

self.addEventListener("fetch", e => {
  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
