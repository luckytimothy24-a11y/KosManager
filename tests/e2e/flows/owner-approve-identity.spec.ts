import { test, expect } from "@playwright/test";
import { LoginPage } from "../pages/LoginPage";

test.describe("Owner: Approve Booking and Identity", () => {
    test("owner can view dashboard", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("owner@example.com", "password");

        await page.goto("/dashboard");
        await expect(page).toHaveURL(/dashboard/);
        await expect(page).toHaveURL(/dashboard/);
    });

    test("owner can view booking list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("owner@example.com", "password");

        await page.goto("/owner/booking");
        await expect(page).toHaveURL(/owner\/booking/);
    });

    test("owner can view checkin list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("owner@example.com", "password");

        await page.goto("/owner/checkin");
        await expect(page).toHaveURL(/owner\/checkin/);
    });

    test("owner can view checkout list", async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login("owner@example.com", "password");

        await page.goto("/owner/checkout");
        await expect(page).toHaveURL(/owner\/checkout/);
    });
});
