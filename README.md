# MrSobolle WordPress Theme

Source for the personal engineering portfolio and software catalog at
https://mrsobolle.com/.

## What it provides

- WordPress classic theme with small service classes, reusable templates, and responsive styles.
- Homepage focused on professional work and independently developed software.
- Two native WordPress Page catalogs, Work and Apps, with automatic listings of published immediate child pages.
- Gutenberg-editable detail pages for projects, software, documentation, privacy notices and release notes.
- Translation-ready strings and a conditional Polylang language switcher (nothing appears until configured).
- No billing, checkout, licensing API or user account logic in the theme.

## Editorial setup (WordPress Pages)

1. Create a published Page titled Work with slug work, and choose template Work catalog.
2. Create a published Page titled Apps with slug apps, and choose template Apps catalog.
3. Create pages with Work or Apps as Parent, for example a WideCrop child with slug widecrop.
4. Write content with WordPress blocks. Use the Page excerpt field for a card description, and Featured Image for an illustration.
5. Adjust menu_order to arrange cards; page titles break ties. Only published direct children are listed.

Native hierarchical Page URLs support /apps/widecrop/ and product
subpages such as /apps/widecrop/docs/.

These templates are not an alternative to a product database. For a paid
release, use a stable product identifier and a separate checkout/entitlement
system. Never identify licenses by URL, translated slug or WordPress Page ID.

### Language support

The languages and default URL format have not been selected yet. Do not create
language prefixes or redirects in this theme. When a multilingual provider
like Polylang is selected and tested, assign equivalent catalog templates to
their translated parent Pages and translate child Pages via WordPress editor.
The switcher shows only when at least two valid translations are available.

Page templates do not assume a language code, country, currency or billing
provider. Gettext translation strings are present, but .po/.mo languages have
not yet been authored.

### Security and publishing

Do not publish confidential client case studies, private repositories or
application download links before review. WideCrop is not yet published in
Chrome Web Store. Local PowerShell needs a security and release review before
public distribution. Never store payment data or API credentials in this theme.

## Tests

Run on PHP 8.2:

    php tests/php/catalog.test.php
    php tests/php/language-switcher.test.php

PHP lint and mocked rendering tests run on Pull Requests. Separate Playwright
smoke tests currently target the deployed public site, not new PR code.
These checks do not replace full end-to-end staging verification.

## Deployment

The main branch is protected by PR and CI checks. Deployment is manual
workflow_dispatch, defaults to dry_run=true, and requires DEPLOY confirmation
for a real publication. rsync --delete targets only the WordPress theme
directory, with exclusions from .deployignore. Inspect a fresh dry-run,
snapshot files, and verify rollback before production changes.
Template changes alone do not create WordPress Pages or modify the DB.

## Author

Dmitriy Fedotov / MrSobolle.