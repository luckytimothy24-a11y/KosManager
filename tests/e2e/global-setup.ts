import { execSync } from "child_process";

async function globalSetup() {
    console.log("Clearing rate limiter cache...");
    try {
        execSync("php artisan cache:clear", { cwd: process.cwd(), stdio: "inherit" });
    } catch (e) {
        console.warn("Failed to clear cache:", e);
    }
}

export default globalSetup;
