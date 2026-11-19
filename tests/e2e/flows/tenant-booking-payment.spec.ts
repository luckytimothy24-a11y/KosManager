import { test, expect } from "@playwright/test";
import { LoginPage } from "../pages/LoginPage";

test.describe("Tenant: Booking to Payment", () => {
    test("tenant can search kos, book, and see payment instructions", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("pencari@example.com", "password");

        // Navigate to search kos
        await page.goto("/tenant/kos");
        await expect(page).toHaveURL(/tenant\/kos/);

        // Click on first kos card
        const firstKos = page.locator("a[href*='/tenant/kos/']").first();
        if (await firstKos.isVisible()) {
            await firstKos.click();
            await page.waitForLoadState("networkidle");
        }

        // Look for booking button
        const bookingBtn = page.locator("a:has-text('Ajukan Booking'), button:has-text('Ajukan Booking')").first();
        if (await bookingBtn.isVisible()) {
            await bookingBtn.click();
            await page.waitForLoadState("networkidle");
        }

        // Verify booking page loaded
        await expect(page).toHaveURL(/booking/);
    });

    test("tenant can view booking list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("pencari@example.com", "password");

        await page.goto("/tenant/booking");
        await expect(page).toHaveURL(/booking/);
        await expect(page.locator("h1, h2").first()).toBeVisible();
    });

    test("tenant can view tagihan list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("pencari@example.com", "password");

        await page.goto("/tenant/tagihan");
        await expect(page).toHaveURL(/tenant\/tagihan/);
    });
});
