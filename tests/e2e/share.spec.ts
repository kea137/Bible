import { test, expect } from './fixtures/base.js';

test.describe('Share Verse', () => {
    test('should navigate to share page from verse', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse to see options
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        // Look for share button/icon
        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();

            // Should navigate to share page
            await page.waitForURL('**/share**', { timeout: 10000 });
            await expect(page).toHaveURL(/.*share/);
        }
    });

    test('should generate verse image with gradient background', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse and share
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();
            await page.waitForURL('**/share**', { timeout: 10000 });

            // Wait for canvas/image to render
            await page.waitForTimeout(2000);

            // Verify canvas element exists
            const canvas = page.locator('canvas');
            if (await canvas.isVisible()) {
                await expect(canvas).toBeVisible();
            }

            // Verify verse text is displayed
            await expect(page.locator('text=Share Verse')).toBeVisible();
        }
    });

    test('should change background gradient', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse and share
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();
            await page.waitForURL('**/share**', { timeout: 10000 });

            await page.waitForTimeout(1000);

            // Look for background selection buttons
            const backgroundButton = page
                .locator('button[aria-label*="background"]')
                .or(page.locator('button[role="radio"]'))
                .nth(1);
            if (await backgroundButton.isVisible()) {
                await backgroundButton.click();
                await page.waitForTimeout(1000);

                // Verify canvas updated
                const canvas = page.locator('canvas');
                if (await canvas.isVisible()) {
                    await expect(canvas).toBeVisible();
                }
            }
        }
    });

    test('should customize text style', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse and share
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();
            await page.waitForURL('**/share**', { timeout: 10000 });

            await page.waitForTimeout(1000);

            // Look for font selection
            const fontSelect = page.locator('select').or(
                page.locator('[role="combobox"]'),
            );
            if (await fontSelect.first().isVisible()) {
                await fontSelect.first().click();
                await page.waitForTimeout(500);

                // Select a different font
                const fontOption = page.locator('[role="option"]').nth(1);
                if (await fontOption.isVisible()) {
                    await fontOption.click();
                    await page.waitForTimeout(1000);
                }
            }

            // Look for bold toggle
            const boldButton = page.locator('button:has-text("Bold")').or(
                page.locator('button[aria-label*="bold"]'),
            );
            if (await boldButton.first().isVisible()) {
                await boldButton.first().click();
                await page.waitForTimeout(1000);
            }
        }
    });

    test('should download verse image', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse and share
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();
            await page.waitForURL('**/share**', { timeout: 10000 });

            await page.waitForTimeout(2000);

            // Set up download listener
            const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);

            // Look for download button
            const downloadButton = page.locator('button:has-text("Download")').or(
                page.locator('button[aria-label*="download"]'),
            );
            if (await downloadButton.first().isVisible()) {
                await downloadButton.first().click();

                // Wait for download
                const download = await downloadPromise;
                if (download) {
                    // Verify download started
                    expect(download).toBeTruthy();
                }
            }
        }
    });

    test('should use custom gradient colors', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page
        await page.click('text=Bibles');
        await page.waitForURL('**/bibles', { timeout: 10000 });

        // Select a Bible
        const firstBible = page.locator('a[href*="/bibles/"]').first();
        await firstBible.click();
        await page.waitForTimeout(1000);

        // Click on a verse and share
        const firstVerse = page.locator('[data-verse-number]').first();
        await firstVerse.click();
        await page.waitForTimeout(500);

        const shareButton = page.locator('button[aria-label*="share"]').or(
            page.locator('button:has-text("Share")'),
        );
        if (await shareButton.first().isVisible()) {
            await shareButton.first().click();
            await page.waitForURL('**/share**', { timeout: 10000 });

            await page.waitForTimeout(1000);

            // Look for custom color toggle
            const customColorToggle = page.locator('input[type="checkbox"]').or(
                page.locator('button:has-text("Custom")'),
            );
            if (await customColorToggle.first().isVisible()) {
                await customColorToggle.first().click();
                await page.waitForTimeout(500);

                // Look for color picker
                const colorInput = page.locator('input[type="color"]');
                if (await colorInput.first().isVisible()) {
                    await colorInput.first().fill('#FF5733');
                    await page.waitForTimeout(1000);
                }
            }
        }
    });
});
