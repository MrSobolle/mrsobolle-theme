# QA for the MrSobolle WordPress theme

## What is checked

- The syntax of all tracked PHP files (php -l, PHP 8.2).
- The live public homepage using Playwright in 1440px desktop and 390px mobile Chromium.
- HTTP 200, semantic content, skip-link target, uncaught JavaScript exceptions.
- Screenshots are attached to the test report; they are not strict pixel-diff assertions.

Important: browser smoke checks the currently deployed site. It does not validate
unmerged PHP/CSS changes. For change-specific testing, deploy to an isolated staging
environment and set QA_BASE_URL to that environment first. Never run destructive
tests on production.

## Running locally

Requirements: Node.js 24, PHP 8.2, Playwright Chromium or installed Chrome.

    npm ci
    npx playwright install chromium
    npm run test:smoke

On Windows, portable Node.js can be used without a system-wide installation.
Set QA_CHROME_EXECUTABLE to the local chrome.exe to avoid another browser download.

## CI / release behaviour

- .github/workflows/quality.yml runs on PRs to main and is reusable.
  It never needs deployment credentials.
- .github/workflows/deploy.yml is proposed to become manual only, with
  quality checks before any SSH operation.
- Manual dispatch defaults to dry_run=true. To publish, select
  dry_run=false and type DEPLOY in the confirmation field.
- .deployignore excludes tests, npm manifests and Playwright tooling
  from production theme files.
- Add branch ruleset and required status checks after the first
  successful PR check. Do not merge this PR before reviewing release policy.
- Automatic rollback is not provided. WPMU DEV daily backup is not a
  guaranteed pre-release snapshot. Each deployment still needs an
  independent pre-change snapshot, dry-run and rollback procedure.