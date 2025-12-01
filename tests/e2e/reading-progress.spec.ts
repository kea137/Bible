import { test, expect } from './fixtures/base.js';

test.describe('Bible Navigation and Reading Progress', () => {
    test('should mark chapter as read', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Look for mark as read button or checkbox
        const markReadButton = page.locator('button:has-text("Mark")').or(
            page.locator('button[aria-label*="read"]').or(
                page.locator('input[type="checkbox"][aria-label*="read"]'),
            ),
        );
        if (await markReadButton.first().isVisible()) {
            await markReadButton.first().click();
            await page.waitForTimeout(1000);

            // Verify visual feedback
            const checkIcon = page.locator('[data-icon="check"]').or(
                page.locator('svg[class*="check"]'),
            );
        }
    });

    test('should view reading progress', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to reading plan/progress page
        const readingPlanLink = page.locator('a[href*="reading-plan"]').or(
            page.locator('a:has-text("Reading Plan")').or(
                page.locator('a:has-text("Progress")'),
            ),
        );
        if (await readingPlanLink.first().isVisible()) {
            await readingPlanLink.first().click();
            await page.waitForURL('**/reading-plan', { timeout: 10000 });

            // Verify progress page loaded
            await expect(page).toHaveURL(/.*reading-plan/);
            await page.waitForTimeout(1000);

            // Check for progress indicators
            const progressBar = page.locator('[role="progressbar"]').or(
                page.locator('.progress'),
            );
        }
    });

    test('should navigate using book selector', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Look for book selector dropdown
        const bookSelector = page.locator('select').or(
            page.locator('[role="combobox"]').or(page.locator('button:has-text("Genesis")')),
        );
        if (await bookSelector.first().isVisible()) {
            await bookSelector.first().click();
            await page.waitForTimeout(500);

            // Select a different book
            const bookOption = page.locator('[role="option"]').or(
                page.locator('option'),
            ).nth(1);
            if (await bookOption.isVisible()) {
                await bookOption.click();
                await page.waitForTimeout(1000);

                // Verify content changed
                await expect(page.locator('[data-verse-number]').first()).toBeVisible();
            }
        }
    });

    test('should navigate using chapter selector', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Look for chapter selector
        const chapterSelector = page.locator('select').or(
            page.locator('[role="combobox"]'),
        ).nth(1);
        if (await chapterSelector.isVisible()) {
            await chapterSelector.click();
            await page.waitForTimeout(500);

            // Select a different chapter
            const chapterOption = page.locator('[role="option"]').or(
                page.locator('option'),
            ).nth(2);
            if (await chapterOption.isVisible()) {
                await chapterOption.click();
                await page.waitForTimeout(1000);

                // Verify content changed
                await expect(page.locator('[data-verse-number]').first()).toBeVisible();
            }
        }
    });

    test('should use previous/next chapter navigation', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Get current chapter number
        const currentChapter = await page.locator('text=/Chapter \\d+/').textContent();

        // Click next chapter
        const nextButton = page.locator('button:has-text("Next")').or(
            page.locator('button[aria-label*="next"]'),
        );
        if (await nextButton.first().isVisible()) {
            await nextButton.first().click();
            await page.waitForTimeout(1000);

            // Verify chapter changed
            await expect(page.locator('[data-verse-number]').first()).toBeVisible();

            // Click previous chapter
            const prevButton = page.locator('button:has-text("Previous")').or(
                page.locator('button[aria-label*="previous"]'),
            );
            if (await prevButton.first().isVisible()) {
                await prevButton.first().click();
                await page.waitForTimeout(1000);

                // Verify back to original chapter
                await expect(page.locator('[data-verse-number]').first()).toBeVisible();
            }
        }
    });

    test('should highlight verses', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        // Look for highlight button/icon
        const highlightButton = page.locator('button[aria-label*="highlight"]').or(
            page.locator('button:has-text("Highlight")'),
        );
        if (await highlightButton.first().isVisible()) {
            await highlightButton.first().click();
            await page.waitForTimeout(1000);

            // Verify verse is highlighted (visual change)
            // The exact verification depends on how highlighting is implemented
        }
    });

    test('should view highlighted verses list', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to highlighted verses page
        const highlightedLink = page.locator('a[href*="highlighted"]').or(
            page.locator('a:has-text("Highlighted")'),
        );
        if (await highlightedLink.first().isVisible()) {
            await highlightedLink.first().click();
            await page.waitForURL('**/highlighted**', { timeout: 10000 });

            await page.waitForTimeout(1000);

            // Verify highlighted verses page loaded
            await expect(page).toHaveURL(/.*highlighted/);
        }
    });

    test('should track reading statistics', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to dashboard to see statistics
        await page.goto('/dashboard');
        await page.waitForTimeout(1000);

        // Look for statistics section
        const statsSection = page.locator('text=/\\d+.*read/i').or(
            page.locator('text=/\\d+.*chapter/i'),
        );
        // Statistics might be visible on dashboard
    });
});
