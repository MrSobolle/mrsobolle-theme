import { expect, test } from '@playwright/test';

test('Work catalog exposes a published, navigable engineering case', async ({ page }) => {
  const response = await page.goto('/work/', { waitUntil: 'domcontentloaded' });
  expect(response?.status()).toBe(200);

  await expect(page.getByRole('heading', { level: 1, name: /^(Work|Engineering)$/ })).toBeVisible();
  const caseLink = page.locator('.catalog-card__title a', { hasText: 'MrSobolle WordPress Theme' });
  await expect(caseLink).toHaveAttribute('href', /\/work\/mrsobolle-theme\/$/);

  await caseLink.click();
  await expect(page).toHaveURL(/\/work\/mrsobolle-theme\/$/);
  await expect(page.getByRole('heading', { level: 1, name: 'MrSobolle WordPress Theme' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'View the public source code on GitHub' })).toHaveAttribute(
    'href',
    'https://github.com/MrSobolle/mrsobolle-theme'
  );
});

test('Apps catalog reaches WideCrop with honest availability and policy', async ({ page }) => {
  const response = await page.goto('/apps/', { waitUntil: 'domcontentloaded' });
  expect(response?.status()).toBe(200);

  await expect(page.getByRole('heading', { level: 1, name: /^(Apps|Products)$/ })).toBeVisible();
  await page.locator('.catalog-card__title a', { hasText: 'WideCrop' }).click();

  await expect(page).toHaveURL(/\/apps\/widecrop\/$/);
  await expect(page.getByRole('heading', { level: 1, name: 'WideCrop' })).toBeVisible();
  await expect(page.getByText(/not yet been submitted to or published in the Chrome Web Store/i)).toBeVisible();
  await expect(page.locator('.page-back-link')).toHaveAttribute('href', /\/apps\/$/);

  await page.getByRole('link', { name: 'WideCrop privacy policy' }).click();
  await expect(page).toHaveURL(/\/apps\/widecrop\/privacy\/$/);
  await expect(page.getByRole('heading', { level: 1, name: 'WideCrop privacy policy' })).toBeVisible();
  await expect(page.getByText(/Last updated: October 25, 2026/)).toBeVisible();
});

test('Internal page navigation remains functional and mobile-safe', async ({ page }, testInfo) => {
  const runtimeErrors = [];
  page.on('pageerror', (error) => runtimeErrors.push(error.message));

  for (const path of ['/work/', '/apps/', '/apps/widecrop/']) {
    const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
    expect(response?.status(), path).toBe(200);
    await expect(page.locator('#masthead')).toBeVisible();

    if (testInfo.project.name.includes('mobile')) {
      const overflows = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      expect(overflows, 'Unexpected horizontal overflow on ' + path).toBe(false);
    }
  }

  const nav = page.locator('#site-navigation');
  await expect(nav.getByRole('link', { name: /^(Work|Engineering)$/ })).toHaveAttribute('href', /\/work\/$/);
  await expect(nav.getByRole('link', { name: /^(Apps|Products)$/ })).toHaveAttribute('href', /\/apps\/$/);
  await expect(nav.getByRole('link', { name: 'About' })).toHaveAttribute('href', /\/(?:#about|about\/)$/);
  expect(runtimeErrors, 'Uncaught JavaScript exceptions').toEqual([]);

  await testInfo.attach('widecrop-page.png', {
    body: await page.screenshot({ fullPage: true }),
    contentType: 'image/png'
  });
});