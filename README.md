# Drongo ERP — Monorepo

Enterprise resource planning platform with **50+ loosely-coupled business modules** in a single repository.

## Repo layout

| Path | Purpose |
|------|---------|
| `apps/` | Deployable applications (API, web, admin) |
| `modules/` | Business modules — accounting, inventory, HR, sales, … |
| `packages/` | Shared libraries — core, database, auth, … |
| `.github/` | CI/CD workflows, CODEOWNERS, rulesets |
| `tools/` | Module boundary validator, scaffolding |
| `docs/` | Architecture & contribution guides |

## Getting started

```bash
# 1. Clone
git clone https://github.com/drongo-org/erp-monorepo.git
cd erp-monorepo

# 2. Install PHP dependencies (root merges all module composer.json files)
composer install

# 3. Run the module boundary validator (runs automatically on commit)
php tools/validate-module-boundaries.php

# 4. Run tests
vendor/bin/phpunit
```

## Module ownership

See `.github/CODEOWNERS` and `CONTRIBUTING.md`. Each module has a dedicated GitHub team that is automatically assigned as reviewer on PRs touching that module.

## Rulesets

Branch protection is enforced via **GitHub Rulesets** (not `.github/` files). See `docs/ARCHITECTURE.md` and the GitHub repo Settings → Rulesets for the exact rule configuration applied to `main`, `develop`, and `release/*`.
