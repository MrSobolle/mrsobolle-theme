# MrSobolle WordPress Theme

Source for the personal engineering portfolio and software catalog at
https://mrsobolle.com/.

## What it provides

- WordPress classic theme with small service classes, reusable templates, and responsive styles.
- Homepage focused on professional work and independently developed software.
- Two native WordPress Page catalogs, Work and Apps, with automatic listings of published immediate child pages.
- Gutenberg-editable detail pages for projects, software, documentation, privacy notices and release notes.
- Polylang Free 3.9.1 integration with 5-language navigation and complete gettext UI catalogs for four translations.
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

The enabled languages are English (default), Russian, German, French and
Spanish. Production uses Polylang Free 3.9.1. English retains canonical
unprefixed URLs, such as /apps/widecrop/. Other languages use /ru/, /de/,
/fr/ and /es/ plus localized page slugs.

Polylang links translated WordPress Pages and provides SEO hreflang links.
The header language switcher links to the equivalent translated Page.
All existing posts/pages were assigned to English at setup; each translated
catalog uses the same WordPress Page template.

The theme contains gettext PO and binary MO catalogs for ru_RU, de_DE,
fr_FR and es_ES, stored under languages/ and tracked in GitHub. English
UI source strings live in PHP. Translated Page contents are editable in
WordPress, while navigation labels and homepage copy are in theme gettext.
Language selection is independent of browser location, payment plans or
license IDs. Translations, especially legal/privacy copy, should be checked
by fluent reviewers before representing them as legally reviewed.
### Security and publishing

Do not publish confidential client case studies, private repositories or
application download links before review. WideCrop is not yet published in
Chrome Web Store. Local PowerShell needs a security and release review before
public distribution. Never store payment data or API credentials in this theme.

## Tests

Run on PHP 8.2:

    php tests/php/catalog.test.php
    php tests/php/language-switcher.test.php
    php tests/php/translations.test.php

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