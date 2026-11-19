import { test, expect } from "@playwright/test";
import { LoginPage } from "../pages/LoginPage";

test.describe("Tenant: Check-Out Request", () => {
    test("tenant can view kontrak list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("pencari@example.com", "password");

        await page.goto("/tenant/kontrak");
        await expect(page).toHaveURL(/tenant\/kontrak/);
    });

    test("tenant can view tagihan list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("pencari@example.com", "password");

        await page.goto("/tenant/tagihan");
        await expect(page).toHaveURL(/tenant\/tagihan/);
    });
});
