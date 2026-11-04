# Sobolle Studio WordPress theme

The independent software catalog, engineering portfolio and Journal at
https://mrsobolle.com/. Theme release: 0.3.0. PHP 8.2+, tested on WordPress 7.1.4.

## Features

- Single-screen desktop Home and shared glass header across all templates.
- Native Page catalogs: Products (`/apps/`) and Engineering (`/work/`).
- Five-language Journal with topic filtering, related product articles and separate version histories.
- Product pages, documentation, About, Contact, privacy and terms pages, search, archives and a real 404 template.
- EN/RU/DE/FR/ES with Polylang, translated page relationships and gettext UI catalogs.
- Server-rendered unique metadata, canonical URLs, hreflang, social previews and JSON-LD.
- Keyboard skip links, narrow-screen layouts and reduced-motion support.
- Local fonts and small native JavaScript; disabled 3D experiments are not loaded.

## Editing content

Use WordPress Pages for catalogs and their immediate children for products/cases.
The excerpt is used in catalog cards. Journal uses native Posts/categories/tags.
The separate **Sobolle Studio Content** plugin owns stable product identifiers,
article relationships and release records. Its reviewed 0.1.0 source is retained
in `bundled-plugins/`, but theme deployment does not install or replace plugins.

Page contents and their translations are CMS data, not automatically deployed by Git.
The public contact demo is browser-only: it never transmits or persists form fields.
Email is a separate mailto link. Billing, accounts, newsletters, beta registration,
external APIs and unreleased product downloads are not connected.
Never invent release dates, ratings, prices, client results or product capabilities.
AI PC Assistant remains a concept, represented as CreativeWork rather than a
working SoftwareApplication. Legal copy is explicitly provisional.

## SEO and deliberate noindex

The theme is the single owner of frontend metadata. SmartCrawl frontend metadata
and duplicate schema output are suppressed; Polylang continues to own hreflang.
Schema includes Person, Organization, WebSite, page types, BreadcrumbList, ItemList,
BlogPosting and truthful product/concept entities. Rich-result eligibility is not
guaranteed and missing commercial data must not be fabricated.

Indexing remains disabled by BOTH the WordPress `blog_public=0` setting and the
absence of `STUDIO_ALLOW_INDEXING=true`. HTML robots and X-Robots-Tag contain
noindex. A future launch requires an explicit owner decision, reviewed content,
`blog_public=1`, the explicit config constant and fresh crawl/metadata/sitemap QA.
Never change these as a side effect of deployment. Noindex is not authentication.

Do not block every page in robots.txt: crawlers need to read the noindex directive.
References: https://developers.google.com/search/docs/crawling-indexing/block-indexing
and https://developers.google.com/search/docs/appearance/structured-data/sd-policies .

## Tests

Run `php -l` on all tracked PHP, then each `tests/php/*.test.php`.
PR checks run these on PHP 8.2. Public-site Playwright smoke checks use
`npm ci && npm run test:smoke`; they inspect the currently deployed version,
not unmerged candidate code. Always test candidate code on staging separately.

Full read-only release checks:

    wp eval-file scripts/export-public-content.php > /private/routes.json
    php tests/verify-site.php https://mrsobolle.com /private/routes.json /private/http-qa.json
    node tests/release-browser.cjs https://mrsobolle.com /private/routes.json /private/browser-qa desktop

The standalone browser script requires Playwright and an installed Chrome binary.
Set QA_CHROME_EXECUTABLE when needed. Repeat with `mobile` and `narrow` modes.

## Safe publication and rollback

Main is protected. Use a feature branch, PR and successful required checks.
The deployment workflow is manual, defaults to dry_run=true and only publishes
main when dry_run=false and confirm_production=DEPLOY. Check exact target and
rsync deletion plan. Take fresh encrypted snapshots before touching production.
Theme deployment excludes tests, scripts, docs and bundled plugins.

`complete-content.php` is a release-specific, guarded, selective migration.
Its default is read-only. Apply requires a NEW rollback JSON outside WordPress:

    wp eval-file scripts/complete-content.php
    wp eval-file scripts/complete-content.php apply /private/new-snapshot.json
    wp eval-file scripts/rollback-content.php /private/new-snapshot.json

Append `apply` to the last command only after reviewing the rollback plan.
Rollback restores only captured fields/metadata; newly created pages become drafts,
not deleted. It refuses to overwrite subsequent author edits. A full SQL import
is not part of this workflow. Retain private snapshots outside Git and webroot.
Never execute an old migration blindly against content that has since changed.

## Author

Dmitriy Fedotov / MrSobolle.
