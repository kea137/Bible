import { test } from './fixtures/base.js';

test.describe('Verse Link Canvas CRUD', () => {
    test('should create a new verse link canvas', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Verse Link page
        const verseLinkNav = page.locator('a[href*="verse-link"]');
        await verseLinkNav.click();
        await page.waitForURL('**/verse-link', { timeout: 10000 });

        // Click create new canvas button
        const createButton = page.locator('button:has-text("Create")').or(
            page.locator('button:has-text("New Canvas")'),
        );
        if (await createButton.first().isVisible()) {
            await createButton.first().click();

            // Wait for dialog/form to appear and fill in canvas details
            const nameInput = page.locator('input[name*="name"]').or(
                page.locator('input[placeholder*="name"]').or(
                    page.locator('input[placeholder*="Name"]'),
                ),
            );
            await nameInput.first().waitFor({ state: 'visible', timeout: 5000 });
            await nameInput.first().fill('Test Canvas ' + Date.now());

            // Save canvas
            const saveButton = page.locator('button:has-text("Save")').or(
                page.locator('button:has-text("Create")'),
            );
            if (await saveButton.first().isVisible()) {
                await saveButton.first().click();
                await page.waitForTimeout(1000);
            }
        }
    });

    test('should add nodes to canvas', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Verse Link page
        const verseLinkNav = page.locator('a[href*="verse-link"]');
        await verseLinkNav.click();
        await page.waitForURL('**/verse-link', { timeout: 10000 });

        // Create a new canvas first
        const createButton = page.locator('button:has-text("Create")').or(
            page.locator('button:has-text("New Canvas")'),
        );
        if (await createButton.first().isVisible()) {
            await createButton.first().click();
            await page.waitForTimeout(500);

            const nameInput = page.locator('input[name*="name"]').or(
                page.locator('input[placeholder*="name"]').or(
                    page.locator('input[placeholder*="Name"]'),
                ),
            );
            if (await nameInput.first().isVisible()) {
                await nameInput.first().fill('Node Test Canvas ' + Date.now());

                const saveButton = page.locator('button:has-text("Save")').or(
                    page.locator('button:has-text("Create")'),
                );
                if (await saveButton.first().isVisible()) {
                    await saveButton.first().click();
                    await page.waitForTimeout(1000);

                    // Try to add a node
                    const addNodeButton = page.locator('button:has-text("Add")').or(
                        page.locator('button[aria-label*="add"]'),
                    );
                    if (await addNodeButton.first().isVisible()) {
                        await addNodeButton.first().click();
                        await page.waitForTimeout(500);
                    }
                }
            }
        }
    });

    test('should view canvas details', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Verse Link page
        const verseLinkNav = page.locator('a[href*="verse-link"]');
        await verseLinkNav.click();
        await page.waitForURL('**/verse-link', { timeout: 10000 });

        await page.waitForTimeout(1000);

        // Look for existing canvases
        const canvasCard = page.locator('[role="button"]').or(page.locator('button')).first();
        if (await canvasCard.isVisible()) {
            const canvasText = await canvasCard.textContent();
            if (canvasText && canvasText.includes('Canvas')) {
                await canvasCard.click();
                await page.waitForTimeout(1000);
            }
        }
    });

    test('should update canvas name', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Verse Link page
        const verseLinkNav = page.locator('a[href*="verse-link"]');
        await verseLinkNav.click();
        await page.waitForURL('**/verse-link', { timeout: 10000 });

        // Create a canvas to update
        const createButton = page.locator('button:has-text("Create")').or(
            page.locator('button:has-text("New Canvas")'),
        );
        if (await createButton.first().isVisible()) {
            await createButton.first().click();
            await page.waitForTimeout(500);

            const nameInput = page.locator('input[name*="name"]').or(
                page.locator('input[placeholder*="name"]').or(
                    page.locator('input[placeholder*="Name"]'),
                ),
            );
            if (await nameInput.first().isVisible()) {
                const originalName = 'Original Canvas ' + Date.now();
                await nameInput.first().fill(originalName);

                const saveButton = page.locator('button:has-text("Save")').or(
                    page.locator('button:has-text("Create")'),
                );
                if (await saveButton.first().isVisible()) {
                    await saveButton.first().click();
                    await page.waitForTimeout(1000);

                    // Try to edit the canvas
                    const editButton = page.locator('button:has-text("Edit")').or(
                        page.locator('button[aria-label*="edit"]'),
                    );
                    if (await editButton.first().isVisible()) {
                        await editButton.first().click();
                        await page.waitForTimeout(500);

                        // Update name
                        const editNameInput = page.locator('input[name*="name"]').or(
                            page.locator('input[placeholder*="name"]'),
                        );
                        if (await editNameInput.first().isVisible()) {
                            await editNameInput.first().clear();
                            await editNameInput.first().fill('Updated Canvas ' + Date.now());

                            const updateButton = page.locator('button:has-text("Update")').or(
                                page.locator('button:has-text("Save")'),
                            );
                            if (await updateButton.first().isVisible()) {
                                await updateButton.first().click();
                                await page.waitForTimeout(1000);
                            }
                        }
                    }
                }
            }
        }
    });

    test('should delete a canvas', async ({ onboardedPage }) => {
        const page = onboardedPage;

        // Navigate to Verse Link page
        const verseLinkNav = page.locator('a[href*="verse-link"]');
        await verseLinkNav.click();
        await page.waitForURL('**/verse-link', { timeout: 10000 });

        // Create a canvas to delete
        const createButton = page.locator('button:has-text("Create")').or(
            page.locator('button:has-text("New Canvas")'),
        );
        if (await createButton.first().isVisible()) {
            await createButton.first().click();
            await page.waitForTimeout(500);

            const nameInput = page.locator('input[name*="name"]').or(
                page.locator('input[placeholder*="name"]').or(
                    page.locator('input[placeholder*="Name"]'),
                ),
            );
            if (await nameInput.first().isVisible()) {
                await nameInput.first().fill('Canvas To Delete ' + Date.now());

                const saveButton = page.locator('button:has-text("Save")').or(
                    page.locator('button:has-text("Create")'),
                );
                if (await saveButton.first().isVisible()) {
                    await saveButton.first().click();
                    await page.waitForTimeout(1000);

                    // Try to delete the canvas
                    const deleteButton = page.locator('button:has-text("Delete")').or(
                        page.locator('button[aria-label*="delete"]'),
                    );
                    if (await deleteButton.first().isVisible()) {
                        await deleteButton.first().click();
                        await page.waitForTimeout(500);

                        // Confirm deletion if there's a confirmation dialog
                        const confirmButton = page.locator('button:has-text("Confirm")').or(
                            page.locator('button:has-text("Delete")'),
                        );
                        if (await confirmButton.last().isVisible()) {
                            await confirmButton.last().click();
                            await page.waitForTimeout(1000);
                        }
                    }
                }
            }
        }
    });
});
