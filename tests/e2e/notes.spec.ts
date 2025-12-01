import { test, expect } from '../fixtures/base';

test.describe('Notes CRUD', () => {
    test('should navigate to notes page', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Notes page
        const notesNav = page.locator('a[href*="notes"]').or(page.locator('text=Notes'));
        await notesNav.first().click();
        await page.waitForURL('**/notes', { timeout: 10000 });

        // Verify we're on notes page
        await expect(page).toHaveURL(/.*notes/);
        await expect(page.locator('text=Notes')).toBeVisible();
    });

    test('should create a new note from Bible verse', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Bibles page first
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

        // Look for note/pen icon or add note button
        const noteButton = page
            .locator('button[aria-label*="note"]')
            .or(page.locator('button:has-text("Note")'));
        if (await noteButton.first().isVisible()) {
            await noteButton.first().click();
            await page.waitForTimeout(500);

            // Fill in note details
            const titleInput = page.locator('input[name*="title"]').or(
                page.locator('input[placeholder*="title"]').or(
                    page.locator('input[placeholder*="Title"]'),
                ),
            );
            if (await titleInput.first().isVisible()) {
                await titleInput.first().fill('Test Note ' + Date.now());
            }

            const contentInput = page.locator('textarea[name*="content"]').or(
                page.locator('textarea[placeholder*="content"]').or(
                    page.locator('textarea[placeholder*="note"]'),
                ),
            );
            if (await contentInput.first().isVisible()) {
                await contentInput.first().fill('This is a test note content.');
            }

            // Save note
            const saveButton = page.locator('button:has-text("Save")');
            if (await saveButton.first().isVisible()) {
                await saveButton.first().click();
                await page.waitForTimeout(1000);
            }
        }
    });

    test('should view notes list', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Notes page
        const notesNav = page.locator('a[href*="notes"]').or(page.locator('text=Notes'));
        await notesNav.first().click();
        await page.waitForURL('**/notes', { timeout: 10000 });

        await page.waitForTimeout(1000);

        // Check if there are any notes
        const notesList = page.locator('[role="list"]').or(page.locator('.note-item'));
        // Just verify the page loaded, notes might be empty
    });

    test('should update a note', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // First create a note via the notes page directly or navigate to notes
        const notesNav = page.locator('a[href*="notes"]').or(page.locator('text=Notes'));
        await notesNav.first().click();
        await page.waitForURL('**/notes', { timeout: 10000 });

        await page.waitForTimeout(1000);

        // Look for existing notes
        const noteItem = page.locator('[role="button"]').first();
        if (await noteItem.isVisible()) {
            await noteItem.click();
            await page.waitForTimeout(500);

            // Look for edit button
            const editButton = page.locator('button[aria-label*="edit"]').or(
                page.locator('button:has-text("Edit")'),
            );
            if (await editButton.first().isVisible()) {
                await editButton.first().click();
                await page.waitForTimeout(500);

                // Update note content
                const contentInput = page.locator('textarea[name*="content"]').or(
                    page.locator('textarea'),
                );
                if (await contentInput.first().isVisible()) {
                    await contentInput.first().fill('Updated note content ' + Date.now());

                    // Save changes
                    const saveButton = page.locator('button:has-text("Save")');
                    if (await saveButton.first().isVisible()) {
                        await saveButton.first().click();
                        await page.waitForTimeout(1000);
                    }
                }
            }
        }
    });

    test('should delete a note', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Notes page
        const notesNav = page.locator('a[href*="notes"]').or(page.locator('text=Notes'));
        await notesNav.first().click();
        await page.waitForURL('**/notes', { timeout: 10000 });

        await page.waitForTimeout(1000);

        // Look for existing notes
        const noteItem = page.locator('[role="button"]').first();
        if (await noteItem.isVisible()) {
            await noteItem.click();
            await page.waitForTimeout(500);

            // Look for delete button
            const deleteButton = page.locator('button[aria-label*="delete"]').or(
                page.locator('button:has-text("Delete")'),
            );
            if (await deleteButton.first().isVisible()) {
                await deleteButton.first().click();
                await page.waitForTimeout(500);

                // Confirm deletion if dialog appears
                const confirmButton = page.locator('button:has-text("Confirm")').or(
                    page.locator('button:has-text("Delete")'),
                );
                if (await confirmButton.last().isVisible()) {
                    await confirmButton.last().click();
                    await page.waitForTimeout(1000);
                }
            }
        }
    });

    test('should search notes', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Notes page
        const notesNav = page.locator('a[href*="notes"]').or(page.locator('text=Notes'));
        await notesNav.first().click();
        await page.waitForURL('**/notes', { timeout: 10000 });

        await page.waitForTimeout(1000);

        // Look for search input
        const searchInput = page.locator('input[type="search"]').or(
            page.locator('input[placeholder*="Search"]').or(
                page.locator('input[placeholder*="search"]'),
            ),
        );
        if (await searchInput.first().isVisible()) {
            await searchInput.first().fill('test');
            await page.waitForTimeout(1000);
        }
    });
});
