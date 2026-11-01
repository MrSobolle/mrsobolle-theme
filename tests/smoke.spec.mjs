import { expect, test } from '@playwright/test';

// Read-only smoke checks against the currently deployed public site.
// These do NOT exercise unmerged theme changes. For change-specific E2E,
// set QA_BASE_URL to a separately deployed staging/test environment.
test('homepage responds and exposes primary content', async ({ page }, testInfo) => {
  const exceptions = [];
  page.on('pageerror', (error) => exceptions.push(error.message));

  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response, 'A response must be received').not.toBeNull();
  expect(response.status()).toBe(200);

  await expect(page.locator('h1')).toHaveCount(1);
  await expect(page.locator('#masthead')).toBeVisible();
  await expect(page.locator('#primary')).toBeVisible();
  expect(exceptions, 'Uncaught browser JavaScript exceptions').toEqual([]);

  await testInfo.attach('homepage.png', {
    body: await page.screenshot({ fullPage: true }),
    contentType: 'image/png'
  });
});

test('skip link targets an existing main region', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  const skipLink = page.locator('a.skip-link');
  await expect(skipLink).toHaveAttribute('href', '#primary');
  await expect(page.locator('#primary')).toHaveCount(1);
});