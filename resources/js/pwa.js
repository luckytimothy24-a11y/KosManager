const sw = import.meta.env.MODE === "production" ? "/sw.js" : "/dev-sw.js";
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker.register(sw)
            .then(function(registration) {
                console.log("SW registered:", registration.scope);
                // Check for push subscription
                checkPushSubscription(registration);
            })
            .catch(function(error) {
                console.error("SW registration failed:", error);
            });
    });
}

// VAPID public key from meta tag
function getVapidPublicKey() {
    var meta = document.querySelector('meta[name="vapid-public-key"]');
    return meta ? meta.getAttribute("content") : null;
}

// Convert base64 to Uint8Array for VAPID key
function urlBase64ToUint8Array(base64String) {
    var padding = "=".repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Check if user has push subscription
async function checkPushSubscription(registration) {
    if (!("PushManager" in window)) {
        console.log("Push not supported");
        return;
    }
    var subscription = await registration.pushManager.getSubscription();
    if (subscription) {
        console.log("Already subscribed to push");
        return;
    }
    // Show custom prompt if not subscribed and permission not denied
    if (Notification.permission !== "denied") {
        showPushPrompt(registration);
    }
}

// Custom push permission prompt
function showPushPrompt(registration) {
    // Don't show if already dismissed in last 7 days
    var dismissed = localStorage.getItem("push-prompt-dismissed");
    if (dismissed && Date.now() - parseInt(dismissed) < 7 * 24 * 60 * 60 * 1000) {
        return;
    }
    // Don't show if already shown this session
    if (sessionStorage.getItem("push-prompt-shown")) {
        return;
    }
    sessionStorage.setItem("push-prompt-shown", "true");

    // Wait 10 seconds before showing
    setTimeout(function() {
        if (document.getElementById("push-prompt-banner")) return;
        var banner = document.createElement("div");
        banner.id = "push-prompt-banner";
        banner.className = "fixed bottom-24 left-4 right-4 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-4 flex items-start gap-3 animate-slide-up";
        banner.innerHTML = '<div class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-500/15 flex items-center justify-center"><i class="ri-notification-3-line text-primary-600 dark:text-primary-400 text-lg"></i></div><div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-white">Aktifkan Notifikasi</p><p class="text-xs text-slate-500 dark:text-slate-400">Dapatkan info booking, pembayaran, dan check-in langsung di browser</p></div><div class="flex gap-2"><button id="push-enable-btn" class="px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 transition">Aktifkan</button><button id="push-dismiss-btn" class="p-2 text-slate-400 hover:text-slate-600 transition"><i class="ri-close-line"></i></button></div>';
        document.body.appendChild(banner);

        document.getElementById("push-enable-btn").addEventListener("click", async function() {
            var permission = await Notification.requestPermission();
            if (permission === "granted") {
                await subscribeToPush(registration);
            }
            hidePushPrompt();
        });
        document.getElementById("push-dismiss-btn").addEventListener("click", function() {
            hidePushPrompt();
            localStorage.setItem("push-prompt-dismissed", Date.now().toString());
        });
    }, 10000);
}

function hidePushPrompt() {
    var banner = document.getElementById("push-prompt-banner");
    if (banner) banner.remove();
}

// Subscribe to push notifications
async function subscribeToPush(registration) {
    var vapidKey = getVapidPublicKey();
    if (!vapidKey) {
        console.error("VAPID public key not found");
        return;
    }
    try {
        var subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey)
        });
        // Send subscription to server
        await fetch("/api/v1/devices/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                push_token: subscription.endpoint,
                device_type: "web",
                device_name: navigator.userAgent.substring(0, 100),
                subscription_data: subscription.toJSON()
            })
        });
        console.log("Push subscription registered");
    } catch (error) {
        console.error("Push subscription failed:", error);
    }
}

// Install prompt
let deferredPrompt = null;
window.addEventListener("beforeinstallprompt", function(e) {
    e.preventDefault();
    deferredPrompt = e;
    setTimeout(function() {
        if (deferredPrompt && !window.matchMedia("(display-mode: standalone)").matches) {
            showInstallBanner();
        }
    }, 30000);
});
window.addEventListener("appinstalled", function() {
    deferredPrompt = null;
    hideInstallBanner();
    console.log("App installed successfully");
});
function showInstallBanner() {
    if (document.getElementById("pwa-install-banner")) return;
    var banner = document.createElement("div");
    banner.id = "pwa-install-banner";
    banner.className = "fixed bottom-20 left-4 right-4 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3";
    banner.innerHTML = '<div class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-500/15 flex items-center justify-center"><i class="ri-download-2-line text-primary-600 dark:text-primary-400 text-lg"></i></div><div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-white">Pasang KosManager</p><p class="text-xs text-slate-500 dark:text-slate-400">Akses lebih cepat dari layar utama</p></div><button id="pwa-install-btn" class="px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 transition">Pasang</button><button id="pwa-dismiss-btn" class="p-2 text-slate-400 hover:text-slate-600 transition"><i class="ri-close-line"></i></button>';
    document.body.appendChild(banner);
    document.getElementById("pwa-install-btn").addEventListener("click", async function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        var result = await deferredPrompt.userChoice;
        if (result.outcome === "accepted") {
            console.log("User accepted install");
        }
        deferredPrompt = null;
        hideInstallBanner();
    });
    document.getElementById("pwa-dismiss-btn").addEventListener("click", function() {
        hideInstallBanner();
        localStorage.setItem("pwa-install-dismissed", Date.now().toString());
    });
}
function hideInstallBanner() {
    var banner = document.getElementById("pwa-install-banner");
    if (banner) banner.remove();
}
// Detect if installed
window.addEventListener("load", function() {
    if (window.matchMedia("(display-mode: standalone)").matches) {
        console.log("App is running in standalone mode");
        window.dispatchEvent(new CustomEvent("pwa-standalone"));
    }
});
// Offline/online events
window.addEventListener("offline", function() {
    window.dispatchEvent(new CustomEvent("app-offline"));
    showOfflineToast();
});
window.addEventListener("online", function() {
    window.dispatchEvent(new CustomEvent("app-online"));
    hideOfflineToast();
});
function showOfflineToast() {
    if (document.getElementById("offline-toast")) return;
    var toast = document.createElement("div");
    toast.id = "offline-toast";
    toast.className = "fixed top-20 left-4 right-4 z-50 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl p-3 flex items-center gap-2 shadow-lg";
    toast.innerHTML = '<i class="ri-wifi-off-line text-amber-600 dark:text-amber-400"></i><span class="text-sm text-amber-800 dark:text-amber-300">Kamu sedang offline. Beberapa fitur mungkin tidak tersedia.</span>';
    document.body.appendChild(toast);
}
function hideOfflineToast() {
    var toast = document.getElementById("offline-toast");
    if (toast) toast.remove();
}
// Clear SW caches on logout
document.addEventListener("submit", function(e) {
    var form = e.target;
    if (form && form.action && form.action.includes("/logout")) {
        if ("serviceWorker" in navigator && navigator.serviceWorker.controller) {
            var channel = new MessageChannel();
            channel.port1.onmessage = function(event) {
                console.log("Caches cleared:", event.data);
            };
            navigator.serviceWorker.controller.postMessage({ type: "CLEAR_CACHES" }, [channel.port2]);
        }
    }
});

console.log("PWA script loaded");
