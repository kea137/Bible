import { test as base, expect as baseExpect } from '@playwright/test';
import type { Page } from '@playwright/test';

/**
 * Custom test fixtures for Bible application
 */
export const test = base.extend<{
    authenticatedPage: Page;
    onboardedPage: Page;
}>({
    /**
     * Fixture that provides an authenticated user session
     */
    authenticatedPage: async ({ page }, use) => {
        // Register and login a test user
        const testUser = {
            name: 'Test User',
            email: `test-${Date.now()}@example.com`,
            password: 'password123',
        };

        // Navigate to register page
        await page.goto('/register');
        await page.fill('input#name', testUser.name);
        await page.fill('input#email', testUser.email);
        await page.fill('input#password', testUser.password);
        await page.fill('input#password_confirmation', testUser.password);
        await page.click('button[type="submit"]');

        // Wait for registration to complete
        await page.waitForURL('**/onboarding', { timeout: 10000 });

        await use(page);
    },

    /**
     * Fixture that provides an onboarded user session
     */
    onboardedPage: async ({ page }, use) => {
        // Register and login a test user
        const testUser = {
            name: 'Onboarded User',
            email: `onboarded-${Date.now()}@example.com`,
            password: 'password123',
        };

        // Navigate to register page
        await page.goto('/register');
        await page.fill('input#name', testUser.name);
        await page.fill('input#email', testUser.email);
        await page.fill('input#password', testUser.password);
        await page.fill('input#password_confirmation', testUser.password);
        await page.click('button[type="submit"]');

        // Wait for onboarding page
        await page.waitForURL('**/onboarding', { timeout: 10000 });

        // Complete onboarding - Step 1: Language (already default)
        await page.click('text=Next');

        // Step 2: Select at least one Bible translation
        await page.waitForTimeout(500);
        // Find and click the first available Bible translation checkbox
        const firstCheckbox = page.locator('[role="checkbox"]').first();
        await firstCheckbox.click();
        await page.click('text=Next');

        // Step 3: Theme selection (already default)
        await page.waitForTimeout(500);
        await page.click('text=Complete Setup');

        // Wait for dashboard
        await page.waitForURL('**/dashboard', { timeout: 10000 });

        await use(page);
    },
});

export const expect = baseExpect;

/**
 * Helper function to wait for API response
 */
export async function waitForApiResponse(page: Page, urlPattern: string | RegExp) {
    return page.waitForResponse((response) => {
        const url = response.url();
        if (typeof urlPattern === 'string') {
            return url.includes(urlPattern);
        }
        return urlPattern.test(url);
    });
}

/**
 * Helper function to take screenshot on failure
 */
export async function captureScreenshotOnFailure(page: Page, testInfo: any) {
    if (testInfo.status !== testInfo.expectedStatus) {
        const screenshot = await page.screenshot();
        await testInfo.attach('screenshot', { body: screenshot, contentType: 'image/png' });
    }
}
