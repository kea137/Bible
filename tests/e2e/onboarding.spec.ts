import { test, expect } from './fixtures/base.js';

test.describe('Onboarding Flow', () => {
    test('should complete full onboarding process', async ({ authenticatedPage }) => {
        const page = authenticatedPage;

        // Verify we're on onboarding page
        await expect(page).toHaveURL(/.*onboarding/);
        await expect(page.locator('text=Welcome')).toBeVisible();

        // Step 1: Language Selection
        await expect(page.locator('text=Language')).toBeVisible();
        // English should be selected by default, proceed to next step
        await page.click('text=Next');

        // Step 2: Bible Translation Selection
        await page.waitForTimeout(500);
        await expect(page.locator('text=Bible Translations')).toBeVisible();

        // Select at least one Bible translation
        const firstCheckbox = page.locator('[role="checkbox"]').first();
        await firstCheckbox.click();

        // Verify checkbox is checked
        await expect(firstCheckbox).toHaveAttribute('data-state', 'checked');

        // Proceed to next step
        await page.click('text=Next');

        // Step 3: Appearance Preferences
        await page.waitForTimeout(500);
        await expect(page.locator('text=Appearance')).toBeVisible();

        // Select a theme (Light)
        const lightThemeButton = page.locator('button:has-text("Light")');
        await lightThemeButton.click();

        // Complete onboarding
        await page.click('text=Complete Setup');

        // Should redirect to dashboard
        await page.waitForURL('**/dashboard', { timeout: 10000 });
        await expect(page).toHaveURL(/.*dashboard/);

        // Verify dashboard content loaded
        await expect(page.locator('text=Dashboard')).toBeVisible();
    });

    test('should allow skipping onboarding', async ({ authenticatedPage }) => {
        const page = authenticatedPage;

        // Verify we're on onboarding page
        await expect(page).toHaveURL(/.*onboarding/);

        // Click skip button if available
        const skipButton = page.locator('text=Skip');
        if (await skipButton.isVisible()) {
            await skipButton.click();

            // Should redirect to dashboard
            await page.waitForURL('**/dashboard', { timeout: 10000 });
            await expect(page).toHaveURL(/.*dashboard/);
        }
    });

    test('should navigate between onboarding steps', async ({ authenticatedPage }) => {
        const page = authenticatedPage;

        // Step 1: Language
        await expect(page.locator('text=Language')).toBeVisible();
        await page.click('text=Next');

        // Step 2: Bible Translations
        await page.waitForTimeout(500);
        await expect(page.locator('text=Bible Translations')).toBeVisible();

        // Go back to previous step
        const backButton = page.locator('text=Previous');
        if (await backButton.isVisible()) {
            await backButton.click();

            // Should be back on language step
            await page.waitForTimeout(500);
            await expect(page.locator('text=Language')).toBeVisible();
        }
    });
});
