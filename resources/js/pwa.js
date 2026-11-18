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
let deferredPrompt = null;
window.addEventListener("beforeinstallprompt", function(e) {
    e.preventDefault();
    deferredPrompt = e;
    window.dispatchEvent(new CustomEvent("pwa-install-available"));
});
window.addEventListener("appinstalled", function() {
    deferredPrompt = null;
    window.dispatchEvent(new CustomEvent("pwa-installed"));
});
window.addEventListener("load", function() {
    if (window.matchMedia("(display-mode: standalone)").matches) {
        console.log("App is running in standalone mode");
        window.dispatchEvent(new CustomEvent("pwa-standalone"));
    }
});
window.addEventListener("offline", function() {
    window.dispatchEvent(new CustomEvent("app-offline"));
});
window.addEventListener("online", function() {
    window.dispatchEvent(new CustomEvent("app-online"));
});
console.log("PWA script loaded");
