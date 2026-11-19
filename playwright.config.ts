import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
    testDir: "./tests/e2e/flows",
    globalSetup: "./tests/e2e/global-setup.ts",
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: "html",
    use: {
        baseURL: process.env.BASE_URL || "http://127.0.0.1:8000",
        trace: "on-first-retry",
        screenshot: "only-on-failure",
    },
    projects: [
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
    ],
    webServer: {
        command: "php artisan serve",
        url: "http://127.0.0.1:8000",
        reuseExistingServer: !process.env.CI,
        timeout: 120000,
    },
});
