const sw = import.meta.env.MODE === "production" ? "/sw.js" : "/dev-sw.js";
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker.register(sw)
            .then(function(registration) {
                console.log("SW registered:", registration.scope);
            })
            .catch(function(error) {
                console.error("SW registration failed:", error);
            });
    });
}
// Install prompt
let deferredPrompt = null;
window.addEventListener("beforeinstallprompt", function(e) {
    e.preventDefault();
    deferredPrompt = e;
    // Show install banner after 30 seconds if not installed
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
// Custom install banner
function showInstallBanner() {
    if (document.getElementById("pwa-install-banner")) return;
    var banner = document.createElement("div");
    banner.id = "pwa-install-banner";
    banner.className = "fixed bottom-20 left-4 right-4 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 animate-slide-up";
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
// Check if previously dismissed (don't show for 7 days)
var dismissed = localStorage.getItem("pwa-install-dismissed");
if (dismissed && Date.now() - parseInt(dismissed) < 7 * 24 * 60 * 60 * 1000) {
    // Don't show banner
} else {
    // Banner will show when beforeinstallprompt fires
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
console.log("PWA script loaded");
