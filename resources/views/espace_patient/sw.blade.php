// Espace patient : pages servies par le réseau ; page de secours hors connexion.
const CACHE = 'gsante-patient-v1';
const SECOURS = '{{ route('patient.hors_ligne', [], false) }}';

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(CACHE).then((c) => c.addAll([SECOURS, '/patient-icone-192.png'])).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
    e.waitUntil(caches.keys().then((cles) => Promise.all(cles.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (e) => {
    // Les données personnelles ne sont jamais mises en cache : seule la page de secours l'est
    if (e.request.mode === 'navigate') {
        e.respondWith(fetch(e.request).catch(() => caches.match(SECOURS)));
    }
});
