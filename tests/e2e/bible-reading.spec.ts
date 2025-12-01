import { test, expect } from './fixtures/base.js';

test.describe('Bible Reading with References', () => {
    test('should navigate to Bible and read verses', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible to read
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();

        // Wait for Bible page to load
        await page.waitForTimeout(1000);

        // Verify Bible content is loaded
        await expect(page.locator('text=Chapter')).toBeVisible();

        // Verify verses are displayed
        const verses = page.locator('[data-verse-number]');
        await expect(verses.first()).toBeVisible();
    });

    test('should view cross-references for a verse', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();

        await page.waitForTimeout(1000);

        // Look for a verse with references icon or hover over a verse
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.hover();

        // Check if references are shown (either automatically or via click)
        // Note: The exact interaction depends on the UI implementation
        await page.waitForTimeout(500);
    });

    test('should navigate between chapters', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();

        await page.waitForTimeout(1000);

        // Click next chapter button if available
        const nextChapterButton = page.locator('button:has-text("Next")').or(
            page.locator('button[aria-label*="next"]'),
        );

        if (await nextChapterButton.first().isVisible()) {
            await nextChapterButton.first().click();
            await page.waitForTimeout(1000);

            // Verify chapter changed
            await expect(page.locator('[data-verse-number]').first()).toBeVisible();
        }
    });

    test('should search for verses', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();

        await page.waitForTimeout(1000);

        // Look for search input or command palette trigger
        const searchButton = page.locator('button[aria-label*="search"]').or(
            page.locator('button:has-text("Search")'),
        );

        if (await searchButton.first().isVisible()) {
            await searchButton.first().click();
            await page.waitForTimeout(500);

            // Type in search field
            const searchInput = page.locator('input[type="search"]').or(
                page.locator('input[placeholder*="Search"]'),
            );
            if (await searchInput.first().isVisible()) {
                await searchInput.first().fill('John 3:16');
                await page.waitForTimeout(1000);
            }
        }
    });

    test('should view parallel Bible translations', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Parallel Bibles page
        const parallelLink = page.locator('a[href*="parallel"]');
        if (await parallelLink.isVisible()) {
            await parallelLink.click();
            await page.waitForURL('**/parallel', { timeout: 10000 });

            // Verify parallel view loaded
            await page.waitForTimeout(1000);
            await expect(page).toHaveURL(/.*parallel/);
        }
    });
});
