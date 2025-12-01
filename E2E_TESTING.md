# Playwright E2E Testing Guide

This guide covers how to run and maintain the end-to-end (E2E) tests for the Bible application using Playwright.

## Overview

The E2E test suite covers 6 core scenarios:
1. **Onboarding Flow** - User registration and onboarding process
2. **Bible Reading with References** - Reading Bible verses and viewing cross-references
3. **Verse Link Canvas CRUD** - Creating, reading, updating, and deleting verse link canvases
4. **Notes CRUD** - Creating, reading, updating, and deleting notes
5. **Share Verse** - Sharing verses with customizable backgrounds
6. **Reading Progress** - Bible navigation and reading progress tracking

## Prerequisites

- Node.js (v18 or higher)
- npm or yarn
- A running Laravel application instance
- Database configured and migrated

## Installation

1. Install dependencies:
```bash
npm install
```

2. Install Playwright browsers:
```bash
npx playwright install chromium
```

## Running Tests Locally

### Run all E2E tests
```bash
npm run test:e2e
```

### Run tests with UI (interactive mode)
```bash
npm run test:e2e:ui
```

### Run tests in headed mode (see browser)
```bash
npm run test:e2e:headed
```

### Run tests in debug mode
```bash
npm run test:e2e:debug
```

### Run specific test file
```bash
npx playwright test tests/e2e/onboarding.spec.ts
```

### Run tests matching a pattern
```bash
npx playwright test --grep "onboarding"
```

## Test Environment Setup

### Local Development

The tests expect the application to be running at `http://localhost:8000` by default. The Playwright config includes a `webServer` configuration that will automatically start the Laravel development server when running tests locally.

If you prefer to run the server manually:

1. Start the Laravel development server:
```bash
php artisan serve
```

2. In another terminal, run the tests:
```bash
npm run test:e2e
```

### Environment Variables

You can customize the base URL by setting the `APP_URL` environment variable:
```bash
APP_URL=http://localhost:8080 npm run test:e2e
```

## CI/CD Integration

The tests are configured to run in CI environments. Key CI-specific behaviors:

- **Retries**: Tests automatically retry up to 2 times on failure
- **Workers**: Tests run sequentially (1 worker) to avoid conflicts
- **Reporters**: JUnit XML, HTML, and list reporters are enabled
- **Artifacts**: Screenshots and videos are captured on failure

### GitHub Actions

The tests are integrated into the GitHub Actions workflow. See `.github/workflows/e2e.yml` for the complete configuration.

Artifacts (screenshots, videos, traces) are automatically uploaded when tests fail and can be downloaded from the Actions run page.

## Test Structure

### Test Files
- `tests/e2e/onboarding.spec.ts` - Onboarding flow tests
- `tests/e2e/bible-reading.spec.ts` - Bible reading and references tests
- `tests/e2e/verse-link.spec.ts` - Verse Link Canvas CRUD tests
- `tests/e2e/notes.spec.ts` - Notes CRUD tests
- `tests/e2e/share.spec.ts` - Share verse functionality tests
- `tests/e2e/reading-progress.spec.ts` - Reading progress and navigation tests

### Fixtures
- `tests/e2e/fixtures/base.ts` - Custom test fixtures including:
  - `authenticatedPage` - Provides an authenticated user session
  - `onboardedPage` - Provides a fully onboarded user session
  - Helper functions for common test operations

## Writing New Tests

### Using Fixtures

```typescript
import { test, expect } from '../fixtures/base';

test('my test', async ({ onboardedPage }) => {
    const page = onboardedPage;
    // Test code here - user is already logged in and onboarded
});
```

### Best Practices

1. **Use semantic locators**: Prefer `getByRole`, `getByLabel`, `getByText` over CSS selectors
2. **Wait for navigation**: Always use `waitForURL` after navigation actions
3. **Add timeouts**: Use `waitForTimeout` sparingly, prefer `waitForSelector` or `waitForURL`
4. **Clean state**: Each test should be independent and not rely on other tests
5. **Screenshots**: Screenshots are automatically captured on failure
6. **Descriptive names**: Use clear, descriptive test names

### Example Test

```typescript
test('should create a note', async ({ onboardedPage }) => {
    const page = onboardedPage;
    
    // Navigate to notes page
    await page.click('text=Notes');
    await page.waitForURL('**/notes');
    
    // Create note
    await page.click('button:has-text("New Note")');
    await page.fill('input[name="title"]', 'My Note');
    await page.fill('textarea[name="content"]', 'Note content');
    await page.click('button:has-text("Save")');
    
    // Verify
    await expect(page.locator('text=My Note')).toBeVisible();
});
```

## Debugging Tests

### View test results
```bash
npx playwright show-report
```

### Run in debug mode
```bash
npx playwright test --debug tests/e2e/onboarding.spec.ts
```

### View trace for a failed test
Traces are automatically captured on failure. To view:
```bash
npx playwright show-trace test-results/[test-name]/trace.zip
```

### Enable verbose logging
```bash
DEBUG=pw:api npm run test:e2e
```

## Common Issues

### Database state
Tests create new users for each run. Ensure your database is properly configured and migrations are run.

### Port conflicts
If port 8000 is in use, update the `baseURL` in `playwright.config.ts` or set `APP_URL` environment variable.

### Timeouts
If tests are timing out, increase timeout in individual tests:
```typescript
test('slow test', async ({ onboardedPage }) => {
    test.setTimeout(60000); // 60 seconds
    // test code
});
```

### Flaky tests
If tests are flaky due to timing:
- Use `waitForSelector` instead of `waitForTimeout`
- Increase `timeout` values in waitFor calls
- Ensure proper navigation with `waitForURL`

## Continuous Improvement

### Adding Coverage
When adding new features, add corresponding E2E tests to maintain coverage.

### Updating Tests
When UI changes, update the locators and assertions in tests accordingly.

### Performance
Monitor test execution time and optimize slow tests. Consider:
- Running tests in parallel (update `workers` in config)
- Reducing unnecessary waits
- Using fixtures to share setup between tests

## Resources

- [Playwright Documentation](https://playwright.dev/)
- [Playwright Test Best Practices](https://playwright.dev/docs/best-practices)
- [Playwright Debugging Guide](https://playwright.dev/docs/debug)

## Support

For issues or questions about the E2E tests:
1. Check this documentation
2. Review test logs and screenshots
3. Open an issue on GitHub with details
