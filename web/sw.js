let cacheName = "Goome-v1";

this.addEventListener("fetch", event => {
  event.respondWith(
    (async () => {
      let cache = await caches.open(cacheName);
      try {
        await cache.add(event.request);
      } catch (e) {
        //Echec de l'ajout dans le cache (hors connection)
      }
      let response = await cache.match(event.request);
      //Si la réponse n'est pas dans le cache, fallback sur une page d'information
      if (!response) {
        response = await cache.match("offline.html");
      }
      return response;
    })()
  );
});

this.addEventListener('install', function(event) {
    event.waitUntil((async () => {
        let cache = await caches.open(cacheName);
        await cache.addAll([
          "/",
          "/liste-des-equipes"
        ]);
    })());
});
