import { matchPrecache, precacheAndRoute } from "workbox-precaching";
import { registerRoute, setCatchHandler } from "workbox-routing";
import { CacheFirst, NetworkOnly } from "workbox-strategies";
import { ExpirationPlugin } from "workbox-expiration";

// Precache assets + halaman offline statis.
precacheAndRoute([...self.__WB_MANIFEST, { url: "/offline", revision: "1" }]);

// Fallback navigasi saat offline: tampilkan halaman /offline, bukan error browser.
setCatchHandler(async ({ event }) => {
    if (event.request.mode === "navigate") {
        const offlinePage = await matchPrecache("/offline");
        if (offlinePage) return offlinePage;
    }

    return Response.error();
});

// Push notification handler
self.addEventListener("push", function(event) {
    if (!event.data) return;

    var data = event.data.json();
    var title = data.title || "KosManager";
    var options = {
        body: data.body || "",
        icon: data.icon || "/icons/icon-192x192.png",
        badge: data.badge || "/icons/icon-72x72.png",
        vibrate: [100, 50, 100],
        data: {
            url: data.url || "/dashboard",
            ...data.data
        },
        actions: [
            { action: "open", title: "Buka" },
            { action: "dismiss", title: "Tutup" }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener("notificationclick", function(event) {
    event.notification.close();

    if (event.action === "dismiss") return;

    var url = event.notification.data && event.notification.data.url ? event.notification.data.url : "/dashboard";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(function(clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url === url && "focus" in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Clear all runtime caches on logout message
self.addEventListener("message", function(event) {
    if (event.data && event.data.type === "CLEAR_CACHES") {
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    // Don't delete precache, only runtime caches
                    if (cacheName !== "workbox-precache-v2" && !cacheName.startsWith("workbox-precache")) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(function() {
            event.ports[0].postMessage({ cleared: true });
        });
    }
});

// Runtime caching - API routes use NetworkOnly (no caching for sensitive data)
registerRoute(
    /^https:\/\/fonts\.bunny\.net\/.*/i,
    new CacheFirst({
        cacheName: "google-fonts",
        plugins: [
            new ExpirationPlugin({
                maxEntries: 20,
                maxAgeSeconds: 60 * 60 * 24 * 365
            })
        ]
    })
);

registerRoute(
    /\/api\/.*/i,
    new NetworkOnly()
);

registerRoute(
    /\.(?:png|jpg|jpeg|svg|gif|webp)$/i,
    new CacheFirst({
        cacheName: "images",
        plugins: [
            new ExpirationPlugin({
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 30
            })
        ]
    })
);
