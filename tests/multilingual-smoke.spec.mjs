import { expect, test } from '@playwright/test';

const locales = [
  {
    code: 'en',
    home: '/',
    product: '/apps/widecrop/',
    privacy: '/apps/widecrop/privacy/',
    tiles: ['Products', 'Engineering']
  },
  {
    code: 'ru',
    home: '/ru/',
    product: '/ru/prilozheniya/widecrop/',
    privacy: '/ru/prilozheniya/widecrop/konfidentsialnost/',
    tiles: ['Продукты', 'Инженерия']
  },
  {
    code: 'de',
    home: '/de/',
    product: '/de/anwendungen/widecrop/',
    privacy: '/de/anwendungen/widecrop/datenschutz/',
    tiles: ['Produkte', 'Softwareentwicklung']
  },
  {
    code: 'fr',
    home: '/fr/',
    product: '/fr/applications/widecrop/',
    privacy: '/fr/applications/widecrop/confidentialite/',
    tiles: ['Produits', 'Ingénierie logicielle']
  },
  {
    code: 'es',
    home: '/es/',
    product: '/es/aplicaciones/widecrop/',
    privacy: '/es/aplicaciones/widecrop/privacidad/',
    tiles: ['Productos', 'Ingeniería de software']
  }
];

function endingWith(url) {
  return new RegExp(url.replaceAll('/', '\\/') + '$');
}

test('All five localized homepages render translated UI and language navigation', async ({ page }, testInfo) => {
  for (const item of locales) {
    const response = await page.goto(item.home, { waitUntil: 'domcontentloaded' });
    expect(response?.status(), item.code).toBe(200);
    await expect(page.locator('html')).toHaveAttribute('lang', new RegExp('^' + item.code));
    await expect(page.locator('.studio-tile__label')).toHaveText(item.tiles);

    const nav = page.locator('nav.language-switcher');
    await expect(nav.locator('.language-switcher__item')).toHaveCount(5);
    await expect(nav.locator('[aria-current="page"]')).toHaveCount(1);
    await expect(nav.locator('a[hreflang="ru"]')).toHaveAttribute('href', endingWith('/ru/'));

    if (testInfo.project.name.includes('mobile')) {
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      expect(overflow, item.code + ' homepage horizontal overflow').toBe(false);
    }
  }
});

test('Switching languages preserves the current WideCrop product and privacy context', async ({ page }, testInfo) => {
  for (const item of locales) {
    const response = await page.goto(item.product, { waitUntil: 'domcontentloaded' });
    expect(response?.status(), item.code).toBe(200);
    await expect(page.locator('html')).toHaveAttribute('lang', new RegExp('^' + item.code));
    await expect(page.getByRole('heading', { level: 1, name: 'WideCrop' })).toBeVisible();

    const nav = page.locator('nav.language-switcher');
    await expect(nav.locator('a')).toHaveCount(5);

    for (const target of locales) {
      await expect(nav.locator('a[hreflang="' + target.code + '"]'))
        .toHaveAttribute('href', endingWith(target.product));
    }

    const privacyLink = page.locator('.content-page__body a', {
      hasText: /privacy|confidentialité|конфиденциальности|Datenschutzerklärung|privacidad/i
    }).first();
    await expect(privacyLink).toHaveAttribute('href', endingWith(item.privacy));

    if (testInfo.project.name.includes('mobile')) {
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      expect(overflow, item.code + ' product horizontal overflow').toBe(false);
    }
  }
});